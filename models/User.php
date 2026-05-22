<?php
/*Gère toutes les opérations sur les utilisateurs*/

class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /*Récupère un utilisateur par ID*/
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /*Récupère un utilisateur par email*/
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /* Crée un nouvel utilisateur*/
    public function create($nom, $prenom, $email, $password, $telephone = null, $adresse = null, $ville = null, $date_naissance = null) {
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare(
                "INSERT INTO users (nom, prenom, email, password, telephone, adresse, ville,date_naissance, role) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'client')"
            );
            $stmt->execute([$nom, $prenom, $email, $passwordHash, $telephone, $adresse, $ville, $date_naissance]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /*Vérifie le mot de passe*/
    public function verifyPassword($email, $password) {
        $user = $this->getByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
   
}
