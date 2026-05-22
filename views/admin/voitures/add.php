<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

require_once dirname(__DIR__, 3) . '/config.php';
require_once dirname(__DIR__, 3) . '/controllers/AdminController.php';

$adminController = new AdminController();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagePath = null;
    
    // Upload image
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
                    $imagePath = 'voitures/' . $fileName;
                } else {
                    $error = 'Impossible d\'enregistrer l\'image.';
                }
            }
        }
    }
    
    // Création
    if (empty($error)) {
        $result = $adminController->createVoiture(
            $_POST['modele'], $_POST['prix_jour'], $_POST['carburant'],
            $_POST['places'], $_POST['kilometrage'], $_POST['caution'],
            $_SESSION['user_id'], $imagePath
        );
        
        if ($result['success']) {
            $success = $result['message'];
            header('refresh:2;url=index.php');
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
   <title>Ajouter une Voiture - Admin</title>
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
            <div class="header-left"><h1>Ajouter une voiture</h1></div>
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
                            <input type="text" name="modele" required>
                        </div>
                        <div class="form-group">
                            <label>Prix par jour (DA)</label>
                            <input type="number" name="prix_jour" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Carburant</label>
                            <select name="carburant" required>
                                <option>essence</option><option>diesel</option><option>electrique</option>
                                <option>hybride</option><option>gpl</option><option>gnv</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre de places</label>
                            <input type="number" name="places" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Kilométrage</label>
                            <input type="number" name="kilometrage" required>
                        </div>
                        <div class="form-group">
                            <label>Caution (DA)</label>
                            <input type="number" name="caution" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Photo voiture</label>
                        <input type="file" name="image_file" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Statut</label>
                        <select name="status" required>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Ajouter</button>
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