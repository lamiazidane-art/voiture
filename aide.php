<?php
$page_title = "Centre d'aide - Location de voitures";
$show_navbar = true;

require_once 'config.php';
require_once 'includes/header.php';
?>

<section class="help-page">
    <div class="container-aide">

        <!-- Header -->
        <div class="help-header">
            <span class="help-badge">
                <i class="fas fa-circle-question"></i>
                Centre d'aide
            </span>

            <h1>Questions fréquentes</h1>
            <p>
                Retrouvez les réponses aux questions les plus posées concernant
                la réservation, les conditions et votre location.
            </p>
        </div>

        <!-- FAQ -->
        <div class="faq-box">

            <div class="faq-item">
                
                    <h3>Comment réserver une voiture ?</h3>
                   
               
                 <p>
                    Choisissez votre véhicule, sélectionnez les dates puis
                    confirmez votre réservation en quelques clics.
</p> 
            </div>

            <div class="faq-item">
                <h3>Puis-je modifier ou annuler ma réservation ?</h3>
                <p>
                    Oui, depuis votre espace client ou en contactant notre support.
                </p>
            </div>

            <div class="faq-item">
                <h3>Quelles sont les conditions ?</h3>
                <p>
                    Un permis valide est obligatoire. 
                </p>
            </div>

            <div class="faq-item">
                <h3>Où récupérer la voiture ?</h3>
                <p>
                    Le lieu de récupération et retour est indiqué lors de la réservation.
                </p>
            </div>

            <div class="faq-item">
                <h3>Que faire en cas de retard ?</h3>
                <p>
                    Contactez-nous rapidement afin de trouver la meilleure solution.
                </p>
            </div>

        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>