<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

$page_title = "Reinitialiser le mot de passe - " . SITE_NAME;
$auth = new AuthController();

$tokenValidation = $auth->validateResetToken($_GET['token'] ?? '');
$token_valid = $tokenValidation['valid'];
$token_error = $tokenValidation['error'] ?? '';
$token = $tokenValidation['token'] ?? '';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $result = $auth->handleResetPassword(
        $_POST['token'] ?? '',
        $_POST['new_password'] ?? '',
        $_POST['confirm_password'] ?? ''
    );
    
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
    <title><?php echo $page_title; ?></title>
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
            <h1 class="brand-name">Nouveau <span>mot de passe</span></h1>
            <p class="brand-tagline">Creez un mot de passe securise</p>
        </div>

        <?php if ($success): ?>
            <div class="alert-message success"><span>Mot de passe reinitialise avec succes. Redirection...</span></div>
            <script>
                setTimeout(() => {
                    window.location.href = '<?= SITE_URL ?>views/auth/login.php';
                }, 2000);
            </script>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-message error"><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <?php if (!$token_valid && !$success): ?>
            <div class="alert-message error"><span><?= htmlspecialchars($token_error) ?></span></div>
            <div class="auth-footer"><a href="<?= SITE_URL ?>views/auth/login.php">Retour a la connexion</a></div>
        <?php endif; ?>

        <?php if ($token_valid && !$success): ?>
            <form method="POST" class="auth-form">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe *</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <small style="color:#666; margin-top:5px; font-size:0.75rem;">8-20 caracteres, au moins 1 majuscule et 1 chiffre</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn-login">Reinitialiser</button>
            </form>

            <div class="auth-footer"><a href="<?= SITE_URL ?>views/auth/login.php">Retour a la connexion</a></div>
        <?php endif; ?>
    </div>
</div>
<script src="<?= SITE_URL ?>assets/js/auth.js"></script>
</body>
