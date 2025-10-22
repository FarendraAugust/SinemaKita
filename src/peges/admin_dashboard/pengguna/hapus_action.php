<?php
session_start();
include '../../../../utils/connection.php'; // sudah memuat $APP_URL dari .env

$id = $_POST['id_pengguna'] ?? null;

if (!$id) {
    $_SESSION['error_msg'] = "ID pengguna tidak valid!";
    header("Location: $APP_URL/admin/pengguna");
    exit();
}

// Ambil data pengguna dari database
$query = $connection->prepare("SELECT profile_img, provider FROM pengguna WHERE id_pengguna = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();

if ($data) {
    // Hapus foto profil jika provider default (bukan Google)
    if ($data['provider'] === 'default' && !empty($data['profile_img'])) {
        $file_path = realpath(__DIR__ . '/../../../../assets/profile/' . $data['profile_img']);
        
        if ($file_path && file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Hapus data pengguna
    $delete = $connection->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
    $delete->bind_param("i", $id);
    $delete->execute();

    $_SESSION['success_msg'] = "Data pengguna berhasil dihapus!";
} else {
    $_SESSION['error_msg'] = "Pengguna tidak ditemukan!";
}

// Redirect ke halaman pengguna
header("Location: {$APP_URL}/admin/pengguna");
exit();
