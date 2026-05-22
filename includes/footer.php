<?php $currentYear = date('Y'); ?>

<footer class="site-footer">
    <div class="footer-shell section-content">
        <div class="footer-brand-block">
            <a class="footer-logo" href="<?= SITE_URL ?>index.php">
                <span class="logo-black">Messaoudene</span><span class="logo-yellow">Car</span>
            </a>
            <p>Des véhicules récents, un service simple et transparent pour tous vos déplacements.</p>
        </div>

        <div class="footer-links-block">
            <h3>Navigation</h3>
            <a href="<?= SITE_URL ?>index.php">Accueil</a>
            <a href="<?= SITE_URL ?>views/voitures/index.php">Véhicules</a>
            <a href="<?= SITE_URL ?>aide.php">Aide</a>
            <a href="<?= SITE_URL ?>contact.php">Contact</a>
            <a href="<?= SITE_URL ?>conditions.php">Conditions de location</a>
        </div>

        <div class="footer-links-block">
            <h3>Contact</h3>
            <p>Email: messaoudene@gmail.com</p>
            <p>Téléphone: 0782383488</p>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="section-content footer-bottom-inner">
            <p>© Tous les droits sont réservés - <?= $currentYear ?></p>
            <ul class="footer-bottom-links">
                <li><a href="<?= SITE_URL ?>conditions.php">Mentions legales</a></li>
                <li><a href="<?= SITE_URL ?>conditions.php">Politique de confidentialite</a></li>
            </ul>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>assets/js/style.js"></script>
</body>
</html>