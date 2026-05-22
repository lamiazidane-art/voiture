<?php
/* Gère toutes les opérations sur les véhicules*/

class Voiture {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /* Récupère un véhicule par ID*/
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM voitures WHERE id = ?");
            $stmt->execute([(int)$id]);
            $result = $stmt->fetch();
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur getById pour ID $id: " . $e->getMessage());
            return null;
        }
    }
    
    /* Récupère tous les véhicules actifs*/
    public function getActifs() {
        $stmt = $this->db->query("SELECT * FROM voitures WHERE status = 'active' ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /* Récupère tous les véhicules (admin)*/
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM voitures ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
 
  /*Crée un nouveau véhicule*/
public function create($modele, $prix_jour, $carburant, $places, $kilometrage, $caution, $admin_id, $image_url = null) 
{
    try {
        $stmt = $this->db->prepare(
            "INSERT INTO voitures (modele, prix_jour, carburant, places, kilometrage, caution, admin_id, image_url, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')"
        );
        $stmt->execute([$modele, $prix_jour, $carburant, $places, $kilometrage, $caution, $admin_id, $image_url]);
        return $this->db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Erreur création véhicule: " . $e->getMessage());
        return false;
    }
}
    
    /*Met à jour un véhicule*/
    public function update($id, $data) {
        try {
            $updates = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                $updates[] = "$key = ?";
                $values[] = $value;
            }
            $values[] = $id;
            
            $sql = "UPDATE voitures SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour véhicule: " . $e->getMessage());
            return false;
        }
    }
    
    /* Supprime un véhicule*/
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM voitures WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur suppression véhicule: " . $e->getMessage());
            return false;
        }
    }
    
    /* Récupère les statistiques des véhicules*/
    public function getStats() {
        return [
            'total' => $this->db->query("SELECT COUNT(*) as count FROM voitures")->fetch()['count'],
            'actifs' => $this->db->query("SELECT COUNT(*) as count FROM voitures WHERE status = 'active'")->fetch()['count'],
            'inactifs' => $this->db->query("SELECT COUNT(*) as count FROM voitures WHERE status = 'inactive'")->fetch()['count']
        ];
    }
    /*filtre les véhicules selon les critères de recherche*/
    public function filtrerEtRechercher($search = '', $sort = '') {
    try {

        $sql = "SELECT * FROM voitures WHERE status = 'active'";
        $params = [];

        // Recherche par modèle
        if (!empty($search)) {
            $sql .= " AND modele LIKE ?";
            $params[] = "%$search%";
        }

        // Tri par prix
        if ($sort === 'prix_desc') {
            $sql .= " ORDER BY prix_jour DESC";
        } elseif ($sort === 'prix_asc') {
            $sql .= " ORDER BY prix_jour ASC";
        } else {
            $sql .= " ORDER BY created_at DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Erreur filtre voitures : " . $e->getMessage());
        return [];
    }
}
}
