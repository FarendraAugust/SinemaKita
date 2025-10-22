<?php
session_start();
include '../../../../utils/connection.php';

// Ambil data dari form
$nama_pengguna = $_POST['nama_pengguna'];
$email_pengguna = $_POST['email_pengguna'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];
$role = $_POST['role'] ?? 'user';
$is_admin = ($role === 'admin') ? 1 : 0;

// Validasi konfirmasi password
if ($password !== $confirm) {
    $_SESSION['error_msg'] = "Konfirmasi password tidak cocok!";
    header("Location: /sinemakita/admin/pengguna/tambah");
    exit();
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Default value lainnya
$verified = 0;
$remember_token = null;
$verify_token = bin2hex(random_bytes(16));
$verify_expire = date("Y-m-d H:i:s", strtotime("+1 day"));
$create_at = date("Y-m-d H:i:s");
$update_at = $create_at;

// === Upload Foto Profil (jika ada) ===
$profile_img = null;

if (!empty($_FILES['profile_img']['name'])) {
    $targetDir = "../../../../assets/profile/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $img_name = basename($_FILES['profile_img']['name']);
    $ext = pathinfo($img_name, PATHINFO_EXTENSION);
    $filename_only = pathinfo($img_name, PATHINFO_FILENAME);
    $clean_name = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);

    $newImgName = date("YmdHis") . "_" . $clean_name . "." . $ext;
    $targetFile = $targetDir . $newImgName;

    if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFile)) {
        $profile_img = $newImgName;
    } else {
        $_SESSION['error_msg'] = "Upload foto profil gagal!";
        header("Location: /sinemakita/admin/pengguna/tambah");
        exit();
    }
}

// === INSERT KE DATABASE ===
$stmt = $connection->prepare("
    INSERT INTO pengguna 
    (profile_img, nama_pengguna, email_pengguna, password, is_admin, remember_token, verified, verify_token, verify_expire, create_at, update_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssissssss",
    $profile_img,
    $nama_pengguna,
    $email_pengguna,
    $hashed,
    $is_admin,
    $remember_token,
    $verified,
    $verify_token,
    $verify_expire,
    $create_at,
    $update_at
);

try {
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Pengguna baru berhasil ditambahkan!";
        header("Location: /sinemakita/admin/pengguna");
        exit();
    } else {
        $_SESSION['error_msg'] = "Gagal menambahkan pengguna.";
        header("Location: /sinemakita/admin/pengguna/tambah");
        exit();
    }
} catch (mysqli_sql_exception $ex) {
    if (strpos($ex->getMessage(), 'Duplicate entry') !== false) {
        $_SESSION['error_msg'] = "Email sudah terdaftar!";
    } else {
        $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
    }
    header("Location: /sinemakita/admin/pengguna/tambah");
    exit();
}

$stmt->close();
$connection->close();
?>
