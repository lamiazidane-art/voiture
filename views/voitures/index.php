<?php
$page_title = "Nos véhicules";
require_once '../../config.php';
require_once '../../controllers/VoitureController.php';
require_once '../../includes/header.php';

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';

$voitures = (new VoitureController())->filtrerEtRechercher($search, $sort);


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
             alt="<?= $titre ?>"
             onerror="this.src='assets/images/no-car-placeholder.svg'" />
        
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
?>

<div class="deals" id="vehicules">
    <div class="section-container deals-container">
        <h2 class="vehicules-titre">voitures</h2>
        
        <?php if (empty($voitures)): ?>
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
                <?php foreach ($voitures as $v): ?>
                    <?php renderAccueilVoitureCard($v); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>