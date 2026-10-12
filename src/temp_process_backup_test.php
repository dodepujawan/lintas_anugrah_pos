<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$dbHost = trim(getenv('DB_HOST') ?: '127.0.0.1');
$dbPort = trim(getenv('DB_PORT') ?: '3306');
$dbName = trim(getenv('DB_DATABASE') ?: '');
$dbUser = trim(getenv('DB_USERNAME') ?: '');
$dbPass = trim(getenv('DB_PASSWORD') ?: '');
$path = trim(getenv('MYSQLDUMP_PATH') ?: 'mysqldump', '" \t');

echo "MYSQLDUMP_PATH=" . var_export($path, true) . PHP_EOL;
echo "DB_USERNAME=" . var_export($dbUser, true) . PHP_EOL;

$cmd = [$path, "--host={$dbHost}", "--port={$dbPort}", "--user={$dbUser}", "--password={$dbPass}", '--single-transaction', '--default-character-set=utf8mb4', $dbName];
$process = new Symfony\Component\Process\Process($cmd);
$process->setTimeout(300);
$process->run();

echo 'exit=' . $process->getExitCode() . PHP_EOL;
echo 'error=' . trim($process->getErrorOutput()) . PHP_EOL;
echo 'output=' . substr($process->getOutput(), 0, 400) . PHP_EOL;
