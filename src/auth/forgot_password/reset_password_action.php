<?php
session_start();

// === Load .env langsung di file ini ===
$envPath = __DIR__ . '/../../../.env'; // sesuaikan path sesuai struktur project kamu
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"');
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

// === Ambil konfigurasi dari .env ===
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/SinemaKita';
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbUser = $_ENV['DB_USERNAME'] ?? 'root';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';
$dbName = $_ENV['DB_DATABASE'] ?? 'sinemakita';

// === Koneksi Database ===
$connection = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($connection->connect_error) {
    die("Koneksi gagal: " . $connection->connect_error);
}

// === Proses Reset Password ===
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($password !== $confirm) {
    $_SESSION['forgot_error'] = "Konfirmasi password tidak cocok!";
    header("Location: $appUrl?pege=reset_password&token=$token");
    exit();
}

// Cek token & expire
$stmt = $connection->prepare("SELECT id_pengguna FROM pengguna WHERE reset_token=? AND reset_expire>NOW() LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['forgot_error'] = "Token tidak valid atau sudah kadaluarsa.";
    header("Location: $appUrl?pege=lupa_password");
    exit();
}

// Hash password baru
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Update password dan hapus token & expire
$update = $connection->prepare("UPDATE pengguna SET password=?, reset_token=NULL, reset_expire=NULL WHERE id_pengguna=?");
$update->bind_param("si", $hashed, $user['id_pengguna']);
$update->execute();

$_SESSION['forgot_success'] = "Password berhasil direset!";
header("Location: $appUrl?pege=login");
exit();
?>
