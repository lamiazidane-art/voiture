<?php
$page_title = "Connexion";
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

$auth = new AuthController();
$auth->checkLoggedIn();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->handleLogin($_POST['email'] ?? '', $_POST['password'] ?? '', isset($_POST['remember']));
    
    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        redirect($result['role'] === 'admin' ? 'views/admin/index.php' : 'views/client/index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-back-home">
            <a href="<?= SITE_URL ?>index.php"><i class="fas fa-home"></i> Retour à l'accueil</a>
        </div>
        <div class="auth-brand">
            <div class="brand-icon"><i class="fas fa-car"></i></div>
            <h1 class="brand-name">Messaoudene<span>Car</span></h1>
            <p class="brand-tagline">Accedez a votre compte</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-message error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-options">
                <label class="checkbox-label" for="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <span class="checkbox-custom"></span>
                    Se souvenir de moi
                </label>
                <a class="forgot-link" href="<?= SITE_URL ?>views/auth/forgot-password.php">Mot de passe oublie ?</a>
            </div>

            <button type="submit" class="btn-login">Se connecter</button>
        </form>

        <div class="auth-footer">
            <p>Vous n'avez pas de compte ? <a href="<?= SITE_URL ?>views/auth/register.php">S'inscrire</a></p>
        </div>
    </div>
</div>
<script src="<?= SITE_URL ?>assets/js/auth.js"></script>
</body>
</html>