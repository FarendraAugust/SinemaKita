<?php
session_start();
include '../../../utils/connection.php'; // env sudah diload di sini

// Pastikan user sudah login
if (!isset($_SESSION['session_id'])) {
  header("Location: {$_ENV['APP_URL']}/login");
  exit;
}

$id_review   = $_POST['id_review'] ?? null;
$id_pengguna = $_SESSION['session_id'];

// Validasi input
if (!$id_review) {
  $_SESSION['error_msg'] = "ID review tidak ditemukan.";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// Pastikan review milik user yang sedang login
$stmt_check = $connection->prepare("
  SELECT id_review FROM review
  WHERE id_review = ? AND id_pengguna = ?
  LIMIT 1
");
$stmt_check->bind_param("ii", $id_review, $id_pengguna);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
  $_SESSION['error_msg'] = "Kamu tidak punya izin menghapus review ini.";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
$stmt_check->close();

// Hapus review
$stmt_delete = $connection->prepare("DELETE FROM review WHERE id_review = ?");
$stmt_delete->bind_param("i", $id_review);
$hapus = $stmt_delete->execute();

if ($hapus) {
  $_SESSION['success_msg'] = "Review berhasil dihapus.";
} else {
  $_SESSION['error_msg'] = "Gagal menghapus review.";
}

$stmt_delete->close();
$connection->close();

// Kembali ke halaman sebelumnya
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
