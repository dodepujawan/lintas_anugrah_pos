<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

var_export(getenv('MYSQLDUMP_PATH'));
echo PHP_EOL;
var_export($_ENV['MYSQLDUMP_PATH'] ?? null);
echo PHP_EOL;
var_export($_SERVER['MYSQLDUMP_PATH'] ?? null);
echo PHP_EOL;
var_export($_ENV['DB_USERNAME'] ?? null);
echo PHP_EOL;
var_export($_SERVER['DB_USERNAME'] ?? null);
echo PHP_EOL;
var_export(getenv('DB_USERNAME'));
echo PHP_EOL;
