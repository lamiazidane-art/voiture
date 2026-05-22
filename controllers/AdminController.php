<?php
/* Gère les opérations d'administration et les contacts*/

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/models/Voiture.php';
require_once dirname(__DIR__) . '/models/Reservation.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/includes/Mailer.php';

class AdminController {
    private $voiture;
    private $reservation;
    private $user;
    
    public function __construct() {
        $this->voiture = new Voiture();
        $this->reservation = new Reservation();
        $this->user = new User();
    }
    
  
    
    /**
     * Récupère le tableau de bord admin
     */
    public function getDashboard() {
        return [
            'voitures' => $this->voiture->getStats(),
            'reservations' => $this->reservation->getStats()
        ];
    }
    
 
    
    /**
     * Liste tous les véhicules pour l'admin
     */
    public function listVoitures() {
        return $this->voiture->getAll();
    }
    
    /**
     * Crée un nouveau véhicule
     */
    public function createVoiture($modele, $prix_jour, $carburant, $places, $kilometrage, $caution, $admin_id, $image_url = null) {
        if (empty($modele) || empty($prix_jour) || empty($carburant) || empty($places) || empty($kilometrage) || empty($caution)) {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires'];
        }
        
        $id = $this->voiture->create($modele, $prix_jour, $carburant, $places, $kilometrage, $caution, $admin_id, $image_url);
        if ($id) {
            return ['success' => true, 'id' => $id, 'message' => 'Véhicule créé avec succès'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la création du véhicule'];
    }
    
    /**
     * Met à jour un véhicule
     */
    public function updateVoiture($id, $data) {
        if (empty($id)) {
            return ['success' => false, 'message' => 'ID véhicule invalide'];
        }
        
        if ($this->voiture->update($id, $data)) {
            return ['success' => true, 'message' => 'Véhicule mis à jour'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
    
    /**
     * Supprime un véhicule
     */
    public function deleteVoiture($id) {
        if (empty($id)) {
            return ['success' => false, 'message' => 'ID véhicule invalide'];
        }
        
        if ($this->voiture->delete($id)) {
            return ['success' => true, 'message' => 'Véhicule supprimé'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la suppression'];
    }
    
    // GESTION DES RÉSERVATIONS 
    
    /**
     * Liste toutes les réservations pour l'admin
     */
    public function listReservations() {
        return $this->reservation->getAll();
    }
    
    /**
     * Approuve une réservation
     */
    public function approveReservation($reservation_id) {
        if ($this->reservation->updateStatus($reservation_id, 'confirmee')) {
            return ['success' => true, 'message' => 'Réservation approuvée'];
        }
        return ['success' => false, 'message' => 'Erreur lors de l\'approbation'];
    }
    
    /**
     * Annule une réservation
     */
    public function cancelReservation($reservation_id) {
        if ($this->reservation->updateStatus($reservation_id, 'annulee_admin')) {
            return ['success' => true, 'message' => 'Réservation annulée'];
        }
        return ['success' => false, 'message' => 'Erreur lors de l\'annulation'];
    }
    
    // GESTION DES CONTACTS 
    
    /**
     * Récupère les coordonnées de l'administrateur
     */
    public function getContactInfo() {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT prenom, nom, email, telephone, adresse FROM users WHERE role = 'admin' LIMIT 1");
            $admin = $stmt->fetch();
            
            if (!$admin) {
                return [
                    'full_name' => 'Administration',
                    'phone' => '+213 78 238 34 88',
                    'email' => 'messaoudene@gmail.com',
                    'address' => 'Aokas,Bejaia, Algerie',
                ];
            }

            return [
                'full_name' => trim(($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? '')) ?: 'Administration',
                'phone' => trim($admin['telephone'] ?? '') ?: '+213 78 238 34 88',
                'email' => trim($admin['email'] ?? '') ?: 'messaoudene@gmail.com',
                'address' => trim($admin['adresse'] ?? '') ?: 'Aokas,Bejaia, Algerie',
            ];
        } catch (Exception $e) {
            error_log("Erreur getContactInfo: " . $e->getMessage());
            return [
                'full_name' => 'Administration',
                'phone' => '+213 78 238 34 88',
                'email' => 'messaoudene@gmail.com',
                'address' => 'Aokas,Bejaia, Algerie',
            ];
        }
    }
    
    /**
     * Envoie un message de contact à l'administrateur
     */
    public function sendContactMessage(array $data) {
        $nom = trim($data['nom'] ?? '');
        $prenom = trim($data['prenom'] ?? '');
        $email = trim($data['email'] ?? '');
        $sujet = trim($data['sujet'] ?? '');
        $message = trim($data['message'] ?? '');

        if ($nom === '' || $prenom === '' || $email === '' || $sujet === '' || $message === '') {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email invalide.'];
        }

        $contact = $this->getContactInfo();
        $to = $contact['email'];
        $subject = 'Contact - ' . $sujet;
        $body = "Nom: {$nom} {$prenom}\nEmail: {$email}\n\nMessage:\n{$message}";

        $mailer = new Mailer();
        $htmlBody = nl2br(htmlspecialchars($body));
        
        if ($mailer->send($to, $subject, $htmlBody)) {
            return ['success' => true, 'message' => 'Votre message a été envoyé avec succès !'];
        }

        return ['success' => false, 'message' => "Erreur lors de l'envoi. Veuillez réessayer."];
    }
}