<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\VentaPos;
use Illuminate\Http\Request;

class HistorialMovimientosController extends Controller
{
    public function index(Request $request)
    {
        $query = CajaMovimiento::with(['pedido.cliente'])
            ->orderBy('created_at', 'desc');

        // Filtro por tipo opcional
        if ($request->filled('tipo')) {
            $tipo = $request->tipo;
            if ($tipo === 'venta') {
                $query->where('concepto', 'LIKE', 'Venta POS%');
            } elseif ($tipo === 'abono') {
                $query->where('concepto', 'LIKE', 'Abono%');
            } elseif ($tipo === 'liquidacion') {
                $query->where('concepto', 'LIKE', 'Liquidación%');
            } elseif ($tipo === 'deposito') {
                $query->where('tipo', 'ingreso')->where('concepto', 'NOT LIKE', 'Venta POS%')->where('concepto', 'NOT LIKE', 'Abono%')->where('concepto', 'NOT LIKE', 'Liquidación%');
            } elseif ($tipo === 'retiro') {
                $query->where('tipo', 'egreso');
            }
        }

        $movimientos = $query->paginate(30);

        return view('caja.historial', compact('movimientos'));
    }

    public function reimprimir($id)
    {
        $movimiento = CajaMovimiento::findOrFail($id);

        // 1. Si es Venta POS, buscar la venta y mandar al ticket de venta POS
        if (preg_match('/Venta POS #(\d+)/i', $movimiento->concepto, $matches)) {
            $ventaId = $matches[1];
            if (VentaPos::where('id', $ventaId)->exists()) {
                return redirect()->route('pos.ticket', $ventaId);
            }
        }

        // 2. Si es Abono o Liquidación de pedido (tiene pedido_id)
        if ($movimiento->pedido_id) {
            return redirect()->route('pago.ticket', $movimiento->id);
        }

        // 3. Si es depósito o retiro manual general
        return redirect()->route('caja.ticket', $movimiento->id);
    }
}
