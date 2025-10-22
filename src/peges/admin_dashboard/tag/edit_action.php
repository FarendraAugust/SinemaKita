<?php
session_start();
include '../../../../utils/connection.php';

// Ambil base URL dari .env
$base_url = getenv('APP_URL') ?: '/sinemakita';

// Ambil data dari form
$id = $_POST['id'];
$nama = trim($_POST['nama_tag']);
$klik = 0;

// Siapkan statement
$stmt = $connection->prepare("UPDATE tag SET nama_tag = ?, klik = ? WHERE id_tag = ?");
$stmt->bind_param("sis", $nama, $klik, $id);

try {
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Data tag berhasil diedit!";
        header("Location: {$base_url}/admin/tag");
        exit();
    } else {
        $_SESSION['error_msg'] = "Gagal memperbarui data tag!";
        header("Location: {$base_url}/admin/tag/edit/{$id}");
        exit();
    }
} catch (mysqli_sql_exception $ex) {
    if (strpos($ex->getMessage(), 'Duplicate entry') !== false) {
        $_SESSION['error_msg'] = "Tag dengan nama tersebut sudah ada!";
    } else {
        $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
    }
    header("Location: {$base_url}/admin/tag/edit/{$id}");
    exit();
}

$stmt->close();
$connection->close();
