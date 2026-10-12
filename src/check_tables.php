<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db_name = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'] ?? 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db_name", $user, $pass);
    $result = $pdo->query('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()');
    echo "Tabel yang ada:\n";
    foreach ($result->fetchAll() as $row) {
        echo "- " . $row['TABLE_NAME'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
