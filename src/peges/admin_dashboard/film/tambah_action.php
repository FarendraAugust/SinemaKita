<?php
session_start();
include '../../../../utils/connection.php';

// Base URL dari .env
$APP_URL = rtrim($_ENV['APP_URL'], '/');

// Ambil data dari form
$nama_film   = $_POST['nama_film'] ?? '';
$deskripsi   = $_POST['deskripsi'] ?? '';
$slug        = $_POST['slug'] ?? '';
$tagArr      = $_POST['tag'] ?? []; // array multiple
$usia        = $_POST['usia'] ?? '';
$negara      = $_POST['negara'] ?? '';
$status      = $_POST['status'] ?? '';
$rilis       = $_POST['rilis'] ?? '';
$klik        = 0; // default selalu 0 untuk film baru

// === Upload Poster ===
if (!empty($_FILES['poster_film']['name'])) {
    $targetDir = __DIR__ . '/../../../../assets/film/'; // gunakan path absolut

    $poster_name   = basename($_FILES['poster_film']['name']);
    $ext           = pathinfo($poster_name, PATHINFO_EXTENSION);
    $filename_only = pathinfo($poster_name, PATHINFO_FILENAME);

    // Bersihkan nama file
    $filename_clean = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);

    $newPoster  = date("YmdHis") . "_" . $filename_clean . "." . $ext;
    $targetFile = $targetDir . $newPoster;

    if (move_uploaded_file($_FILES['poster_film']['tmp_name'], $targetFile)) {
        $poster_name = $newPoster;
    } else {
        $_SESSION['error_msg'] = "Upload poster gagal!";
        header("Location: $APP_URL/admin/film/tambah");
        exit();
    }
} else {
    $_SESSION['error_msg'] = "Poster wajib diupload!";
    header("Location: $APP_URL/admin/film/tambah");
    exit();
}

// === INSERT FILM ===
$stmt = $connection->prepare("
    INSERT INTO film (nama_film, poster_film, deskripsi, slug, usia, negara, status, rilis, klik)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "ssssssssi",
    $nama_film,
    $poster_name,
    $deskripsi,
    $slug,
    $usia,
    $negara,
    $status,
    $rilis,
    $klik
);

try {
    if ($stmt->execute()) {
        $id_film = $stmt->insert_id;

        // Simpan tag ke tabel relasi film_tag
        if (!empty($tagArr)) {
            $rel = $connection->prepare("INSERT INTO film_tag (id_film, id_tag) VALUES (?, ?)");
            foreach ($tagArr as $id_tag) {
                $rel->bind_param("ii", $id_film, $id_tag);
                $rel->execute();
            }
            $rel->close();
        }

        $_SESSION['success_msg'] = "Film berhasil ditambahkan!";
        header("Location: $APP_URL/admin/film");
        exit();
    } else {
        $_SESSION['error_msg'] = "Gagal menambahkan film!";
        header("Location: $APP_URL/admin/film/tambah");
        exit();
    }
} catch (mysqli_sql_exception $ex) {
    $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
    header("Location: $APP_URL/admin/film/tambah");
    exit();
}

$stmt->close();
$connection->close();
