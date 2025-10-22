<?php
session_start();
$APP_URL = rtrim($_ENV['APP_URL'], '/'); // Ambil dari env
include '../../../../../utils/connection.php';

// Ambil data episode
$id_film        = $_POST['id_film'] ?? null;
$nomor_episode  = $_POST['nomor_episode'] ?? null;
$download_url   = $_POST['download_url'] ?? null;

// Ambil data link server
$nama_server    = $_POST['nama_server'] ?? [];
$url_video      = $_POST['url'] ?? [];

// Validasi
if (empty($id_film) || empty($nomor_episode)) {
    $_SESSION['error_msg'] = "Data episode wajib diisi!";
    header("Location: {$APP_URL}/admin/episode/detail/tambah/" . urlencode($id_film));
    exit();
}

try {
    // Insert episode + download URL
    $stmt = $connection->prepare("
        INSERT INTO episode (id_film, nomor_episode, download_url) 
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $id_film, $nomor_episode, $download_url);
    $stmt->execute();
    $id_episode = $stmt->insert_id;
    $stmt->close();

    // Insert link server (video)
    if (!empty($nama_server) && !empty($url_video)) {
        $stmt_link = $connection->prepare("
            INSERT INTO episode_link (id_episode, nama_server, url_video) 
            VALUES (?, ?, ?)
        ");
        for ($i = 0; $i < count($nama_server); $i++) {
            $server = $nama_server[$i] ?? '';
            $video  = $url_video[$i] ?? '';

            if (!empty($server) && !empty($video)) {
                $stmt_link->bind_param("iss", $id_episode, $server, $video);
                $stmt_link->execute();
            }
        }
        $stmt_link->close();
    }

    $_SESSION['success_msg'] = "Episode berhasil ditambahkan!";
    header("Location: {$APP_URL}/admin/episode/detail/" . urlencode($id_film));
    exit();

} catch (mysqli_sql_exception $ex) {
    $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
    header("Location: {$APP_URL}/admin/episode/detail/tambah/" . urlencode($id_film));
    exit();
}

$connection->close();
