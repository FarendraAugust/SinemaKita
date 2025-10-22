<?php
include 'connection.php';

$q = $_GET['q'] ?? '';
$q = mysqli_real_escape_string($connection, $q);

if ($q === '') {
  echo json_encode([]);
  exit;
}

$result = mysqli_query($connection, "
  SELECT id_film, nama_film, slug, poster_film, rilis 
  FROM film 
  WHERE nama_film LIKE '%$q%' 
  ORDER BY rilis DESC 
  LIMIT 6
");

$films = [];
while ($row = mysqli_fetch_assoc($result)) {
  $films[] = $row;
}

header('Content-Type: application/json');
echo json_encode($films);
