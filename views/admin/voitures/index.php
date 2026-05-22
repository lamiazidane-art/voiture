<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

include '../../../config.php';
$pdo = getDB();

$voitures = [];
try {
    $stmt = $pdo->query("SELECT * FROM voitures ORDER BY created_at DESC");
    $voitures = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Voitures - Admin</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
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
            <a href="../index.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="index.php" class="nav-link active">
                <i class="fas fa-car"></i>
                <span>Voitures</span>
            </a>
            <a href="../reservations.php" class="nav-link">
                <i class="fas fa-calendar-check"></i>
                <span>Réservations</span>
            </a>
            <a href="../../auth/logout.php" class="nav-link">
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
                <h1>Gestion des voitures</h1>
            </div>
            <div class="header-right">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter voiture
                </a>
            </div>
        </header>

        <main class="admin-content">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>image</th>
                            <th>Modèle</th>
                            <th>Prix/jour</th>
                            <th>Carburant</th>
                            <th>Places</th>
                            <th>Statut</th>
                            <th>Km</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($voitures)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucune voiture trouvée</td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php foreach ($voitures as $voiture): ?>
                            <tr>
                                <td>
                                    <?php if ($voiture['image_url']): ?>
                                    
                                        <img src="../../../assets/images/<?php echo htmlspecialchars($voiture['image_url']); ?>" alt="Image de <?php echo htmlspecialchars($voiture['modele']); ?>" class="table-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="table-image-placeholder" style="display:none;">Image non trouvée</div>
                                    <?php else: ?>
                                        <div class="table-image-placeholder">Pas d'image</div>
                                    <?php endif; ?>
                                    </td>    
                                <td class="cell-primary"><?php echo htmlspecialchars($voiture['modele']); ?></td>
                                <td><?php echo number_format($voiture['prix_jour'], 2); ?> DA</td>
                                <td><?php echo ucfirst($voiture['carburant']); ?></td>
                                <td><?php echo $voiture['places']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $voiture['status']; ?>">
                                        <?php echo ucfirst($voiture['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($voiture['kilometrage']); ?> km</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="edit.php?id=<?php echo $voiture['id']; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-edit"></i> Éditer
                                        </a>
                                        <a href="delete.php?id=<?php echo $voiture['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Êtes-vous sûr ?">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<script src="../../../assets/js/dashboard.js"></script>
</body>
</html>
