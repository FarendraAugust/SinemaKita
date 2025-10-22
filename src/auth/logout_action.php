<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';

// 🔧 Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
$APP_URL = rtrim($_ENV['APP_URL'], '/');

include '../../utils/connection.php';

// Jika ada id_pengguna di session, hapus remember_token di DB
if (!empty($_SESSION['session_id'])) {
    $stmt = $connection->prepare("UPDATE pengguna SET remember_token = NULL WHERE id_pengguna = ?");
    $stmt->bind_param("i", $_SESSION['session_id']);
    $stmt->execute();
    $stmt->close();
}

// Hapus semua variabel session
$_SESSION = [];
session_unset();

// Hapus cookie session (PHPSESSID)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params["path"],
        'domain' => $params["domain"],
        'secure' => $params["secure"],
        'httponly' => $params["httponly"],
        'samesite' => 'Lax'
    ]);
}

// Hapus cookie remember_token
setcookie('remember_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Hancurkan session di server
session_destroy();
session_write_close();

// Tambahan perlindungan cache browser
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// ✅ Redirect dinamis sesuai APP_URL
header("Location: {$APP_URL}/login");
exit;
