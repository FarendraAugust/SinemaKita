<?php
session_start();
include '../../../../utils/connection.php';

$id = $_POST['id_tag'] ?? null;

// Pastikan ID ada
if (!$id) {
    $_SESSION['error_msg'] = "ID tag tidak ditemukan!";
    header("Location: " . (getenv('APP_URL') ?: '/sinemakita') . "/admin/tag");
    exit();
}

try {
    // Gunakan prepared statement agar aman
    $stmt = $connection->prepare("DELETE FROM tag WHERE id_tag = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Data tag berhasil dihapus!";
    } else {
        $_SESSION['error_msg'] = "Gagal menghapus data tag!";
    }

    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
}

$connection->close();

// Redirect dengan APP_URL dari .env
header("Location: " . (getenv('APP_URL') ?: '/sinemakita') . "/admin/tag");
exit();
?>
