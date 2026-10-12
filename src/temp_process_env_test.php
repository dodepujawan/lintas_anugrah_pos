<?php
require __DIR__ . '/vendor/autoload.php';

echo 'cwd=' . getcwd() . PHP_EOL;
echo 'sapi=' . php_sapi_name() . PHP_EOL;
echo 'php_ini=' . php_ini_loaded_file() . PHP_EOL;
echo 'disable=' . ini_get('disable_functions') . PHP_EOL;

echo 'before=' . var_export(getenv('MYSQLDUMP_PATH'), true) . PHP_EOL;

if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
    echo 'after=' . var_export(getenv('MYSQLDUMP_PATH'), true) . PHP_EOL;
} else {
    echo 'dotnev=no' . PHP_EOL;
}

$path = trim(getenv('MYSQLDUMP_PATH') ?: 'mysqldump', "\" \t");
$cmd = [$path, '--help'];
$process = new Symfony\Component\Process\Process($cmd);
$process->setTimeout(60);
$process->run();

echo 'cmd=' . var_export($cmd, true) . PHP_EOL;
echo 'exit=' . $process->getExitCode() . PHP_EOL;
echo 'error=' . trim($process->getErrorOutput()) . PHP_EOL;
echo 'outputHead=' . substr($process->getOutput(), 0, 500) . PHP_EOL;
