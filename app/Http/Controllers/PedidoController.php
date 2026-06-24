<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('cliente')->get()->groupBy('estado');
        return view('pedidos.index', compact('pedidos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'total' => 'required|numeric|min:0',
            'adelanto' => 'required|numeric|min:0',
            'fecha_entrega' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $saldo = $validated['total'] - $validated['adelanto'];

            $pedido = Pedido::create([
                'cliente_id' => $validated['cliente_id'],
                'estado' => 'Pendiente',
                'total' => $validated['total'],
                'adelanto' => $validated['adelanto'],
                'saldo' => $saldo,
                'fecha_entrega' => $validated['fecha_entrega'],
                'notas' => $validated['notas']
            ]);

            if ($validated['adelanto'] > 0) {
                CajaMovimiento::create([
                    'tipo' => 'ingreso',
                    'monto' => $validated['adelanto'],
                    'concepto' => 'Adelanto Pedido #' . $pedido->id,
                    'pedido_id' => $pedido->id,
                    'fecha' => now()->toDateString(),
                ]);
            }

            // Opcional: Actualizar el total gastado del cliente
            $pedido->cliente->increment('total_gastado', $validated['total']);

            DB::commit();

            return response()->json(['success' => true, 'pedido' => $pedido]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:Pendiente,Diseño,Aprobación,Producción,Listo,Entregado,Cancelado'
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido->update(['estado' => $validated['estado']]);

        return response()->json(['success' => true, 'estado' => $pedido->estado]);
    }
}
