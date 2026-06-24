<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        // Balance del día actual
        $movimientosHoy = CajaMovimiento::with('pedido')
            ->whereDate('fecha', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get();

        $totalIngresos = $movimientosHoy->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $movimientosHoy->where('tipo', 'egreso')->sum('monto');
        $balance = $totalIngresos - $totalEgresos;

        return view('caja.index', compact('movimientosHoy', 'totalIngresos', 'totalEgresos', 'balance'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'fecha' => 'required|date'
        ]);

        $movimiento = CajaMovimiento::create($validated);

        return response()->json(['success' => true, 'movimiento' => $movimiento]);
    }
}
