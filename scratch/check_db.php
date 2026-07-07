<?php
use App\Models\VentaPos;
use App\Models\CajaMovimiento;

$ventas = VentaPos::all();
echo "=== VENTAS POS ===\n";
foreach ($ventas as $v) {
    echo "ID: {$v->id}, Total: {$v->total}, Estado: {$v->estado}, Metodo: {$v->metodo_pago}, CajaSesion: {$v->caja_sesion_id}\n";
}

$movimientos = CajaMovimiento::all();
echo "\n=== CAJA MOVIMIENTOS ===\n";
foreach ($movimientos as $m) {
    echo "ID: {$m->id}, Tipo: {$m->tipo}, Monto: {$m->monto}, Concepto: {$m->concepto}, Referencia: {$m->referencia}\n";
}
