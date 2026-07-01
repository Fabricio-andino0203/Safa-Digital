<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pedido = \App\Models\Pedido::with(['cliente', 'detalles.variante.producto'])->findOrFail(4);
$qrUrl = route('pedidos.track', $pedido->numero_orden);
$qrImgData = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(5)->get("https://api.qrserver.com/v1/create-qr-code/?size=100x100&margin=0&data=" . urlencode($qrUrl))->body();
$qrBase64 = 'data:image/png;base64,' . base64_encode($qrImgData);

$html = view('pdf.ticket_80mm', compact('pedido', 'qrBase64'))->render();
echo substr($html, strpos($html, 'qr-section') - 50, 500);
