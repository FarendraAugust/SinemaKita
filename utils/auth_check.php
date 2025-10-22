<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'utils/connection.php'; // koneksi db

$isLoggedIn = isset($_SESSION['session_id']); // cek id_pengguna bukan username

// cek cookie remember_token
if (!$isLoggedIn && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $connection->prepare("SELECT * FROM pengguna WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // set session
        $_SESSION['session_id'] = $user['id_pengguna'];
        $_SESSION['session_username'] = $user['nama_pengguna'];
        $isLoggedIn = true;

        // perpanjang cookie 30 hari
        setcookie(
            'remember_token',
            $user['remember_token'],
            [
                'expires' => time() + 60*60*24*30, // 30 hari
                'path' => '/',
                'domain' => '', // sesuaikan domain
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    $stmt->close();
}

// ambil role jika sudah login
$role = null;
if ($isLoggedIn) {
    $id = $_SESSION['session_id'];
    $stmt = $connection->prepare("SELECT is_admin FROM pengguna WHERE id_pengguna = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $role = $r['is_admin'] ?? null;
    $stmt->close();
}
