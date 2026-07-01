<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $request = Illuminate\Http\Request::create('/pedidos/4/ticket', 'GET');
    $response = $app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 500) {
        if ($response->exception) {
            echo "Exception: " . $response->exception->getMessage() . "\n";
            echo "Line: " . $response->exception->getFile() . ":" . $response->exception->getLine() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Fatal Exception: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
