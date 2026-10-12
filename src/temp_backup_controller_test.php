<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
Illuminate\Support\Facades\Facade::setFacadeApplication($app);
$app->make(Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class)->bootstrap($app);
$app->make(Illuminate\Foundation\Bootstrap\LoadConfiguration::class)->bootstrap($app);
$app->make(Illuminate\Foundation\Bootstrap\RegisterFacades::class)->bootstrap($app);
$app->make(Illuminate\Foundation\Bootstrap\RegisterProviders::class)->bootstrap($app);

$request = Illuminate\Http\Request::create('/backup/run', 'POST');
$controller = new App\Http\Controllers\BackupController();

try {
    $response = $controller->run($request);
    echo 'responseClass=' . get_class($response) . "\n";
    if (method_exists($response, 'getStatusCode')) {
        echo 'status=' . $response->getStatusCode() . "\n";
    }
    if (method_exists($response, 'getFile')) {
        $file = $response->getFile();
        echo 'file=' . $file->getRealPath() . "\n";
    }
} catch (Throwable $e) {
    echo 'exception=' . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
