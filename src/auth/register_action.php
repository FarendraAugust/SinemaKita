<?php
session_start();

// ✅ Load .env langsung di file ini
$env = parse_ini_file(__DIR__ . '/../../.env', true);
$DB_HOST = $env['DB_HOST'] ?? 'localhost';
$DB_USER = $env['DB_USER'] ?? 'root';
$DB_PASS = $env['DB_PASS'] ?? '';
$DB_NAME = $env['DB_NAME'] ?? 'sinemakita';

// ✅ Koneksi database (tanpa include config)
$connection = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($connection->connect_error) {
    die("Koneksi gagal: " . $connection->connect_error);
}

include '../../utils/verify_email.php'; // fungsi kirim email verifikasi

$err = '';
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// 🔍 Validasi input
if (empty($username) || empty($email) || empty($password)) {
    $err .= "Semua field harus diisi!<br>";
}
if ($password !== $confirm_password) {
    $err .= "Password dan konfirmasi tidak sama!<br>";
}

// 🔍 Cek apakah email sudah terdaftar
$check = $connection->prepare("SELECT id_pengguna FROM pengguna WHERE email_pengguna = ?");
$check->bind_param("s", $email);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    $err .= "Email <b>$email</b> sudah terdaftar.<br>";
}

// ❌ Jika error, kembalikan ke halaman register
if ($err !== '') {
    $_SESSION['register_error'] = $err;
    header("Location: /sinemakita/register");
    exit();
}

// 🔐 Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 🔑 Buat token verifikasi dan masa berlaku 7 hari
$token = bin2hex(random_bytes(32));
$expire = date('Y-m-d H:i:s', strtotime('+7 days'));

// 💾 Simpan ke database
$stmt = $connection->prepare("
    INSERT INTO pengguna (nama_pengguna, email_pengguna, password, verified, verify_token, verify_expire)
    VALUES (?, ?, ?, 0, ?, ?)
");
$stmt->bind_param("sssss", $username, $email, $password_hash, $token, $expire);

if ($stmt->execute()) {
    // 🔔 Kirim email verifikasi
    sendVerificationEmail($email, $username, $token);

    // 🔓 Set session
    $_SESSION['session_id'] = $stmt->insert_id;
    $_SESSION['session_username'] = $username;
    $_SESSION['verified'] = 0;

    $_SESSION['register_success'] = "Registrasi berhasil! Email verifikasi telah dikirim ke <b>$email</b>. 
    Anda bisa login sekarang, tetapi belum bisa menonton sebelum memverifikasi akun Anda.";

    header("Location: /sinemakita");
    exit();
} else {
    $_SESSION['register_error'] = "Gagal mendaftar: " . $stmt->error;
    header("Location: /sinemakita/register");
    exit();
}
?>
