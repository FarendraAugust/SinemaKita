<?php
session_start();
include '../../../utils/connection.php'; // env sudah diload di sini

// Pastikan user sudah login
if (!isset($_SESSION['session_id'])) {
  header("Location: {$_ENV['APP_URL']}/login");
  exit;
}

$id_pengguna = $_SESSION['session_id'];
$id_review   = $_POST['id_review'] ?? null;
$id_film     = $_POST['id_film'] ?? null;
$rating      = $_POST['rating'] ?? 0;
$komentar    = trim($_POST['komentar'] ?? '');

// Validasi input
if (!$id_review || $komentar === '') {
  $_SESSION['error_msg'] = "Data review tidak lengkap.";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// Update review
$stmt = $connection->prepare("
  UPDATE review 
  SET rating = ?, komentar = ? 
  WHERE id_review = ? AND id_pengguna = ?
");
$stmt->bind_param("isii", $rating, $komentar, $id_review, $id_pengguna);

if ($stmt->execute()) {
  $_SESSION['success_msg'] = "Review berhasil diperbarui!";
} else {
  $_SESSION['error_msg'] = "Gagal memperbarui review.";
}

$stmt->close();
$connection->close();

// Redirect kembali ke halaman sebelumnya (detail film)
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
