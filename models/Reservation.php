<?php
/* Gère toutes les opérations sur les réservations*/

class Reservation {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
     
    /* Récupère une réservation par ID */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT r.*, v.modele, v.prix_jour, u.nom, u.prenom 
             FROM reservations r
             JOIN voitures v ON r.voiture_id = v.id
             JOIN users u ON r.user_id = u.id
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /*Récupère les réservations d'un utilisateur*/
    public function getByUserId($user_id) {
        $stmt = $this->db->prepare(
            "SELECT r.*, v.modele, v.image_url 
             FROM reservations r
             JOIN voitures v ON r.voiture_id = v.id
             WHERE r.user_id = ?
             ORDER BY r.date_debut DESC"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    /*Récupère les réservations par statut d'un utilisateur (filtrer)  */
    public function getByUserIdAndStatus($user_id, $statut) {
        $stmt = $this->db->prepare(
            "SELECT r.*, v.modele, v.image_url 
             FROM reservations r
             JOIN voitures v ON r.voiture_id = v.id
             WHERE r.user_id = ? AND r.statut = ?
             ORDER BY r.date_debut DESC"
        );
        $stmt->execute([$user_id, $statut]);
        return $stmt->fetchAll();
    }
    
    /* Récupère toutes les réservations (admin) */
    public function getAll() {
        $stmt = $this->db->query(
            "SELECT r.*, v.modele, u.nom, u.prenom, u.email 
             FROM reservations r
             JOIN voitures v ON r.voiture_id = v.id
             JOIN users u ON r.user_id = u.id
             ORDER BY r.date_debut DESC"
        );
        return $stmt->fetchAll();
    }
    
    /* Crée une nouvelle réservation */
    public function create($user_id, $voiture_id, $date_debut, $date_fin, $prix_total, $lieuPriseEnCharge, $lieuRetour) {
        try {
            // Génère un code de réservation unique
            $code_reservation = 'RES-' . date('YmdHis') . '-' . uniqid();
            
            $stmt = $this->db->prepare(
                "INSERT INTO reservations 
                 (code_reservation, user_id, voiture_id, date_debut, date_fin, prix_total, lieuPriseEnCharge, lieuRetour, statut) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')"
            );
            
            $success = $stmt->execute([
                $code_reservation, 
                (int)$user_id, 
                (int)$voiture_id, 
                $date_debut, 
                $date_fin, 
                (float)$prix_total, 
                $lieuPriseEnCharge, 
                $lieuRetour
            ]);
            
            if (!$success) {
                $errors = $stmt->errorInfo();
                error_log("Erreur création réservation: " . $errors[2]);
                return false;
            }
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur création réservation (Exception): " . $e->getMessage());
            return false;
        }
    }
    
    /* Met à jour le statut d'une réservation*/
    public function updateStatus($id, $statut) {
        try {
            $stmt = $this->db->prepare("UPDATE reservations SET statut = ? WHERE id = ?");
            return $stmt->execute([$statut, $id]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour statut: " . $e->getMessage());
            return false;
        }
    }
    
    /* Met à jour une réservation*/
    public function update($id, $data) {
        try {
            $updates = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                $updates[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $id;
            
            $sql = "UPDATE reservations SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour réservation: " . $e->getMessage());
            return false;
        }
    }
    
    /* Supprime une réservation */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur suppression réservation: " . $e->getMessage());
            return false;
        }
    }
    
    /* Vérifie si une voiture est disponible pour les dates données*/
    public function isAvailable($voiture_id, $date_debut, $date_fin) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM reservations 
             WHERE voiture_id = ? AND statut IN ('en_attente', 'confirmee')
             AND date_debut < ? AND date_fin > ?"
        );
        $stmt->execute([$voiture_id, $date_fin, $date_debut]);
        $result = $stmt->fetch();
      
        return (int)$result['count'] === 0;
    }

    /* Récupère les plages déjà réservées pour une voiture */
    public function getBookedDateRanges($voiture_id) {
        $stmt = $this->db->prepare(
            "SELECT date_debut, date_fin
             FROM reservations
             WHERE voiture_id = ? AND statut IN ('en_attente', 'confirmee')
             ORDER BY date_debut ASC"
        );
        $stmt->execute([(int)$voiture_id]);
        return $stmt->fetchAll();
    }
    
    /* Récupère les statistiques des réservations*/
    public function getStats() {
        return [
            'total' => $this->db->query("SELECT COUNT(*) as count FROM reservations")->fetch()['count'],
            'en_attente' => $this->db->query("SELECT COUNT(*) as count FROM reservations WHERE statut = 'en_attente'")->fetch()['count'],
            'confirmees' => $this->db->query("SELECT COUNT(*) as count FROM reservations WHERE statut = 'confirmee'")->fetch()['count'],
            'terminee' => $this->db->query("SELECT COUNT(*) as count FROM reservations WHERE statut = 'terminee'")->fetch()['count']
        ];
    }
}
