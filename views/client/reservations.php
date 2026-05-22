<?php
/**
 * VUE: Mes réservations - Affiche les réservations de l'utilisateur connecté
 */

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../controllers/ReservationController.php';


$reservationController = new ReservationController();


$filter = $_GET['filter'] ?? 'all';
if ($filter === 'confirmee') {
    $reservations = $reservationController->getConfirmed($_SESSION['user_id']);
} elseif ($filter === 'en_attente') {
    $reservations = $reservationController->getPending($_SESSION['user_id']);
} elseif ($filter === 'terminee') {
    $reservations = $reservationController->getTerminated($_SESSION['user_id']);
} elseif ($filter === 'annulee') {
    $reservations = $reservationController->getCancelled($_SESSION['user_id']);
} else {
    $reservations = $reservationController->getByUserId($_SESSION['user_id']);
}

usort($reservations, function($a, $b) {
    $dateA = strtotime($a['created_at'] ?? $a['date_debut']);
    $dateB = strtotime($b['created_at'] ?? $b['date_debut']);
    return $dateB - $dateA;
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - L'Voiture</title>
<link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
      <!-- MAIN CONTENT -->
    <div class="admin-main">
        <header class="admin-header">
            <button type="button" class="sidebar-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-left">
                <h1>Tableau de bord</h1>
            </div>
            <div class="header-right">
                <span><?php echo date('d/m/Y'); ?></span>
            </div>
        </header>
</div>
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2>Client</h2>
            <p><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Client'); ?></p>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="reservations.php" class="nav-link active">
                <i class="fas fa-calendar-check"></i>
                <span>Mes réservations</span>
            </a>
            <a href="../voitures/index.php" class="nav-link">
                <i class="fas fa-car"></i>
                <span>Réserver une voiture</span>
            </a>
            <a href="../auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <h1>Mes réservations</h1>
            </div>
           
        </header>

        <main class="admin-content">
            <div class="quick-actions">
                <h2><i class="fas fa-filter"></i> Filtrer les réservations</h2>
                <div class="btn-group" style="flex-wrap: wrap;">
                    <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">Toutes</a>
                    <a href="?filter=confirmee" class="btn <?php echo $filter === 'confirmee' ? 'btn-primary' : 'btn-secondary'; ?>">Confirmées</a>
                    <a href="?filter=en_attente" class="btn <?php echo $filter === 'en_attente' ? 'btn-primary' : 'btn-secondary'; ?>">En attente</a>
                    <a href="?filter=terminee" class="btn <?php echo $filter === 'terminee' ? 'btn-primary' : 'btn-secondary'; ?>">Terminées</a>
                    <a href="?filter=annulee" class="btn <?php echo $filter === 'annulee' ? 'btn-primary' : 'btn-secondary'; ?>">Annulées</a>
                </div>
            </div>

            <?php if (empty($reservations)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <img src="../../assets/images/i1.png" alt="aucune réservation" />
                    </div>
                    <div class="empty-text">Aucune réservation trouvée</div>
                    <a href="../voitures/index.php" class="empty-action">Parcourir les voitures</a>
                </div>
            <?php else: ?>
                <div class="cards-grid">
                    <?php foreach ($reservations as $r): ?>
                        <div class="reservation-card" data-reservation='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES); ?>' onclick="openReservationDetailsFromData(this)" style="cursor: pointer;">
                            <div class="card-header">
                                <div class="card-vehicle">
                                    <div class="card-vehicle-name"><?php echo htmlspecialchars($r['modele']); ?></div>
                                    <div class="card-vehicle-meta"><?php echo htmlspecialchars($r['code_reservation']); ?></div>
                                </div>
                                <span class="status-badge status-<?php echo $r['statut']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $r['statut'])); ?>
                                </span>
                            </div>

                            <div class="card-info">
                                <div class="info-row">
                                    <span class="info-label">Réservée le</span>
                                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($r['created_at'] ?? $r['date_debut'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Début</span>
                                    <span class="info-value"><?php echo date('d/m/Y', strtotime($r['date_debut'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Fin</span>
                                    <span class="info-value"><?php echo date('d/m/Y', strtotime($r['date_fin'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Lieu de prise</span>
                                    <span class="info-value"><?php echo htmlspecialchars($r['lieuPriseEnCharge'] ?? 'Non spécifié'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Lieu de retour</span>
                                    <span class="info-value"><?php echo htmlspecialchars($r['lieuRetour'] ?? 'Non spécifié'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Prix total</span>
                                    <span class="info-value price"><?php echo number_format($r['prix_total'], 2); ?> DA</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Modal Détails Réservation -->
<div id="reservationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Détails de la réservation</h2>
            <button type="button" class="modal-close" onclick="closeReservationDetails()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
         
        </div>
    </div>
</div>

<script src="../../assets/js/dashboard.js"></script>
<script>
function openReservationDetailsFromData(element) {
    const reservation = JSON.parse(element.dataset.reservation);
    openReservationDetails(reservation);
}

function openReservationDetails(reservation) {
    const modalBody = document.getElementById('modalBody');
    const statusLabel = reservation.statut.replace('_', ' ').charAt(0).toUpperCase() + reservation.statut.replace('_', ' ').slice(1);
    
    const createdDate = new Date(reservation.created_at || reservation.date_debut);
    const createdAtFormatted = createdDate.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    const startDate = new Date(reservation.date_debut);
    const startTime = startDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    const startFormatted = startDate.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }) + ' ' + startTime;
    
    const endDate = new Date(reservation.date_fin);
    const endTime = endDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    const endFormatted = endDate.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }) + ' ' + endTime;
    
    modalBody.innerHTML = `
        <div class="reservation-details">
            <div class="details-section">
                <h3>Informations Véhicule</h3>
                <p><strong>Modèle:</strong> ${escapeHtml(reservation.modele)}</p>
            </div>
            
            <div class="details-section">
                <h3>Détails Réservation</h3>
                <p><strong>Code:</strong> ${escapeHtml(reservation.code_reservation)}</p>
                <p><strong>Réservée le:</strong> ${createdAtFormatted}</p>
                <p><strong>Début:</strong> ${startFormatted}</p>
                <p><strong>Fin:</strong> ${endFormatted}</p>
            </div>
            
            <div class="details-section">
                <h3>Lieux de Location</h3>
                <p><strong>Lieu de prise:</strong> ${escapeHtml(reservation.lieuPriseEnCharge || 'Non spécifié')}</p>
                <p><strong>Lieu de retour:</strong> ${escapeHtml(reservation.lieuRetour || 'Non spécifié')}</p>
            </div>
            
            <div class="details-section">
                <h3>Montant</h3>
                <p><strong>Prix total:</strong> <span style="font-size: 1.2em; color: #2563eb;">${Number(reservation.prix_total).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} DA</span></p>
                <p><strong>Statut:</strong> <span class="status-badge status-${reservation.statut}">${statusLabel}</span></p>
            </div>
        </div>
    `;
    
    document.getElementById('reservationModal').style.display = 'flex';
}

function closeReservationDetails() {
    document.getElementById('reservationModal').style.display = 'none';
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Fermer le modal en cliquant en dehors
document.addEventListener('click', function(event) {
    const modal = document.getElementById('reservationModal');
    if (event.target === modal) {
        closeReservationDetails();
    }
});
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #fefefe;
    padding: 0;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #64748b;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #334155;
}

.modal-body {
    padding: 20px;
}

.reservation-details {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.details-section {
    padding: 15px;
    background-color: #f8fafc;
    border-radius: 6px;
}

.details-section h3 {
    margin: 0 0 12px 0;
    font-size: 1rem;
    color: #1e293b;
}

.details-section p {
    margin: 8px 0;
    font-size: 0.95rem;
    color: #334155;
}

.details-section strong {
    color: #0f172a;
}

.card-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e2e8f0;
}

.card-actions .btn {
    flex: 1;
}

.reservation-card {
    transition: all 0.3s ease;
}

.reservation-card:hover {
    box-shadow: 0 8px 12px rgba(37, 99, 235, 0.15);
    transform: translateY(-2px);
}
</style>

</body>
</html>