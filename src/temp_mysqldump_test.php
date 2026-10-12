<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Process\Process;

$mysqlDump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
$cmd = [$mysqlDump, '--host=127.0.0.1', '--port=3306', '--user=admin', '--password=admin', '--single-transaction', '--default-character-set=utf8mb4', 'db_akuntansi'];

$process = new Process($cmd);
$process->setTimeout(300);
$process->run();

echo "exit=" . $process->getExitCode() . "\n";
echo "output=" . substr($process->getOutput(), 0, 2000) . "\n";
echo "error=" . substr($process->getErrorOutput(), 0, 2000) . "\n";
