<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class)->bootstrap($app);
$app->make(Illuminate\Foundation\Bootstrap\LoadConfiguration::class)->bootstrap($app);

$conn = config('database.default');
$config = config("database.connections.{$conn}");

echo 'default=' . var_export($conn, true) . PHP_EOL;
echo 'dbHost=' . var_export($config['host'] ?? null, true) . PHP_EOL;
echo 'dbPort=' . var_export($config['port'] ?? null, true) . PHP_EOL;
echo 'dbName=' . var_export($config['database'] ?? null, true) . PHP_EOL;
echo 'dbUser=' . var_export($config['username'] ?? null, true) . PHP_EOL;
echo 'dbPass=' . var_export($config['password'] ?? null, true) . PHP_EOL;
echo 'mysqldump=' . var_export(env('MYSQLDUMP_PATH', 'mysqldump'), true) . PHP_EOL;

$cmd = [
    trim((string) env('MYSQLDUMP_PATH', 'mysqldump'), "\" \t"),
    "--host={$config['host']}",
    "--port={$config['port']}",
    "--user={$config['username']}",
    "--password={$config['password']}",
    '--single-transaction',
    '--default-character-set=utf8mb4',
    $config['database'],
];

$process = new Symfony\Component\Process\Process($cmd);
$process->setTimeout(300);
$process->run();

echo 'cmd=' . var_export($cmd, true) . PHP_EOL;
echo 'exit=' . $process->getExitCode() . PHP_EOL;
echo 'error=' . trim($process->getErrorOutput()) . PHP_EOL;
echo 'output=' . substr($process->getOutput(), 0, 200) . PHP_EOL;
