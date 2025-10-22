<?php
session_start();
$APP_URL = rtrim($_ENV['APP_URL'], '/'); // Ambil dari env
include '../../../../../utils/connection.php';

$id = $_POST['id_episode'] ?? null;
$id_film = $_POST['id_film'] ?? null;

if (!$id || !$id_film) {
    $_SESSION['error_msg'] = "ID episode tidak valid!";
    header("Location: {$APP_URL}/admin/episode/detail/" . urlencode($id_film));
    exit();
}

try {
    // Hapus dulu link terkait episode
    $stmt_link = $connection->prepare("DELETE FROM episode_link WHERE id_episode = ?");
    $stmt_link->bind_param("i", $id);
    $stmt_link->execute();
    $stmt_link->close();

    // Hapus episode
    $stmt = $connection->prepare("DELETE FROM episode WHERE id_episode = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_msg'] = "Episode berhasil dihapus!";
    header("Location: {$APP_URL}/admin/episode/detail/" . urlencode($id_film));
    exit();

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_msg'] = "Gagal menghapus episode: " . $e->getMessage();
    header("Location: {$APP_URL}/admin/episode/detail/" . urlencode($id_film));
    exit();
}
