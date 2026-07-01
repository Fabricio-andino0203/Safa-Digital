<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/pedidos/4/ticket', 'GET');
$response = $app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
if ($response->getStatusCode() === 200) {
    file_put_contents('test_ticket.pdf', $response->getContent());
    echo "PDF generated.\n";
} else {
    echo "Error: " . $response->getStatusCode() . "\n";
}
