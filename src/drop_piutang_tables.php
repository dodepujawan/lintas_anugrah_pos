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

    // Drop piutangs related tables
    $pdo->exec('DROP TABLE IF EXISTS piutang_pembayarans');
    $pdo->exec('DROP TABLE IF EXISTS piutangs');

    // Remove from migrations table
    $pdo->exec("DELETE FROM migrations WHERE migration = '2026_06_24_000000_create_piutangs_tables'");

    echo "✅ Tabel piutangs dan piutang_pembayarans berhasil dihapus!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
