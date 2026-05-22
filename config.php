<?php
// config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loadEnv($path) {
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

loadEnv(__DIR__ . '/.env');

// Configuration base de données
define('DB_HOST', $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? 'localhost');
define('DB_NAME', $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'railway');
define('DB_USER', $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? 'root');
define('DB_PASS', $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '');
define('DB_PORT', (int)($_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? 3306));

// Configuration site
define('SITE_URL', 'https://lvoiture-production.up.railway.app/');
define('SITE_NAME', 'Bejaia Location Service');

// SMTP
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', (int)($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');
define('SMTP_ENCRYPTION', strtolower($_ENV['SMTP_ENCRYPTION'] ?? 'tls'));
define('EMAIL_FROM', $_ENV['EMAIL_FROM'] ?? 'noreply@lvoiture-bejaia.com');
define('SENDGRID_API_KEY', $_ENV['SENDGRID_API_KEY'] ?? getenv('SENDGRID_API_KEY') ?? '');
define('SENDGRID_API_URL', 'https://api.sendgrid.com/v3/mail/send');
/*
// Configuration base de données (LOCALHOST - XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 'lvoiture'); // ex: location_voiture
define('DB_USER', 'root');
define('DB_PASS', ''); // par défaut XAMPP n'a pas de mot de passe
define('DB_PORT', 3306);

// Configuration site (LOCAL)
define('SITE_URL', 'http://localhost/lvoiture/');
define('SITE_NAME', 'Bejaia Location Service');

// SMTP (optionnel en local)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', ''); // mets ton email si besoin
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls');
define('EMAIL_FROM', 'noreply@localhost');
*/
// Connexion PDO
function getDB() {
    try {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Erreur de connexion: " . $e->getMessage());
    }
}

// FIN DE LA FONCTION AJOUTEE

function vehicle_image_src($image_url) {
    if (!empty($image_url)) {
        if (preg_match('#^https?://#i', $image_url)) {
            return $image_url;
        }
        return SITE_URL . 'assets/images/' . ltrim($image_url, '/');
    }
    return SITE_URL . 'assets/images/no-car-placeholder.svg';
}

function isLogged() {
    return isset($_SESSION['user_id']);
}

/**
 * Nettoie et échappe les données utilisateur
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

function clean($data) {
    return htmlspecialchars(trim($data));
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function appBaseUrl() {
    return rtrim(SITE_URL, '/');
}
?>
