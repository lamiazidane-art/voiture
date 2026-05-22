<?php
$page_title = "Contact - Bejaia Location Service";
$show_navbar = true;
$body_class = "contact-page";
require_once 'config.php';
require_once 'controllers/AdminController.php';
include 'includes/header.php';

$contactController = new AdminController();
$contactInfo = $contactController->getContactInfo();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $result = $contactController->sendContactMessage($_POST);
    $success = $result['success'];
    $error = $result['success'] ? '' : $result['message'];
}
?>

<section class="contact-pro-wrapper">
    <div class="contact-pro-container">
        <!-- Left Column: Contact Information -->
        <div class="contact-pro-left">
            <div class="contact-pro-header">
                <h1 class="contact-pro-title">Contactez-nous</h1>
                <p class="contact-pro-description">
                    Nous sommes toujours heureux de vous aider. Que ce soit pour une question, 
                    une réservation ou un problème, notre équipe est prête à vous assister.
                </p>
            </div>
<div class="contact-pro-details">
    <h3 class="contact-pro-details-title">Coordonnées</h3>
    <ul class="contact-pro-list">
        <li>
            <span class="contact-pro-label">Responsable :</span>
            <span class="contact-pro-text">Admin.Messaoudene</span>
        </li>

        <li>
            <span class="contact-pro-label">Téléphone :</span>
            <a href="tel:+213000000000" class="contact-pro-link">0782383488</a>
        </li>

        <li>
            <span class="contact-pro-label">Email :</span>
            <a href="mailto:contact@example.com" class="contact-pro-link">messaoudene@gmail.com</a>
        </li>

        <li>
            <span class="contact-pro-label">Adresse :</span>
            <span class="contact-pro-text">J6QR+R9J, Rue des 80 logements, Aokas</span>
        </li>

        <li>
            <span class="contact-pro-label">Horaires :</span>
            <span class="contact-pro-text">Dimanche - Jeudi : 08:00 - 18:00</span>
        </li>
    </ul>
</div>
        </div>

        <!-- Right Column: Contact Form -->
        <div class="contact-pro-right">
            <div class="contact-pro-form-wrapper">
                <h2>Envoyez-nous un Message</h2>
                <p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</p>

                <?php if ($success): ?>
                    <div class="alert alert-success" style="background:#d4edda; color:#155724; padding:12px; border-radius:8px; margin-bottom:15px;">
                        <i class="fas fa-check-circle"></i> Votre message a été envoyé avec succès !
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error" style="background:#f8d7da; color:#721c24; padding:12px; border-radius:8px; margin-bottom:15px;">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="contact-pro-form">
                    <div class="contact-pro-form-row">
                        <div class="contact-pro-form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required>
                        </div>
                        <div class="contact-pro-form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
                        </div>
                    </div>

                    <div class="contact-pro-form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="votre.email@exemple.com" required>
                    </div>

                    <div class="contact-pro-form-group">
                        <label for="sujet">Sujet</label>
                        <input type="text" id="sujet" name="sujet" placeholder="Sujet de votre message" required>
                    </div>

                    <div class="contact-pro-form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Écrivez votre message ici..." required></textarea>
                    </div>

                    <button type="submit" name="submit" class="contact-pro-button">
                        <i class="fas fa-paper-plane"></i> Envoyer le Message
                    </button>
                </form>
            </div>
        </div>
       
    </div>
</section>

<?php include 'includes/footer.php'; ?>