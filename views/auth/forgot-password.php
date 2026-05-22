<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

$auth = new AuthController();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->handleForgotPassword($_POST['email'] ?? '');
    
    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublie - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-back-home">
            <a href="<?= SITE_URL ?>index.php"><i class="fas fa-home"></i> Retour à l'accueil</a>
        </div>
        <div class="auth-brand">
            <h1 class="brand-name">Mot de passe <span>oublie</span></h1>
        </div>

        <?php if ($success): ?>
            <div class="alert-message success"><span> un lien de reinitialisation a ete envoye (verifier votre spam email).</span></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-message error"><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn-login">Envoyer le lien</button>
        </form>

        <div class="auth-footer">
            <a href="<?= SITE_URL ?>views/auth/login.php">Retour a la connexion</a>
        </div>
    </div>
</div>
<script src="<?= SITE_URL ?>assets/js/auth.js"></script>
</body>
</html>
