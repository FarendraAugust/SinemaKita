<?php
session_start();

// Base URL dari .env

include '../../../../utils/connection.php';
$APP_URL = rtrim($_ENV['APP_URL'], '/');

$id = (int)$_POST['id_film'];

// Ambil poster lama
$query = mysqli_query($connection, "SELECT poster_film FROM film WHERE id_film = '$id'");
$data  = mysqli_fetch_assoc($query);

if ($data && !empty($data['poster_film'])) {
    // Path absolut ke file poster
    $file_path = __DIR__ . '/../../../../assets/film/' . $data['poster_film'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Hapus data film dari database
mysqli_query($connection, "DELETE FROM film WHERE id_film = '$id'");

$_SESSION['success_msg'] = "Data film berhasil dihapus!";
header("Location: $APP_URL/admin/film");
exit();
