<?php
$page_title = "Inscription";
require_once '../../config.php';
require_once '../../controllers/AuthController.php';

$auth = new AuthController();
$auth->checkLoggedIn();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->handleRegister($_POST);
    
    if (isset($result['errors'])) {
        $errors = $result['errors'];
    } elseif (isset($result['success'])) {
        redirect('views/auth/login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?= SITE_NAME ?></title>
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
            <div class="brand-icon"><i class="fas fa-user-plus"></i></div>
            <h1 class="brand-name">Creer un <span>compte</span></h1>
            <p class="brand-tagline">Rejoignez-nous pour louer votre prochaine voiture</p>
        </div>

        <?php foreach ($errors as $e): ?>
            <div class="alert-message error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($e) ?></span>
            </div>
        <?php endforeach; ?>

        <form method="POST" class="auth-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="prenom">Prenom</label>
                    <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="telephone">Telephone</label>
                <input type="tel" id="telephone" name="telephone" required value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
            </div>
            <div class="form-group">
   <div class="form-group">
    <label for="date_naissance">Date de naissance</label>

    <input 
        type="date"
        id="date_naissance"
        name="date_naissance"
        class="modern-date"
        required
        value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>"
    >
</div>
</div>


            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmer mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>

            <div class="form-options">
                <label class="checkbox-label" for="agree_terms">
                    <input type="checkbox" id="agree_terms" name="agree_terms" required>
                    <span class="checkbox-custom"></span>
                    J'accepte les conditions d'utilisation
                </label>
            </div>

            <button type="submit" class="btn-login"><i class="fas fa-check"></i> Creer mon compte</button>
        </form>

        <div class="auth-footer">
            <p>Vous avez deja un compte ? <a href="<?= SITE_URL ?>views/auth/login.php">Se connecter</a></p>
        </div>
    </div>
</div>
<script src="<?= SITE_URL ?>assets/js/auth.js"></script>
</body>
</html>