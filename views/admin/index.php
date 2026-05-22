<?php
/**
 * VUE: Tableau de bord administrateur
 * Affiche les statistiques et gère les véhicules
 */

session_start();

// Vérification: utilisateur connecté et admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../controllers/AdminController.php';


$adminController = new AdminController();


$stats = $adminController->getDashboard();


$allReservations = $adminController->listReservations();
usort($allReservations, function($a, $b) {
    $dateA = strtotime($a['created_at'] ?? $a['date_debut']);
    $dateB = strtotime($b['created_at'] ?? $b['date_debut']);
    return $dateB - $dateA;
});
$latestReservations = array_slice($allReservations, 0, 5);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - L'Voiture</title>
<link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin</h2>
            <p><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Admin'); ?></p>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php" class="nav-link active">
                <i class="fas fa-chart-line"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="voitures/index.php" class="nav-link">
                <i class="fas fa-car"></i>
                <span>Voitures</span>
            </a>
            <a href="reservations.php" class="nav-link">
                <i class="fas fa-calendar-check"></i>
                <span>Réservations</span>
            </a>
            <a href="../auth/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </nav>
    </aside>

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

        <main class="admin-content">
            <!-- STAT CARDS - Affiche les statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <div>
                        <h3><?php echo $stats['voitures']['total']; ?></h3>
                        <p>Total des voitures</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3><?php echo $stats['voitures']['actifs']; ?></h3>
                        <p>Voitures actives</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div>
                        <h3><?php echo $stats['reservations']['total']; ?></h3>
                        <p>Total des réservations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <h3><?php echo $stats['reservations']['en_attente']; ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="quick-actions">
                <h2><i class="fas fa-bolt"></i> Actions rapides</h2>
                <div class="actions-grid">
                    <a href="voitures/add.php" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Ajouter voiture</span>
                    </a>
                    <a href="voitures/index.php" class="action-card">
                        <i class="fas fa-list"></i>
                        <span>Gérer voitures</span>
                    </a>
                    <a href="reservations.php" class="action-card">
                        <i class="fas fa-calendar"></i>
                        <span>Voir réservations</span>
                    </a>
                </div>
            </div>

            <!-- DERNIÈRES RÉSERVATIONS -->
            <div class="recent-reservations">
                <h2><i class="fas fa-history"></i> Dernières réservations</h2>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Véhicule</th>
                                <th>Réservée le</th>
                                <th>Début</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($latestReservations)): ?>
                                <tr><td colspan="5" class="text-center">Aucune réservation</td></tr>
                            <?php else: ?>
                                <?php foreach ($latestReservations as $r): ?>
                                    <tr data-reservation='<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES); ?>' onclick="openAdminReservationDetailsFromData(this)" style="cursor: pointer;">
                                        <td><?php echo htmlspecialchars($r['prenom'] . ' ' . $r['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($r['modele']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($r['created_at'] ?? $r['date_debut'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($r['date_debut'])); ?></td>
                                        <td><span class="status-badge status-<?php echo $r['statut']; ?>"><?php echo ucfirst(str_replace('_', ' ', $r['statut'])); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="action-link">
                    <a href="reservations.php">Voir toutes les réservations →</a>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Détails Réservation Admin -->
<div id="adminReservationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Détails de la réservation</h2>
            <button type="button" class="modal-close" onclick="closeAdminReservationDetails()">&times;</button>
        </div>
        <div class="modal-body" id="adminModalBody">
         
        </div>
    </div>
</div>


<script src="../../assets/js/dashboard.js"></script>
<script>
function openAdminReservationDetailsFromData(element) {
    const reservation = JSON.parse(element.dataset.reservation);
    openAdminReservationDetails(reservation);
}

function openAdminReservationDetails(reservation) {
    const modalBody = document.getElementById('adminModalBody');
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
                <a href="reservations.php?action=confirm&id=${reservation.id}" class="btn-confirm" onclick="return confirm('Confirmer cette réservation ?')">Confirmer</a>
                <a href="reservations.php?action=cancel&id=${reservation.id}" class="btn-cancel" onclick="return confirm('Annuler cette réservation ?')">Annuler</a>
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
    
    document.getElementById('adminReservationModal').style.display = 'flex';
}

function closeAdminReservationDetails() {
    document.getElementById('adminReservationModal').style.display = 'none';
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


document.addEventListener('click', function(event) {
    const modal = document.getElementById('adminReservationModal');
    if (event.target === modal) {
        closeAdminReservationDetails();
    }
});
</script>
</body>
</html>
