<?php
session_start();
include '../../../utils/connection.php'; // env sudah diload di sini

// Pastikan user sudah login
if (!isset($_SESSION['session_id'])) {
  header("Location: {$_ENV['APP_URL']}/login");
  exit;
}

$id_pengguna = $_SESSION['session_id'];
$id_film     = $_POST['id_film'] ?? null;
$rating      = $_POST['rating'] ?? 0;
$komentar    = trim($_POST['komentar'] ?? '');

// Validasi input
if (!$id_film || $komentar === '') {
  $_SESSION['error_msg'] = "Silakan isi semua kolom review.";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// Cek apakah pengguna sudah pernah mereview film ini
$stmt_check = $connection->prepare("
  SELECT id_review 
  FROM review 
  WHERE id_pengguna = ? AND id_film = ?
  LIMIT 1
");
$stmt_check->bind_param("ii", $id_pengguna, $id_film);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
  // Kalau sudah pernah mereview → update review
  $row = $result->fetch_assoc();
  $id_review = $row['id_review'];

  $stmt_update = $connection->prepare("
    UPDATE review
    SET rating = ?, komentar = ?
    WHERE id_review = ? AND id_pengguna = ?
  ");
  $stmt_update->bind_param("isii", $rating, $komentar, $id_review, $id_pengguna);
  $stmt_update->execute();
  $stmt_update->close();

  $_SESSION['success_msg'] = "Review berhasil diperbarui.";
} else {
  // Tambahkan review baru
  $stmt_insert = $connection->prepare("
    INSERT INTO review (id_film, id_pengguna, rating, komentar)
    VALUES (?, ?, ?, ?)
  ");
  $stmt_insert->bind_param("iiis", $id_film, $id_pengguna, $rating, $komentar);
  $stmt_insert->execute();
  $stmt_insert->close();

  $_SESSION['success_msg'] = "Review berhasil ditambahkan.";
}

$stmt_check->close();
$connection->close();

// Redirect ke halaman sebelumnya
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
