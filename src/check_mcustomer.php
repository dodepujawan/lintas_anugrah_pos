<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'] ?? 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);

    $result = $pdo->query('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "mcustomer"');
    $row = $result->fetch();

    if ($row) {
        echo "✅ Table mcustomer ada\n";
        $count = $pdo->query('SELECT COUNT(*) as cnt FROM mcustomer')->fetch();
        echo "Total: " . $count['cnt'] . " rows\n";

        // Show sample data
        $sample = $pdo->query('SELECT CUSTOMER, NAMACUST FROM mcustomer LIMIT 5');
        if ($sample->rowCount() > 0) {
            echo "\nSample data:\n";
            foreach ($sample as $row) {
                echo "- " . $row['CUSTOMER'] . " - " . $row['NAMACUST'] . "\n";
            }
        }
    } else {
        echo "❌ Table mcustomer TIDAK ada\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
