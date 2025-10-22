<?php
session_start();
require '../../../../utils/connection.php'; // pastikan dotenv sudah diload di sini

$APP_URL = rtrim($_ENV['APP_URL'], '/');

// Validasi ID
if (!isset($_POST['id_banner']) || !is_numeric($_POST['id_banner'])) {
    $_SESSION['error_msg'] = "ID banner tidak valid!";
    header("Location: {$APP_URL}/admin/banner");
    exit();
}

$id = (int)$_POST['id_banner'];

// Ambil data file lama
$stmt = $connection->prepare("SELECT banner_file FROM banner WHERE id_banner = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

// Hapus file fisik jika ada
if ($data && !empty($data['banner_file'])) {
    $file_path = realpath(__DIR__ . '/../../../../assets/banner/' . $data['banner_file']);
    if ($file_path && file_exists($file_path)) {
        unlink($file_path);
    }
}

// Hapus data banner dari database
$stmt = $connection->prepare("DELETE FROM banner WHERE id_banner = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$_SESSION['success_msg'] = "Banner berhasil dihapus!";
header("Location: {$APP_URL}/admin/banner");
exit();
?>
