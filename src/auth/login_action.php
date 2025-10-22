<?php
session_start();

// === Load ENV langsung di sini ===
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

include __DIR__ . '/../../utils/connection.php';
include __DIR__ . '/../../utils/auth_check.php';

// Ambil variabel dari POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? 1 : 0;
$err = '';

// Query user berdasarkan email
$stmt = $connection->prepare("SELECT * FROM pengguna WHERE email_pengguna = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Validasi user & password
if (!$user) {
    $err .= "Email <b>$email</b> tidak tersedia.";
} elseif (!password_verify($password, $user['password'])) {
    $err .= "Password salah.";
}

// Ambil base URL dari .env
$APP_URL = rtrim($_ENV['APP_URL'], '/');

// Jika gagal login
if ($err != '') {
    $_SESSION['login_error'] = $err;
    header("Location: $APP_URL/login");
    exit();
}

// === Login sukses ===
$_SESSION['session_id'] = $user['id_pengguna'];
$_SESSION['session_username'] = $user['nama_pengguna'];

// Jika "ingat saya" dipilih
if ($remember == 1) {
    $token = bin2hex(random_bytes(32));
    setcookie("remember_token", $token, time() + (60 * 60 * 24 * 30), "/");

    $stmt = $connection->prepare("UPDATE pengguna SET remember_token=? WHERE email_pengguna=?");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();
}

// Redirect ke beranda
header("Location: $APP_URL");
exit();
?>
