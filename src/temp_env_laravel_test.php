<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class)->bootstrap($app);

var_export(env('MYSQLDUMP_PATH'));
echo PHP_EOL;
var_export(env('DB_USERNAME'));
echo PHP_EOL;
$path = trim((string) env('MYSQLDUMP_PATH', 'mysqldump'), "\" \t");
var_export($path);
echo PHP_EOL;
