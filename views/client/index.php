<?php
/**
 
 * Affiche les réservations et statistiques de l'utilisateur connecté
 */

session_start();

// Vérification: utilisateur connecté et client
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../../config.php';
require_once '../../controllers/ReservationController.php';

$reservationController = new ReservationController();

$reservations = $reservationController->getByUserId($_SESSION['user_id']);
$reservations_confirmees = $reservationController->getConfirmed($_SESSION['user_id']);
$reservations_en_attente = $reservationController->getPending($_SESSION['user_id']);


$stats = [
    'total_reservations' => count($reservations),
    'reservations_confirmees' => count($reservations_confirmees),
    'reservations_en_attente' => count($reservations_en_attente)
];

$active_reservation = !empty($reservations_confirmees) ? $reservations_confirmees[0] : null;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - L'Voiture</title>
<link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2>Client</h2>
            <p><?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Client'); ?></p>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php" class="nav-link active">
                <i class="fas fa-chart-line"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="reservations.php" class="nav-link">
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
            <!-- STAT CARDS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div>
                        <h3><?php echo $stats['total_reservations']; ?></h3>
                        <p>Réservations totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3><?php echo $stats['reservations_confirmees']; ?></h3>
                        <p>Confirmées</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <h3><?php echo $stats['reservations_en_attente']; ?></h3>
                        <p>En attente</p>
                    </div>
                </div>
            </div>

            <!-- RESERVATIONS SECTION -->
            <div class="quick-actions">
                <h2><i class="fas fa-list"></i> Vos réservations récentes</h2>
                <?php if ($active_reservation): ?>
                    <div class="reservation-card">
                        <div class="card-header">
                            <div class="card-vehicle">
                                <div class="card-vehicle-name"><?php echo htmlspecialchars($active_reservation['modele']); ?></div>
                                <div class="card-vehicle-meta"><?php echo htmlspecialchars($active_reservation['code_reservation']); ?></div>
                            </div>
                            <span class="status-badge status-<?php echo $active_reservation['statut']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $active_reservation['statut'])); ?>
                            </span>
                        </div>

                        <div class="card-info">
                            <div class="info-row">
                                <span class="info-label">Début</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($active_reservation['date_debut'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Fin</span>
                                <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($active_reservation['date_fin'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Prix total</span>
                                <span class="info-value price"><?php echo number_format($active_reservation['prix_total'], 2); ?> DA</span>
                            </div>
                        </div>

                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--bg-light);">
                            <a href="reservations.php" class="btn btn-primary">Voir toutes mes réservations</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px; background: var(--bg-light); border-radius: var(--radius); margin-top: 20px;">
                        <p style="color: var(--text-secondary); margin-bottom: 16px;">Vous n'avez pas de réservation active</p>
                        <a href="../voitures/index.php" class="btn btn-primary">Parcourir les voitures</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>
<script src="../../assets/js/dashboard.js"></script>
</body>
</html>
