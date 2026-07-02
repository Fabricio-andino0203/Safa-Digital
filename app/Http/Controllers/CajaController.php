<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\CajaSesion;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $sesion = CajaSesion::sesionAbierta();

        // Si hay sesión abierta, mostramos movimientos de esa sesión; si no, del día actual
        if ($sesion) {
            $movimientosHoy = CajaMovimiento::with('pedido')
                ->where('caja_sesion_id', $sesion->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $movimientosHoy = CajaMovimiento::with('pedido')
                ->whereDate('fecha', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $totalIngresos = $movimientosHoy->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos  = $movimientosHoy->where('tipo', 'egreso')->sum('monto');
        $balance       = $totalIngresos - $totalEgresos;

        // Cálculos contables globales históricos
        $todosMovimientos = CajaMovimiento::all();

        $balanceEfectivo = $todosMovimientos->filter(function($m) {
            return $m->tipo === 'ingreso' && strtolower($m->referencia) === 'efectivo';
        })->sum('monto') - $todosMovimientos->filter(function($m) {
            return $m->tipo === 'egreso' && strtolower($m->referencia) === 'efectivo';
        })->sum('monto');

        $balanceBancos = $todosMovimientos->filter(function($m) {
            return $m->tipo === 'ingreso' && in_array(strtolower($m->referencia), ['bancos', 'tarjeta', 'transferencia']);
        })->sum('monto') - $todosMovimientos->filter(function($m) {
            return $m->tipo === 'egreso';
        })->sum('monto');

        return view('caja.index', compact(
            'movimientosHoy', 'totalIngresos', 'totalEgresos', 'balance',
            'balanceEfectivo', 'balanceBancos', 'sesion'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo'    => 'required|in:ingreso,egreso',
            'monto'   => 'required|numeric|min:0.01',
            'concepto'=> 'required|string|max:255',
            'metodo'  => 'nullable|string|max:255',
        ]);

        $sesion = CajaSesion::sesionAbierta();

        $movimiento = CajaMovimiento::create([
            'caja_sesion_id' => $sesion?->id,
            'tipo'      => $validated['tipo'],
            'monto'     => $validated['monto'],
            'concepto'  => $validated['concepto'],
            'referencia'=> $validated['tipo'] === 'egreso' ? 'Bancos' : ($validated['metodo'] ?? 'Efectivo'),
            'fecha'     => now()->toDateString(),
        ]);

        return response()->json([
            'success'    => true,
            'movimiento' => $movimiento,
            'ticket_url' => route('caja.ticket', $movimiento->id),
        ]);
    }

    public function descargarTicket($id)
    {
        ini_set('memory_limit', '512M');
        $movimiento = CajaMovimiento::with('pedido')->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket_caja', compact('movimiento'));
        $pdf->setPaper([0, 0, 226.77, 800], 'portrait');

        return $pdf->stream('ticket_caja_' . $movimiento->id . '.pdf');
    }
}
