<?php
// controllers/AuthController.php

require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/../includes/Mailer.php';

class AuthController {
    private $db;
    private $mailer;
    
    public function __construct() {
     
        $this->db = getDB();
        $this->mailer = new Mailer();
    }
    
    /* Vérifier si l'utilisateur est connecté*/
    public function checkLoggedIn() {
        if (isLogged()) {
            redirect(isAdmin() ? 'views/admin/index.php' : 'views/client/index.php');
        }
        return $this;
    }
    
    /* Gérer la connexion*/
    public function handleLogin($email, $password, $remember = false) {
        $email = clean($email);
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if ((int)($user['email_verified'] ?? 0) !== 1) {
                return ['error' => "Veuillez confirmer votre email avant de vous connecter."];
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($remember) {
                $this->setRememberMe($user['id']);
            }
            
            setFlash('success', 'Connexion reussie !');
            
            return ['success' => true, 'role' => $user['role']];
        }
        
        return ['error' => "Email ou mot de passe incorrect"];
    }
    
    /* Gérer l'inscription*/
   public function handleRegister($data) {

    $nom = clean($data['nom'] ?? '');
    $prenom = clean($data['prenom'] ?? '');
    $email = clean($data['email'] ?? '');
    $telephone = clean($data['telephone'] ?? '');
    $date_naissance = clean($data['date_naissance'] ?? '');

    
    if (!empty($date_naissance)) {

        $birthDate = new DateTime($date_naissance);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;

        if ($age < 20) {
            return ['errors' => ["Vous devez avoir au moins 20 ans pour creer un compte"]];
        }
    }

   
    if (!preg_match('/^[0-9]{10}$/', $telephone)) {
        return ['errors' => ["Le numero de telephone doit contenir exactement 10 chiffres"]];
    }

    $password = $data['password'] ?? '';
    $confirm = $data['password_confirm'] ?? '';

    $errors = $this->validateRegistration(
        $nom,
        $prenom,
        $email,
        $telephone,
        $password,
        $confirm
    );

    if (!empty($errors)) {
        return ['errors' => $errors];
    }

  
    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return ['errors' => ["Cet email est deja utilise"]];
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $verificationToken = bin2hex(random_bytes(32));

    $stmt = $this->db->prepare("
        INSERT INTO users
        (nom, prenom, email, telephone, password, role, email_verified, verification_token, is_active)
        VALUES (?, ?, ?, ?, ?, 'client', 0, ?, 1)
    ");

    if ($stmt->execute([
        $nom,
        $prenom,
        $email,
        $telephone,
        $hashed,
        $verificationToken
    ])) {

        $this->sendVerificationEmail($email, $prenom, $nom, $verificationToken);

        setFlash('success', 'Compte cree. Verifiez votre email(spam) puis connectez-vous.');

        return ['success' => true];
    }

    return ['errors' => ["Erreur lors de l'inscription"]];
}
    
    /* Gérer la demande de réinitialisation*/
    public function handleForgotPassword($email) {
        $email = clean($email);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Email invalide.'];
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id, prenom, nom, email FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                
                $up = $this->db->prepare("UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?");
                $up->execute([$token, $expires, $user['id']]);
                
                $this->sendResetEmail($email, $user['prenom'], $user['nom'], $token);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            error_log('Forgot password error: ' . $e->getMessage());
            return ['error' => 'Erreur technique. Veuillez reessayer.'];
        }
    }
    
    /* Valider le token de réinitialisation*/
    public function validateResetToken($token) {
        if (empty($token)) {
            return ['valid' => false, 'error' => 'Lien de reinitialisation invalide'];
        }
        
        $token = clean($token);
        
        try {
            $check = $this->db->prepare("SELECT id FROM users WHERE password_reset_token = :token AND password_reset_expires > NOW() LIMIT 1");
            $check->execute([':token' => $token]);
            
            if ($check->rowCount() > 0) {
                return ['valid' => true, 'token' => $token];
            }
            
            return ['valid' => false, 'error' => 'Lien de reinitialisation invalide ou expire'];
        } catch (Exception $e) {
            error_log('Erreur token check: ' . $e->getMessage());
            return ['valid' => false, 'error' => 'Erreur de verification du token'];
        }
    }
    
    /*Réinitialiser le mot de passe*/
    public function handleResetPassword($token, $newPassword, $confirmPassword) {
        if (strlen($newPassword) < 8 || strlen($newPassword) > 20) {
            return ['error' => "Le mot de passe doit contenir entre 8 et 20 caracteres."];
        }
        
        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
            return ['error' => "Le mot de passe doit contenir au moins une majuscule et un chiffre."];
        }
        
        if ($newPassword !== $confirmPassword) {
            return ['error' => "Les mots de passe ne correspondent pas."];
        }
        
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $update = $this->db->prepare("UPDATE users SET password = :password, password_reset_token = NULL, password_reset_expires = NULL WHERE password_reset_token = :token AND password_reset_expires > NOW()");
            
            if ($update->execute([':password' => $hashed, ':token' => $token])) {
                return ['success' => true];
            }
            
            return ['error' => "Erreur lors de la reinitialisation."];
        } catch (Exception $e) {
            error_log('Reset password error: ' . $e->getMessage());
            return ['error' => "Erreur technique. Veuillez reessayer."];
        }
    }
    
    /*Vérifier l'email*/
    public function verifyEmail($token) {
        if (empty($token)) {
            return ['success' => false, 'message' => 'Lien invalide ou expire.'];
        }
        
        $token = clean($token);
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ? AND is_active = 1");
            $stmt->execute([$token]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Email confirme avec succes. Redirection vers connexion...'];
            }
            
            return ['success' => false, 'message' => 'Lien invalide ou expire.'];
        } catch (Exception $e) {
            error_log('Verify email error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur technique lors de la verification.'];
        }
    }
    
    /* Déconnexion*/
    public function logout() {
        $this->clearRememberMe();
        session_destroy();
        redirect('views/auth/login.php');
    }
    
   
    private function validateRegistration($nom, $prenom, $email, $telephone, $password, $confirm) {
        $errors = [];
        if (empty($nom)) $errors[] = "Le nom est requis";
        if (empty($prenom)) $errors[] = "Le prenom est requis";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
        
        
        if (strlen($password) < 8 || strlen($password) > 20) {
            $errors[] = "Le mot de passe doit contenir entre 8 et 20 caracteres";
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule et un chiffre";
        }
        
        if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas";
        return $errors;
    }
    
    private function sendVerificationEmail($email, $prenom, $nom, $token) {
        $verifyUrl = appBaseUrl() . '/views/auth/verify-email.php?token=' . urlencode($token);
        $subject = 'Confirmez votre email - ' . SITE_NAME;
        $body = '<p>Bonjour ' . htmlspecialchars($prenom . ' ' . $nom) . ',</p>'
            . '<p>Merci pour votre inscription. Cliquez sur le lien ci-dessous pour confirmer votre email :</p>'
            . '<p><a href="' . $verifyUrl . '">' . $verifyUrl . '</a></p>'
            . '<p>Apres confirmation, connectez-vous.</p>';
        
        if (!$this->mailer->send($email, $subject, $body)) {
            setFlash('warning', 'Compte cree, mais email non envoye. Contactez le support.');
        }
    }
    
    private function sendResetEmail($email, $prenom, $nom, $token) {
        $resetUrl = appBaseUrl() . '/views/auth/reset-password.php?token=' . urlencode($token);
        $subject = 'Reinitialisation mot de passe - ' . SITE_NAME;
        $body = '<p>Bonjour ' . htmlspecialchars($prenom . ' ' . $nom) . ',</p>'
            . '<p>Cliquez sur le lien suivant pour changer votre mot de passe :</p>'
            . '<p><a href="' . $resetUrl . '">' . $resetUrl . '</a></p>'
            . '<p>Ce lien est valable 60 minutes.</p>';
        
        $this->mailer->send($email, $subject, $body);
    }
    
    private function setRememberMe($userId) {
        // 30jours 
        $_SESSION['remember_me'] = true;
        $_SESSION['remember_me_until'] = time() + 86400 * 30; 
        
        
        $expires = time() + 86400 * 30;
        setcookie('remember_me', 'true', $expires, '/', '', false, true);
    }
    
    private function clearRememberMe() {
        
        unset($_SESSION['remember_me']);
        unset($_SESSION['remember_me_until']);
        
      
        setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    }
}
