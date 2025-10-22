<?php
session_start();
include '../../../../utils/connection.php';

// Ambil data dari form
$nama = $_POST['nama_tag'] ?? null;
$klik = 0;

// Pastikan nama tag tidak kosong
if (!$nama) {
    $_SESSION['error_msg'] = "Nama tag tidak boleh kosong!";
    header("Location: " . (getenv('APP_URL') ?: '/sinemakita') . "/admin/tag/tambah");
    exit();
}

try {
    // Gunakan prepared statement
    $stmt = $connection->prepare("INSERT INTO tag (nama_tag, klik) VALUES (?, ?)");
    $stmt->bind_param("si", $nama, $klik);

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Data tag berhasil ditambahkan!";
        header("Location: " . (getenv('APP_URL') ?: '/sinemakita') . "/admin/tag");
        exit();
    } else {
        $_SESSION['error_msg'] = "Gagal menambahkan data tag!";
        header("Location: " . (getenv('APP_URL') ?: '/sinemakita') . "/admin/tag/tambah");
        exit();
    }

} catch (mysqli_sql_exception $ex) {
    if (strpos($ex->getMessage(), 'Duplicate entry') !== false) {
        $_SESSION['error_msg'] = "Tag dengan nama tersebut sudah ada!";
    } else {
        $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
    }

    header("Location: " . (getenv('APP_URL') ?: '/sinemakita') . "/admin/tag/tambah");
    exit();
} finally {
    if (isset($stmt)) $stmt->close();
    $connection->close();
}
?>
