<?php

require __DIR__ . '/../vendor/autoload.php';


use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$ip = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$database = $_ENV['DB_DATABASE'];
$APP_URL = $_ENV['APP_URL'];

$connection = new mysqli($ip, $username, $password, $database);

if (mysqli_connect_errno()){
    echo "Koneksi database gagal : " . mysqli_connect_error();
}