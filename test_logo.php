<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logo_ruta = get_setting('logo_ruta');
echo "Ruta guardada: " . $logo_ruta . "\n";
$path = public_path($logo_ruta);
echo "Path absoluto: " . $path . "\n";
echo "Existe: " . (file_exists($path) ? 'SI' : 'NO') . "\n";
if (file_exists($path)) {
    echo "Mime: " . mime_content_type($path) . "\n";
    $data = file_get_contents($path);
    echo "Size: " . strlen($data) . "\n";
}

echo "GD extension: " . (extension_loaded('gd') ? 'YES' : 'NO') . "\n";
