<?php
session_start();
include '../../../../../utils/connection.php';

$APP_URL = rtrim($_ENV['APP_URL'], '/');

$id_episode     = $_POST['id_episode'] ?? null;
$id_film        = $_POST['id_film'] ?? null;
$nomor_episode  = $_POST['nomor_episode'] ?? null;
$download_url   = $_POST['download_url'] ?? null; // baru
$servers        = $_POST['nama_server'] ?? [];
$urls           = $_POST['url'] ?? [];

if (empty($id_episode) || empty($id_film) || empty($nomor_episode)) {
    $_SESSION['error_msg'] = "Data episode tidak lengkap!";
    header("Location: {$APP_URL}/admin/episode/detail/" . urlencode($id_film));
    exit();
}

try {
    // Update episode: nomor_episode + download_url
    $stmt = $connection->prepare("UPDATE episode SET nomor_episode = ?, download_url = ? WHERE id_episode = ?");
    $stmt->bind_param("ssi", $nomor_episode, $download_url, $id_episode);
    $stmt->execute();
    $stmt->close();

    // Hapus semua link lama
    $stmtDel = $connection->prepare("DELETE FROM episode_link WHERE id_episode = ?");
    $stmtDel->bind_param("i", $id_episode);
    $stmtDel->execute();
    $stmtDel->close();

    // Tambahkan link baru
    if (!empty($servers) && !empty($urls)) {
        $stmtIns = $connection->prepare("INSERT INTO episode_link (id_episode, nama_server, url_video) VALUES (?, ?, ?)");
        foreach ($servers as $i => $server) {
            $url = $urls[$i] ?? null;
            if (!empty($server) && !empty($url)) {
                $stmtIns->bind_param("iss", $id_episode, $server, $url);
                $stmtIns->execute();
            }
        }
        $stmtIns->close();
    }

    $_SESSION['success_msg'] = "Episode berhasil diperbarui!";
    header("Location: {$APP_URL}/admin/episode/detail/" . urlencode($id_film));
    exit();

} catch (mysqli_sql_exception $e) {
    $_SESSION['error_msg'] = "Gagal edit episode: " . $e->getMessage();
    header("Location: {$APP_URL}/admin/episode/detail/" . urlencode($id_film));
    exit();
}

$connection->close();
