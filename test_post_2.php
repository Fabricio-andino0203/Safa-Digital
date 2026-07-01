<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::create('/', 'GET')); // Boot app

try {
    $request = Illuminate\Http\Request::create('/pedidos', 'POST', [
        'prioridad' => 'Normal',
        'subtotal' => 150,
        'descuento' => 0,
        'total_pedido' => 150,
        'detalles' => [
            ['tipo_producto' => 'Libre', 'nombre_libre' => 'Test', 'cantidad' => 1, 'precio_venta' => 150]
        ]
    ]);
    
    // Fake auth if needed
    auth()->loginUsingId(1);

    $controller = app(\App\Http\Controllers\PedidoController::class);
    $response = $controller->store($request);
    
    echo "STATUS: " . $response->getStatusCode() . "\n";
    echo "CONTENT-TYPE: " . $response->headers->get('Content-Type') . "\n";
    echo "BODY:\n";
    echo substr($response->getContent(), 0, 500) . "\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . " - " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
