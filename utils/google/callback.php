<?php
require_once '../../vendor/autoload.php';
session_start();
include __DIR__ . '/../connection.php';

use Dotenv\Dotenv;
use Google\Client;
use Google\Service\Oauth2;

// === Load file .env langsung di sini ===
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Inisialisasi Google Client pakai variabel dari .env
$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);

if (!isset($_GET['code'])) {
    exit("Kode Google tidak tersedia.");
}

// Ambil token dari Google
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
if (!isset($token['access_token'])) {
    exit("Gagal login dengan Google.");
}

$client->setAccessToken($token['access_token']);
$oauth = new Oauth2($client);
$googleUser = $oauth->userinfo->get();

$email  = $googleUser->email;
$name   = $googleUser->name;
$avatar = $googleUser->picture;

// Cek user di database
$stmt = $connection->prepare("SELECT * FROM pengguna WHERE email_pengguna = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Generate remember token
    $rememberToken = bin2hex(random_bytes(16));

    // Insert akun Google baru
    $stmt = $connection->prepare("
        INSERT INTO pengguna 
        (nama_pengguna, email_pengguna, profile_img, password, is_admin, remember_token, verified, provider, verify_token, verify_expire, reset_token, create_at, update_at) 
        VALUES (?, ?, ?, NULL, 0, ?, 1, 'google', NULL, NULL, NULL, NOW(), NOW())
    ");
    $stmt->bind_param("ssss", $name, $email, $avatar, $rememberToken);
    $stmt->execute();

    $userId = $stmt->insert_id;
    $user = [
        'id_pengguna'   => $userId,
        'nama_pengguna' => $name,
        'email_pengguna'=> $email,
        'profile_img'   => $avatar,
        'verified'      => 1,
        'provider'      => 'google',
        'remember_token'=> $rememberToken
    ];

    $stmt->close();
}

// Jika user sudah ada tapi belum punya remember_token
if (empty($user['remember_token'])) {
    $rememberToken = bin2hex(random_bytes(16));
    $stmt = $connection->prepare("UPDATE pengguna SET remember_token = ? WHERE id_pengguna = ?");
    $stmt->bind_param("si", $rememberToken, $user['id_pengguna']);
    $stmt->execute();
    $user['remember_token'] = $rememberToken;
    $stmt->close();
}

// Set session
$_SESSION['session_id'] = $user['id_pengguna'];
$_SESSION['session_username'] = $user['nama_pengguna'];

// Set cookie remember_token (30 hari)
setcookie(
    'remember_token',
    $user['remember_token'],
    [
        'expires' => time() + 60 * 60 * 24 * 30, // 30 hari
        'path' => '/',
        'domain' => '',       // ubah kalau pakai domain tertentu
        'secure' => false,    // ubah ke true kalau HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]
);

// Redirect ke homepage dari .env
header('Location: ' . $_ENV['APP_URL']);
exit;
