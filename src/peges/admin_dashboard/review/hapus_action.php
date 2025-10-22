<?php
session_start();
include '../../../../utils/connection.php';

// Ambil base URL dari .env
$base_url = getenv('APP_URL') ?: '/sinemakita';

$id_review = $_POST['id_review'] ?? null;

if (!$id_review) {
    $_SESSION['error_msg'] = "ID review tidak valid!";
    header("Location: {$base_url}/admin/review");
    exit();
}

// Ambil id_film dari review sebelum dihapus
$result = mysqli_query($connection, "SELECT id_film FROM review WHERE id_review = " . (int)$id_review);
$review = mysqli_fetch_assoc($result);
$id_film = $review['id_film'] ?? null;

if (!$id_film) {
    $_SESSION['error_msg'] = "Review tidak ditemukan!";
    header("Location: {$base_url}/admin/review");
    exit();
}

try {
    $stmt = $connection->prepare("DELETE FROM review WHERE id_review = ?");
    $stmt->bind_param("i", $id_review);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_msg'] = "Data review berhasil dihapus!";
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_msg'] = "Gagal menghapus review: " . $e->getMessage();
}

// Redirect ke halaman detail film
header("Location: {$base_url}/admin/review/detail/{$id_film}");
exit();
?>
