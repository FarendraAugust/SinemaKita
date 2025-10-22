<?php
session_start();
include '../../../../utils/connection.php';

$APP_URL = rtrim($_ENV['APP_URL'], '/'); // base URL dari env
$ASSETS_DIR = __DIR__ . "/../../../../assets/film/"; // path fisik folder film

$id        = $_POST['id_film'];
$nama      = $_POST['nama_film'];
$deskripsi = $_POST['deskripsi'];
$slug      = $_POST['slug'];
$usia      = $_POST['usia'];
$negara    = $_POST['negara'];
$status    = $_POST['status'];
$rilis     = $_POST['rilis'];
$tags      = isset($_POST['tag']) ? $_POST['tag'] : []; // array tag

// Ambil poster lama
$qPoster = mysqli_query($connection, "SELECT poster_film FROM film WHERE id_film = '$id'");
$rowPoster = mysqli_fetch_assoc($qPoster);
$poster = $rowPoster['poster_film'];

// cek upload poster baru
if (!empty($_FILES['poster_film']['name'])) {

    $poster_name   = basename($_FILES['poster_film']['name']);
    $ext           = pathinfo($poster_name, PATHINFO_EXTENSION);
    $filename_only = pathinfo($poster_name, PATHINFO_FILENAME);
    $filename_clean = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);
    $newPoster  = date("YmdHis") . "_" . $filename_clean . "." . $ext;
    $targetFile = $ASSETS_DIR . $newPoster;

    if (move_uploaded_file($_FILES['poster_film']['tmp_name'], $targetFile)) {
        // hapus poster lama
        if (!empty($poster) && file_exists($ASSETS_DIR . $poster)) {
            unlink($ASSETS_DIR . $poster);
        }
        $poster = $newPoster;
    } else {
        $_SESSION['error_msg'] = "Gagal upload poster!";
        header("Location: $APP_URL/admin/film/edit/$id");
        exit();
    }
}

// === UPDATE FILM ===
$stmt = $connection->prepare("UPDATE film 
    SET nama_film=?, poster_film=?, deskripsi=?, slug=?, usia=?, negara=?, status=?, rilis=? 
    WHERE id_film=?");

$stmt->bind_param(
    "ssssssssi", 
    $nama, $poster, $deskripsi, $slug, $usia, $negara, $status, $rilis, $id
);

try {
    if ($stmt->execute()) {
        // Update relasi tag
        mysqli_query($connection, "DELETE FROM film_tag WHERE id_film = '$id'");
        
        if (!empty($tags)) {
            $insertStmt = $connection->prepare("INSERT INTO film_tag (id_film, id_tag) VALUES (?, ?)");
            foreach ($tags as $tag_id) {
                $insertStmt->bind_param("ii", $id, $tag_id);
                $insertStmt->execute();
            }
            $insertStmt->close();
        }

        $_SESSION['success_msg'] = "Data film berhasil diperbarui!";
    } else {
        $_SESSION['error_msg'] = "Gagal update film!";
    }
} catch (mysqli_sql_exception $ex) {
    $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
}

$stmt->close();
$connection->close();

// redirect menggunakan base URL dari env
header("Location: $APP_URL/admin/film");
exit();
