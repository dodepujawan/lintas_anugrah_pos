<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$mysqlPath = getenv('MYSQLDUMP_PATH');
var_export(['mysqlPath' => $mysqlPath]);

$cmd = [$mysqlPath, '--host=127.0.0.1', '--port=3306', '--user=admin', '--password=admin', '--single-transaction', '--default-character-set=utf8mb4', 'db_akuntansi'];
try {
    $process = new Symfony\Component\Process\Process($cmd);
    $process->setTimeout(300);
    $process->run();
    echo "exit=" . $process->getExitCode() . "\n";
    echo "error=" . trim($process->getErrorOutput()) . "\n";
    echo "outputHead=" . substr($process->getOutput(), 0, 200) . "\n";
} catch (Throwable $e) {
    echo 'exception=' . $e->getMessage() . "\n";
}
