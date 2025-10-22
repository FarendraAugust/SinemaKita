<?php
require_once '../../vendor/autoload.php';
session_start();

use Dotenv\Dotenv;
use Google\Client;

// === Load file .env langsung ===
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Inisialisasi Google Client pakai variabel dari .env
$client = new Google\Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']); // callback URL
$client->addScope("email");
$client->addScope("profile");

// Redirect user ke halaman login Google
header('Location: ' . $client->createAuthUrl());
exit;
