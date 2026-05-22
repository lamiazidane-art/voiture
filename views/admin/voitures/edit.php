<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

require_once dirname(__DIR__, 3) . '/config.php';
require_once dirname(__DIR__, 3) . '/controllers/AdminController.php';
require_once dirname(__DIR__, 3) . '/models/Voiture.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$adminController = new AdminController();
$voitureModel = new Voiture();
$voiture = $voitureModel->getById($_GET['id']);
$error = $success = '';

if (!$voiture) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagePath = $voiture['image_url'] ?? null;
    
    // Upload nouvelle image
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Erreur lors du téléchargement de l\'image.';
        } else {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (!in_array($ext, $allowed)) {
                $error = 'Format image invalide. Utilisez: jpg, jpeg, png, webp ou gif.';
            } else {
                $targetDir = dirname(__DIR__, 3) . '/assets/images/voitures';
                is_dir($targetDir) || mkdir($targetDir, 0777, true);
                
                $fileName = 'voiture_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetDir . '/' . $fileName)) {
                    // Supprime ancienne image
                    if (!empty($voiture['image_url']) && !preg_match('#^https?://#i', $voiture['image_url'])) {
                        $oldFile = dirname(__DIR__, 3) . '/assets/images/' . ltrim($voiture['image_url'], '/');
                        is_file($oldFile) && @unlink($oldFile);
                    }
                    $imagePath = 'voitures/' . $fileName;
                } else {
                    $error = 'Impossible d\'enregistrer l\'image.';
                }
            }
        }
    }
    
    // Mise à jour
    if (empty($error)) {
        $data = [
            'modele' => $_POST['modele'],
            'prix_jour' => $_POST['prix_jour'],
            'carburant' => $_POST['carburant'],
            'places' => $_POST['places'],
            'kilometrage' => $_POST['kilometrage'],
            'caution' => $_POST['caution'],
            'image_url' => $imagePath,
            'status' => $_POST['status']
        ];
        
        $result = $adminController->updateVoiture($_GET['id'], $data);
        
        if ($result['success']) {
            $success = $result['message'];
            $voiture = $voitureModel->getById($_GET['id']);
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Éditer une Voiture - Admin</title>
<link rel="stylesheet" href="../../../assets/css/dashboard.css">
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
            <a href="../index.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Tableau de bord</span></a>
            <a href="index.php" class="nav-link active"><i class="fas fa-car"></i><span>Voitures</span></a>
            <a href="../reservations.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Réservations</span></a>
            <a href="../../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-header">
            <button type="button" class="sidebar-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-left">
                <h1>Éditer: <?= htmlspecialchars($voiture['modele'] ?? '') ?></h1>
            </div>
        </header>

        <main class="admin-content">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="section">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Modèle</label>
                            <input type="text" name="modele" value="<?= htmlspecialchars($voiture['modele']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Prix par jour (DA)</label>
                            <input type="number" name="prix_jour" step="0.01" value="<?= $voiture['prix_jour'] ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Carburant</label>
                            <select name="carburant" required>
                                <?php $c = $voiture['carburant']; ?>
                                <option value="essence" <?= $c === 'essence' ? 'selected' : '' ?>>essence</option>
                                <option value="diesel" <?= $c === 'diesel' ? 'selected' : '' ?>>diesel</option>
                                <option value="electrique" <?= $c === 'electrique' ? 'selected' : '' ?>>electrique</option>
                                <option value="hybride" <?= $c === 'hybride' ? 'selected' : '' ?>>hybride</option>
                                <option value="gpl" <?= $c === 'gpl' ? 'selected' : '' ?>>gpl</option>
                                <option value="gnv" <?= $c === 'gnv' ? 'selected' : '' ?>>gnv</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre de places</label>
                            <input type="number" name="places" value="<?= $voiture['places'] ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Kilométrage</label>
                            <input type="number" name="kilometrage" value="<?= $voiture['kilometrage'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Caution (DA)</label>
                            <input type="number" name="caution" step="0.01" value="<?= $voiture['caution'] ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Photo voiture</label>
                        <input type="file" name="image_file" accept="image/*">
                        <?php if (!empty($voiture['image_url'])): ?>
                            <p class="text-muted" style="margin-top:8px;">Image actuelle:</p>
                            <img src="<?= htmlspecialchars(vehicle_image_src((string)$voiture['image_url'])) ?>" alt="Photo voiture" style="max-width:180px; border-radius:8px; margin-top:8px;">
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Statut</label>
                        <select name="status" required>
                            <option value="active" <?= $voiture['status'] === 'active' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactive" <?= $voiture['status'] === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        <a href="index.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
<script src="../../../assets/js/dashboard.js"></script>
</body>
</html>