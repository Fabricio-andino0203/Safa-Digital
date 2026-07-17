<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Identificar y eliminar duplicados de efectivo en movimiento_tesorerias
        // (Movimientos de cuentas tipo 'efectivo' con modulo POS-Venta-Directa o POS-Pedido-Pago)
        \Illuminate\Support\Facades\DB::transaction(function () {
            // Eliminar registros duplicados
            \Illuminate\Support\Facades\DB::table('movimiento_tesorerias')
                ->whereIn('cuenta_id', function ($query) {
                    $query->select('id')
                        ->from('cuenta_financieras')
                        ->where('tipo', 'efectivo');
                })
                ->whereIn('referencia_modulo', ['POS-Venta-Directa', 'POS-Pedido-Pago'])
                ->delete();

            // Recalcular saldo_actual de todas las cuentas financieras
            $cuentas = \Illuminate\Support\Facades\DB::table('cuenta_financieras')->get();
            foreach ($cuentas as $cuenta) {
                $ingresos = \Illuminate\Support\Facades\DB::table('movimiento_tesorerias')
                    ->where('cuenta_id', $cuenta->id)
                    ->where('tipo', 'ingreso')
                    ->sum('monto');

                $egresos = \Illuminate\Support\Facades\DB::table('movimiento_tesorerias')
                    ->where('cuenta_id', $cuenta->id)
                    ->where('tipo', 'egreso')
                    ->sum('monto');

                $nuevoSaldo = $ingresos - $egresos;

                \Illuminate\Support\Facades\DB::table('cuenta_financieras')
                    ->where('id', $cuenta->id)
                    ->update(['saldo_actual' => $nuevoSaldo]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Operación destructiva de un solo sentido (saneamiento de base de datos)
    }
};
