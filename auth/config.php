<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$google_client = new Google\Client();
$google_client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$google_client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$google_client->setRedirectUri('http://localhost/auth/google_callback.php');
$google_client->addScope("email");
$google_client->addScope("profile");
?>
