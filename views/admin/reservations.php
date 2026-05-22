<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/controllers/AdminController.php';

$adminController = new AdminController();
$error = $success = '';

// Gestion des actions (confirmer/annuler)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'confirm') {
        $result = $adminController->approveReservation($id);
    } elseif ($action === 'cancel') {
        $result = $adminController->cancelReservation($id);
    }
    
    if (isset($result)) {
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
    
    // Redirection pour éviter la resoumission
    header('Location: reservations.php');
    exit();
}

// Récupération du filtre
$filter = $_GET['filter'] ?? 'all';

// Récupération des réservations
$allReservations = $adminController->listReservations();

// Filtrer les réservations selon le statut
$reservations = $allReservations;
if ($filter !== 'all') {
    $reservations = array_filter($allReservations, function($r) use ($filter) {
        return $r['statut'] === $filter;
    });
}

// Trier par date de création décroissante (plus récentes d'abord)
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
      <title>Réservations - Admin</title>
<link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin</h2>
            <p><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Admin') ?></p>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Tableau de bord</span></a>
            <a href="voitures/index.php" class="nav-link"><i class="fas fa-car"></i><span>Voitures</span></a>
            <a href="reservations.php" class="nav-link active"><i class="fas fa-calendar-check"></i><span>Réservations</span></a>
            <a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-header">
            <button type="button" class="sidebar-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-left"><h1>Gestion des réservations</h1></div>
        </header>

        <main class="admin-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="quick-actions">
                <h2><i class="fas fa-filter"></i> Filtrer par statut</h2>
                <div class="btn-group" style="flex-wrap: wrap;">
                    <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">Tous</a>
                    <a href="?filter=en_attente" class="btn <?php echo $filter === 'en_attente' ? 'btn-primary' : 'btn-secondary'; ?>">En attente</a>
                    <a href="?filter=confirmee" class="btn <?php echo $filter === 'confirmee' ? 'btn-primary' : 'btn-secondary'; ?>">Confirmées</a>
                    <a href="?filter=terminee" class="btn <?php echo $filter === 'terminee' ? 'btn-primary' : 'btn-secondary'; ?>">Terminées</a>
                    <a href="?filter=annulee" class="btn <?php echo $filter === 'annulee' ? 'btn-primary' : 'btn-secondary'; ?>">Annulées</a>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>Code</th><th>Client</th><th>Voiture</th><th>Réservation</th><th>Début</th><th>Fin</th><th>Prix</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservations)): ?>
                            <tr><td colspan="9" class="text-center">Aucune réservation trouvée</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach ($reservations as $r): ?>
                            <tr data-reservation='<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>' onclick="openReservationDetailsFromData(this)" style="cursor: pointer;">
                                <td class="cell-primary"><?= htmlspecialchars($r['code_reservation']) ?></td>
                                <td><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></td>
                                <td><?= htmlspecialchars($r['modele']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($r['created_at'] ?? $r['date_debut'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($r['date_debut'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($r['date_fin'])) ?></td>
                                <td><?= number_format($r['prix_total'], 2) ?> DA</td>
                                <td><span class="status-badge status-<?= $r['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $r['statut'])) ?></span></td>
                                <td onclick="event.stopPropagation();">
                                    <?php if ($r['statut'] === 'en_attente'): ?>
                                    <div class="action-icons">
                                        <a href="?action=confirm&id=<?= $r['id'] ?>" class="icon-btn confirm" title="Confirmer" onclick="return confirm('Confirmer cette réservation ?')"><i class="fas fa-check"></i></a>
                                        <a href="?action=cancel&id=<?= $r['id'] ?>" class="icon-btn cancel" title="Annuler" onclick="return confirm('Annuler cette réservation ?')"><i class="fas fa-times"></i></a>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
            <!-- Le contenu sera inséré ici par JavaScript -->
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
    
    let actionButtons = '';
    if (reservation.statut === 'en_attente') {
        actionButtons = `
            <div class="modal-actions">
                <a href="?action=confirm&id=${reservation.id}" class="btn-confirm" onclick="return confirm('Confirmer cette réservation ?')">Confirmer</a>
                <a href="?action=cancel&id=${reservation.id}" class="btn-cancel" onclick="return confirm('Annuler cette réservation ?')">Annuler</a>
            </div>
        `;
    }
    
    modalBody.innerHTML = `
        <div class="reservation-details">
            <div class="details-section">
                <h3>Informations Client</h3>
                <p><strong>Nom:</strong> ${escapeHtml(reservation.nom)}</p>
                <p><strong>Prénom:</strong> ${escapeHtml(reservation.prenom)}</p>
                <p><strong>Email:</strong> ${escapeHtml(reservation.email)}</p>
            </div>
            
            <div class="details-section">
                <h3>Informations Véhicule</h3>
                <p><strong>Modèle:</strong> ${escapeHtml(reservation.modele)}</p>
            </div>
            
            <div class="details-section">
                <h3>Détails Réservation</h3>
                <p><strong>Code:</strong> ${escapeHtml(reservation.code_reservation)}</p>
                <p><strong>Date de création:</strong> ${createdAtFormatted}</p>
                <p><strong>Début:</strong> ${startFormatted}</p>
                <p><strong>Fin:</strong> ${endFormatted}</p>
                <p><strong>Lieu de prise:</strong> ${escapeHtml(reservation.lieuPriseEnCharge || 'Non spécifié')}</p>
                <p><strong>Lieu de retour:</strong> ${escapeHtml(reservation.lieuRetour || 'Non spécifié')}</p>
                <p><strong>Prix total:</strong> ${Number(reservation.prix_total).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} DA</p>
                <p><strong>Statut:</strong> <span class="status-badge status-${reservation.statut}">${statusLabel}</span></p>
            </div>
            
            ${actionButtons}
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


</body>
</html>