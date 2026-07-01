<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/pedidos', 'POST', [
    'prioridad' => 'Normal',
    'subtotal' => 150,
    'descuento' => 0,
    'total_pedido' => 150,
    'detalles' => [
        ['tipo_producto' => 'Libre', 'nombre_libre' => 'Test', 'cantidad' => 1, 'precio_venta' => 150]
    ]
]);
$request->headers->set('Accept', 'application/json');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

$response = $kernel->handle($request);

echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT-TYPE: " . $response->headers->get('Content-Type') . "\n";
echo "BODY:\n";
echo substr($response->getContent(), 0, 500) . "\n";
