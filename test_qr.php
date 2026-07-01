<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qrUrl = "http://localhost:8000/pedidos/track/ORD-000004";
$response = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(5)->get("https://api.qrserver.com/v1/create-qr-code/?size=100x100&margin=0&data=" . urlencode($qrUrl));
echo "Status: " . $response->status() . "\n";
echo "Headers: " . json_encode($response->headers()) . "\n";
echo "Body size: " . strlen($response->body()) . "\n";
echo "First 20 bytes: " . bin2hex(substr($response->body(), 0, 20)) . "\n";
