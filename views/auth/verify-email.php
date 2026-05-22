<?php
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

$auth = new AuthController();
$result = $auth->verifyEmail($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification email - <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>assets/css/auth.css">
</head>
<body>

<section class="auth-compact">

    <div class="hero-text" style="text-align: center;">
        <div class="auth-topbar">
            <a href="<?= SITE_URL ?>"><i class="fa-solid fa-arrow-left"></i> Retour à l'accueil</a>
        </div>
        
        <div class="title">
            <h1>Vérification Email</h1>
            <p class="subtitle">Votre email est en cours de vérification</p>
        </div>

        <div style="padding: 10px; border-radius: 6px; margin-bottom: 10px; background: <?= $result['success'] ? '#e5ffe8' : '#ffe5e5' ?>; color: <?= $result['success'] ? '#1a7f2e' : '#c00' ?>; font-size: 14px;">
            <?= htmlspecialchars($result['message']) ?>
        </div>

        <p style="text-align: center; margin-top: 16px; font-size: 14px; color: var(--text-color);">
            <a href="<?= SITE_URL ?>views/auth/login.php" style="color: var(--secondary); text-decoration: none;">Aller à la connexion</a>
        </p>

    </div>

</section>

<?php if ($result['success']): ?>
<script>
setTimeout(function () {
    window.location.href = '<?= SITE_URL ?>views/auth/login.php';
}, 2000);
</script>
<?php endif; ?>

<script defer src="<?= SITE_URL ?>assets/js/auth.js"></script>

</body>
</html>