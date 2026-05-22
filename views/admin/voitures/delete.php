<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

include '../../../config.php';
$pdo = getDB();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM voitures WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    header('Location: index.php?success=deleted');
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}
