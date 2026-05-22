<?php
/*  Page d'accueil - Affiche les véhicules disponibles */

$page_title = "Accueil - Bejaia Location Service";
$body_class = "home";
$show_navbar = true;

require_once 'config.php';
require_once 'controllers/VoitureController.php';
require_once 'includes/header.php';


$voitureController = new VoitureController();
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';

$voituresActives = $voitureController->filtrerEtRechercher($search, $sort);
/* Affiche une carte de véhicule*/
function renderAccueilVoitureCard($voiture) {
    $img_src = vehicle_image_src((string)($voiture['image_url'] ?? ''));
    $titre = htmlspecialchars(trim((string)($voiture['modele'] ?? 'Voiture')));
    $prixJour = number_format((float)($voiture['prix_jour'] ?? 0), 0);
    $kilometrage = number_format((float)($voiture['kilometrage'] ?? 0), 0);
    $carburant = htmlspecialchars($voiture['carburant'] ?? 'Non spécifié');
    $places = (int)($voiture['places'] ?? 0);
    $voitureId = (int)($voiture['id'] ?? 0);
    ?>
    <div class="deals-card">
        <img class="vehicle-thumb" data-zoomable="true" src="<?= htmlspecialchars($img_src) ?>"
             alt="<?= $titre ?>" />
       
        <h4><?= $titre ?></h4>
        <div class="deals-card-grid">
            <div><span><i class="ri-speed-up-line"></i></span><?= $kilometrage ?> km</div>
            <div><span><i class="ri-settings-3-line"></i></span><?= $carburant ?></div>
            <div><span><i class="ri-users-line"></i></span><?= $places ?> places</div>
            <div><span><i class="ri-price-tag-3-line"></i></span><?= $prixJour ?> DA</div>
        </div>
        <hr />
        <div class="deals-card-footer">
            <h3><?= $prixJour ?> Da<span>/jour</span></h3>
            <a href="<?= SITE_URL ?>views/reservation/reserver.php?id=<?= $voitureId ?>">
                Réserver <span><i class="ri-arrow-right-line"></i></span>
            </a>
        </div>
    </div>
    <?php
}

$flash = getFlash();
?>

<main>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>" style="max-width:1200px; margin:20px auto; padding:15px 20px; border-radius:8px;">
        <?= $flash['message'] ?>
    </div>
    <?php endif; ?>

    <!--  HERO section-->
    <section class="hero-section">
        <div class="section-content">
            <div class="hero-details">
                <p class="hero-tag">AOKAS LOCATION SERVICE . Aokas,Béjaïa, Algérie</p>
                <h2 class="title">Roulez partout en Algérie</h2>
                <h3 class="subtitle">Des véhicules d'exception pour chaque destination</h3>
                <p class="description">
                    Louez la voiture de vos rêves pour vos déplacements professionnels ou vos voyages.
                    Large choix de véhicules récents et bien entretenus.
                </p>
                <div class="features">
                <div class="securite">
                    <h1><i class="ri-shield-check-line"></i>securite</h1>
                    <p>Assurance tous risques</p>
                </div>
                <div class="flexibilite">
                    <h1><i class="ri-refresh-line"></i>Flexibilite</h1>
                    <p>Annulation gratuite</p>
                </div>
                <div class="prix">
                    <h1><i class="ri-price-tag-3-line"></i>Meilleure prix</h1>
                    <p>Tarifs compétitifs</p>

                </div>
                </div>
                <div class="buttons">
                    <a href="#vehicules" class="button order-now">Voir nos véhicules</a>
                    <a href="<?= SITE_URL ?>contact.php" class="button contact-us">Nous contacter</a>
                </div>
            </div>
        </div>
    </section>

    <!-- COMMENT ÇA MARCHE  -->
    <section class="section-container about-container">
        <h2 class="section-header">Comment ça marche</h2>
        <p class="section-description">
            Découvrez comment louer une voiture facilement en quelques étapes simples.
            Choisissez votre emplacement, sélectionnez la date de prise en charge
            et réservez la voiture qui correspond à vos besoins.
        </p>
        <div class="about-grid">
            <div class="about-card">
                <span><i class="fa-solid fa-location-dot icon"></i></span>
                <h4>Vérifiez la voiture</h4>
                <p>Choisissez votre emplacement et sélectionnez le véhicule qui correspond à vos besoins.</p>
            </div>
            <div class="about-card active">
                <span><i class="fa-solid fa-calendar icon"></i></span>
                <h4>Sélectionner la date</h4>
                <p>Choisissez vos dates de prise en charge et de retour selon votre planning.</p>
            </div>
            
            <div class="about-card">
                <span><i class="fa-solid fa-car icon"></i></span>
                <h4>Réserver la voiture</h4>
                <p>Confirmez votre réservation et profitez de votre véhicule en toute sérénité.</p>
            </div>
        </div>
    </section>

    <!--  NOS VÉHICULES  -->
    <section class="deals" id="vehicules">
        <div class="section-container deals-container">

            <h2 class="vehicules-titre">Nos véhicules</h2>

            <?php if (empty($voituresActives)): ?>
                <div class="no-vehicules-msg">
                    <i class="fas fa-car"></i>
                    <h3>Aucun véhicule pour le moment</h3>
                    <p>Revenez plus tard, nous ajoutons régulièrement de nouveaux véhicules.</p>
                </div>
            <?php else: ?>
                <form method="GET" class="vehicule-filter-form">

    <input 
        type="text" 
        name="search" 
        placeholder="Rechercher un modèle..."
        value="<?= htmlspecialchars($search) ?>"
    >

    <select name="sort">
        <option value="">Trier par</option>

        <option value="prix_desc"
            <?= $sort === 'prix_desc' ? 'selected' : '' ?>>
            Prix décroissant
        </option>

        <option value="prix_asc"
            <?= $sort === 'prix_asc' ? 'selected' : '' ?>>
            Prix croissant
        </option>
    </select>

    <button type="submit">
        Filtrer
    </button>

</form>
                <div class="deals-carousel">
                    <?php foreach ($voituresActives as $voiture): ?>
                        <?php renderAccueilVoitureCard($voiture); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!--  POURQUOI NOUS CHOISIR -->
    <section class="choose__container" id="choose">
        <div class="choose__image">
            <img src="assets/images/vehicule.jpg" alt="Pourquoi nous choisir" />
        </div>
        <div class="choose__content">
            <h2 class="section__header">Pourquoi nous choisir</h2>
            <p class="section__description">
                Découvrez la différence avec notre service de location. Nous proposons des
                véhicules fiables, un service client exceptionnel et des tarifs compétitifs
                pour une expérience de location sans souci.
            </p>
            <div class="choose__grid">
                <div class="choose__card">
                    <span><i class="ri-customer-service-line"></i></span>
                    <div>
                        <h4>Support client</h4>
                        <p>Notre équipe dédiée est disponible 24h/24 et 7j/7 pour vous assister.</p>
                    </div>
                </div>
                <div class="choose__card">
                    <span><i class="ri-map-pin-line"></i></span>
                    <div>
                        <h4>Plusieurs agences</h4>
                        <p>Des points de prise en charge et de retour pratiques selon vos besoins.</p>
                    </div>
                </div>
                <div class="choose__card">
                    <span><i class="ri-wallet-line"></i></span>
                    <div>
                        <h4>Meilleur prix</h4>
                        <p>Des tarifs compétitifs et un excellent rapport qualité-prix pour chaque location.</p>
                    </div>
                </div>
              
                <div class="choose__card">
                    <span><i class="ri-verified-badge-line"></i></span>
                    <div>
                        <h4>Marques certifiées</h4>
                        <p>Choisissez parmi des marques reconnues et des véhicules bien entretenus.</p>
                    </div>
                </div>
               
            </div>
        </div>
    </section>


</main>


<?php include 'includes/footer.php'; ?>
