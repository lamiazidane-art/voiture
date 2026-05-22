<nav class="navbar">

    <!-- LOGO -->
    <a href="<?= SITE_URL ?>" class="logo">
        <span class="logo-main">Messaoudene</span>
        <span class="logo-accent">Car</span>
    </a>

    <!-- HAMBURGER -->
    <button class="menu-btn" id="menuBtn">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- MENU -->
    <div class="mobile-menu" id="mobileMenu">

        <!-- NAV LINKS -->
        <ul class="nav-links">
            <li><a href="<?= SITE_URL ?>"><i class="fa-solid fa-house"></i> Accueil</a></li>
            <li><a href="<?= SITE_URL ?>views/voitures/index.php"><i class="fa-solid fa-car-side"></i> Véhicules</a></li>
            <li><a href="<?= SITE_URL ?>aide.php"><i class="fa-solid fa-circle-info"></i> Aide</a></li>
            <li><a href="<?= SITE_URL ?>conditions.php"><i class="fa-solid fa-file-signature"></i> Conditions</a></li>
            <li><a href="<?= SITE_URL ?>contact.php"><i class="fa-solid fa-envelope"></i> Contact</a></li>
        </ul>

        <!-- AUTH -->
        <ul class="connexion">

            <?php if (isLogged()): ?>

                <li>
                    <a href="<?= SITE_URL ?>views/client/index.php">
                        <i class="fa-solid fa-user"></i> Mon compte
                    </a>
                </li>

                <?php if (isAdmin()): ?>
                    <li>
                        <a href="<?= SITE_URL ?>views/admin/index.php">
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="<?= SITE_URL ?>views/auth/logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                    </a>
                </li>

            <?php else: ?>

                <li>
                    <a href="<?= SITE_URL ?>views/auth/login.php" class="btn-login-nav">
                        <i class="fa-solid fa-right-to-bracket"></i> Connexion
                    </a>
                </li>

                <li>
                    <a href="<?= SITE_URL ?>views/auth/register.php" class="btn-register-nav">
                        <i class="fa-solid fa-user-plus"></i> Inscription
                    </a>
                </li>

            <?php endif; ?>

        </ul>

    </div>

</nav>