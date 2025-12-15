<?php
require_once 'config.php';  

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

$pdo = new PDO(
  "mysql:host=$host;dbname=$dbname;charset=utf8mb4",  // ← Uses $_ENV vars
  $username,                                         // ← Uses $_ENV
  $password,                                         // ← Uses $_ENV
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]
);

// Pour que DATE_FORMAT renvoie la date en français
$pdo->query("SET lc_time_names = 'fr_FR';");

#echo "✅ Secure connection: " . $_ENV['DB_NAME'] . "\n";  // Test line
?>
