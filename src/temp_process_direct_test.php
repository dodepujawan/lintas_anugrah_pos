<?php
require __DIR__ . '/vendor/autoload.php';

$path = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
$cmd = [$path, '--help'];
$process = new Symfony\Component\Process\Process($cmd);
$process->setTimeout(30);
$process->run();

echo 'path=' . $path . PHP_EOL;
echo 'exists=' . (file_exists($path) ? 'yes' : 'no') . PHP_EOL;
echo 'isFile=' . (is_file($path) ? 'yes' : 'no') . PHP_EOL;
echo 'exit=' . $process->getExitCode() . PHP_EOL;
echo 'error=' . trim($process->getErrorOutput()) . PHP_EOL;
echo 'out=' . substr($process->getOutput(), 0, 200) . PHP_EOL;
