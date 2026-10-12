<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = config('database.default');
$connection = config("database.connections.$db");
print_r(['active' => $db, 'connection' => $connection]);
if ($db === 'sqlite') {
    $rows = Illuminate\Support\Facades\DB::select("PRAGMA table_info(coas)");
} else {
    $rows = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM coas');
}
print_r($rows);
