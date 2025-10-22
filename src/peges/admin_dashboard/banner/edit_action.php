<?php
session_start();
require '../../../../utils/connection.php'; // Sudah otomatis load .env dan buat $APP_URL

echo $APP_URL;

$id_banner = $_POST['id_banner'] ?? null;
$url       = $_POST['url'] ?? '';
$id_film   = $_POST['id_film'] ?? null;

if (!$id_banner) {
    $_SESSION['error_msg'] = "ID banner tidak valid.";
    header("Location: {$APP_URL}/admin/banner");
    exit();
}

// === Ambil data banner lama ===
$qBanner = mysqli_query($connection, "SELECT banner_file FROM banner WHERE id_banner = '$id_banner'");
$rowBanner = mysqli_fetch_assoc($qBanner);
$banner = $rowBanner['banner_file'] ?? null;

// === Cek upload banner baru ===
if (!empty($_FILES['banner_file']['name'])) {
    $targetDir = realpath(__DIR__ . '/../../../../assets/banner/') . '/';

    $banner_name   = basename($_FILES['banner_file']['name']);
    $ext           = pathinfo($banner_name, PATHINFO_EXTENSION);
    $filename_only = pathinfo($banner_name, PATHINFO_FILENAME);

    // Bersihkan nama file dari karakter aneh
    $filename_clean = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);
    $newBanner      = date("YmdHis") . "_" . $filename_clean . "." . $ext;
    $targetFile     = $targetDir . $newBanner;

    if (move_uploaded_file($_FILES['banner_file']['tmp_name'], $targetFile)) {
        // Hapus file lama
        if (!empty($banner) && file_exists($targetDir . $banner)) {
            unlink($targetDir . $banner);
        }
        $banner = $newBanner;
    } else {
        $_SESSION['error_msg'] = "Gagal upload banner!";
        header("Location: {$APP_URL}/admin/banner/edit?id=$id_banner");
        exit();
    }
}

// === Update database ===
$stmt = $connection->prepare("UPDATE banner SET banner_file=?, url=?, id_film=? WHERE id_banner=?");
$stmt->bind_param("ssii", $banner, $url, $id_film, $id_banner);

try {
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Banner berhasil diperbarui!";
    } else {
        $_SESSION['error_msg'] = "Gagal update banner!";
    }
} catch (mysqli_sql_exception $ex) {
    $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
}

$stmt->close();
$connection->close();

header("Location: {$APP_URL}/admin/banner");
exit();
