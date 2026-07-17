<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function index(Request $request)
    {
        // Filtros de fecha: si no se proporcionan, últimos 30 días por defecto
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        // Movimientos filtrados por rango de fechas
        $movimientosHoy = CajaMovimiento::with('pedido')
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin)
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalIngresos = $movimientosHoy->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $movimientosHoy->where('tipo', 'egreso')->sum('monto');
        $balance = $totalIngresos - $totalEgresos;

        // Cálculos contables globales históricos por consultas limpias de base de datos
        $balanceEfectivo = CajaMovimiento::where('tipo', 'ingreso')
            ->where(DB::raw('lower(referencia)'), 'efectivo')
            ->sum('monto') - 
            CajaMovimiento::where('tipo', 'egreso')
            ->where(DB::raw('lower(referencia)'), 'efectivo')
            ->sum('monto');

        $balanceBancos = CajaMovimiento::where('tipo', 'ingreso')
            ->whereIn(DB::raw('lower(referencia)'), ['bancos', 'tarjeta', 'transferencia'])
            ->sum('monto') - 
            CajaMovimiento::where('tipo', 'egreso')
            ->sum('monto');

        // Total Depósitos (Hoy)
        $totalIngresosHoy = CajaMovimiento::where('tipo', 'ingreso')
            ->whereDate('fecha', now()->toDateString())
            ->sum('monto');

        return view('caja.index', compact('movimientosHoy', 'totalIngresos', 'totalEgresos', 'balance', 'balanceEfectivo', 'balanceBancos', 'totalIngresosHoy', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Eliminar un movimiento de caja de forma definitiva (Hard Delete).
     * Solo accesible por administradores.
     */
    public function eliminarMovimiento($id)
    {
        $user = auth()->user();
        if ($user->id !== 1 && $user->rol !== 'admin') {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        $movimiento = CajaMovimiento::findOrFail($id);
        $movimiento->delete();

        return response()->json(['success' => true, 'message' => 'Movimiento eliminado permanentemente.']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'required|string|max:255',
            'metodo' => 'nullable|string|max:255',
        ]);

        $movimiento = CajaMovimiento::create([
            'tipo' => $validated['tipo'],
            'monto' => $validated['monto'],
            'concepto' => $validated['concepto'],
            'referencia' => $validated['tipo'] === 'egreso' ? 'Bancos' : ($validated['metodo'] ?? 'Efectivo'),
            'fecha' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'movimiento' => $movimiento,
            'ticket_url' => route('caja.ticket', $movimiento->id)
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
