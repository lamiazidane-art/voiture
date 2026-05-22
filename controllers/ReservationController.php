<?php
/* Contrôleur Reservation - Gère les opérations sur les réservations*/

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/models/Reservation.php';
require_once dirname(__DIR__) . '/models/Voiture.php';

class ReservationController {
    private $db;
    private $reservation;
    private $voiture;
    
    public function __construct() {
        $this->db = getDB();
        $this->reservation = new Reservation();
        $this->voiture = new Voiture();
    }
     
    /* Récupère les réservations d'un utilisateu */
    public function getByUserId($user_id) {
        if (empty($user_id)) {
            return [];
        }
        return $this->reservation->getByUserId($user_id);
    }
    
    /*Récupère les réservations confirmées d'un utilisateur*/
    public function getConfirmed($user_id) {
        if (empty($user_id)) {
            return [];
        }
        return $this->reservation->getByUserIdAndStatus($user_id, 'confirmee');
    }
    
    /*Récupère les réservations en attente d'un utilisateur*/
    public function getPending($user_id) {
        if (empty($user_id)) {
            return [];
        }
        return $this->reservation->getByUserIdAndStatus($user_id, 'en_attente');
    }

    /* Récupère les réservations terminées d'un utilisateu*/
    public function getTerminated($user_id) {
        if (empty($user_id)) {
            return [];
        }
        return $this->reservation->getByUserIdAndStatus($user_id, 'terminee');
    }

    /* Récupère les réservations annulées d'un utilisateur */
    public function getCancelled($user_id) {
        if (empty($user_id)) {
            return [];
        }
        return $this->reservation->getByUserIdAndStatus($user_id, 'annulee');
    }

    /* Récupère les plages déjà réservées pour un véhicule*/
    public function getBookedDateRanges($voiture_id) {
        if (empty($voiture_id)) {
            return [];
        }

        return $this->reservation->getBookedDateRanges($voiture_id);
    }
    
    /*Crée une nouvelle réservation*/
    public function create($user_id, $voiture_id, $date_debut, $date_fin, $lieuPriseEnCharge, $lieuRetour) {
      
        if (empty($user_id) || empty($voiture_id) || empty($date_debut) || empty($date_fin)) {
            return ['success' => false, 'message' => 'Données invalides'];
        }
        
        // Vérifie si l'utilisateur a déjà une réservation active pour ce véhicule
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ? AND voiture_id = ? AND statut IN ('en_attente', 'confirmee')");
        $stmt->execute([$user_id, $voiture_id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Vous avez déjà une réservation active pour ce véhicule'];
        }
        
        // Vérifie si l'utilisateur a des réservations qui se chevauchent avec ces dates
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ? AND statut IN ('en_attente', 'confirmee') AND date_debut < ? AND date_fin > ?");
        $stmt->execute([$user_id, $date_fin, $date_debut]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Vous avez déjà une réservation pour ces dates'];
        }
        
// Valide le format des dates
try {

    $debut = new DateTime($date_debut);
    $fin = new DateTime($date_fin);

    // date début dans le passé interdite
    $today = new DateTime();
    $today->setTime(0,0);

    if ($debut < $today) {
        return [
            'success' => false,
            'message' => 'La date de début est invalide'
        ];
    }

    if ($fin <= $debut) {
        return [
            'success' => false,
            'message' => 'La date de fin doit être après la date de début'
        ];
    }

} catch (Exception $e) {

    return [
        'success' => false,
        'message' => 'Format de date invalide'
    ];
}
        
        // Vérifie la disponibilité
        if (!$this->reservation->isAvailable($voiture_id, $date_debut, $date_fin)) {
            return ['success' => false, 'message' => 'Le véhicule n\'est pas disponible pour ces dates'];
        }
        
        // Récupère le prix du véhicule
        $voiture = $this->voiture->getById($voiture_id);
        if (!$voiture) {
            return ['success' => false, 'message' => 'Véhicule introuvable'];
        }
        
        // Calcule le prix total
        $jours = $fin->diff($debut)->days;
        if ($jours == 0) $jours = 1;
        $prix_total = $voiture['prix_jour'] * $jours;
        
        // Crée la réservation
        $id = $this->reservation->create($user_id, $voiture_id, $date_debut, $date_fin, $prix_total, $lieuPriseEnCharge, $lieuRetour);
        
        if ($id) {
            return ['success' => true, 'id' => $id, 'message' => 'Réservation créée', 'prix_total' => $prix_total];
        }
        return ['success' => false, 'message' => 'Erreur lors de la création de la réservation. Vérifiez vos données.'];
    }
    
    /*Annule une réservation */
    public function cancel($reservation_id, $user_id) {
        $reservation = $this->reservation->getById($reservation_id);
        
        if (!$reservation || $reservation['user_id'] != $user_id) {
            return ['success' => false, 'message' => 'Réservation introuvable'];
        }
        
        if ($this->reservation->updateStatus($reservation_id, 'annulee')) {
            return ['success' => true, 'message' => 'Réservation annulée'];
        }
        return ['success' => false, 'message' => 'Erreur lors de l\'annulation'];
    }
    
    /* Confirme une réservation*/
    public function confirm($reservation_id, $user_id) {
        $reservation = $this->reservation->getById($reservation_id);
        
        if (!$reservation || $reservation['user_id'] != $user_id) {
            return ['success' => false, 'message' => 'Réservation introuvable'];
        }
        
        if ($this->reservation->updateStatus($reservation_id, 'confirmee')) {
            return ['success' => true, 'message' => 'Réservation confirmée'];
        }
        return ['success' => false, 'message' => 'Erreur lors de la confirmation'];
    }
}