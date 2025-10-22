<?php
session_start();

// Load .env
require_once '../../../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../');
$dotenv->load();

// Koneksi database
include '../../../../utils/connection.php';

// Ambil data dari form
$url     = $_POST['url'] ?? '';
$id_film = !empty($_POST['id_film']) ? $_POST['id_film'] : null; // opsional

// === Upload Banner ===
if (!empty($_FILES['banner_file']['name'])) {
    // Folder upload banner (path absolut)
    $uploadDir = realpath(__DIR__ . '/../../../../assets/banner/');

    // Pastikan folder ada
    if (!$uploadDir || !is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName  = basename($_FILES['banner_file']['name']);
    $ext           = pathinfo($originalName, PATHINFO_EXTENSION);
    $filename_only = pathinfo($originalName, PATHINFO_FILENAME);

    // Bersihkan nama file
    $filename_clean = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);

    // Nama baru unik
    $newBanner = date("YmdHis") . "_" . $filename_clean . "." . $ext;
    $targetFile = $uploadDir . DIRECTORY_SEPARATOR . $newBanner;

    if (!move_uploaded_file($_FILES['banner_file']['tmp_name'], $targetFile)) {
        $_SESSION['error_msg'] = "Upload banner gagal!";
        header("Location: " . $_ENV['APP_URL'] . "/admin/banner/tambah");
        exit();
    }

    $banner_name = $newBanner;
} else {
    $_SESSION['error_msg'] = "Banner wajib diupload!";
    header("Location: " . $_ENV['APP_URL'] . "/admin/banner/tambah");
    exit();
}

// === INSERT BANNER ===
$stmt = $connection->prepare("INSERT INTO banner (banner_file, url, id_film) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $banner_name, $url, $id_film);

try {
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Banner berhasil ditambahkan!";
        header("Location: " . $_ENV['APP_URL'] . "/admin/banner");
        exit();
    }
} catch (mysqli_sql_exception $ex) {
    $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
    header("Location: " . $_ENV['APP_URL'] . "/admin/banner/tambah");
    exit();
}

$stmt->close();
$connection->close();
