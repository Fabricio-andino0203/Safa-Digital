<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\PedidoArchivo;
use App\Models\ProductoVariante;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'detalles.variante.producto', 'archivos'])->get()->groupBy('estado');
        $plantillas = \App\Models\MensajePlantilla::where('activa', true)->get();
        return view('pedidos.index', compact('pedidos', 'plantillas'));
    }

    public function store(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'cliente_id'             => 'nullable|exists:clientes,id',
                'nuevo_cliente_nombre'   => 'required_without:cliente_id|nullable|string',
                'nuevo_cliente_telefono' => 'nullable|string',
                'nuevo_cliente_email'    => 'nullable|email',
                'prioridad'              => 'required|in:Normal,Urgente,Alta Prioridad',
                'fecha_estimada_entrega' => 'nullable|date',
                'hora_estimada_entrega'  => 'nullable|date_format:H:i',
                'notas'                  => 'nullable|string',
                'subtotal'               => 'required|numeric|min:0',
                'descuento'              => 'required|numeric|min:0',
                'total_pedido'           => 'required|numeric|min:0',

                // Detalles
                'detalles'                        => 'required|array|min:1',
                'detalles.*.tipo_producto'        => 'required|in:Inventario,Libre',
                'detalles.*.producto_variante_id' => 'nullable|required_if:detalles.*.tipo_producto,Inventario|exists:producto_variantes,id',
                'detalles.*.nombre_libre'         => 'nullable|required_if:detalles.*.tipo_producto,Libre|string',
                'detalles.*.descripcion_libre'    => 'nullable|string',
                'detalles.*.cantidad'             => 'required|integer|min:1',
                'detalles.*.precio_venta'         => 'required|numeric|min:0',
                'detalles.*.extras'               => 'nullable|array',

                // Archivos adjuntos
                'archivos'        => 'nullable|array',
                'archivos.*'      => 'file|mimes:jpeg,png,jpg,pdf|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . $validator->errors()->first()
                ], 422);
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            $total_abonado = 0; // Se fuerza a 0 porque los abonos ahora se hacen desde POS
            $saldo_pendiente = $validated['total_pedido'];

            $clienteId = $validated['cliente_id'] ?? null;
            if (!$clienteId) {
                // Crear el cliente sobre la marcha
                $nuevoCliente = \App\Models\Cliente::create([
                    'nombre'   => $validated['nuevo_cliente_nombre'] ?? 'Cliente General',
                    'telefono' => $validated['nuevo_cliente_telefono'] ?? null,
                    'email'    => $validated['nuevo_cliente_email'] ?? null,
                ]);
                $clienteId = $nuevoCliente->id;
            }

            $pedido = Pedido::create([
                'cliente_id'             => $clienteId,
                'prioridad'              => $validated['prioridad'],
                'estado'                 => 'Pendiente',
                'subtotal'               => $validated['subtotal'],
                'descuento'              => $validated['descuento'],
                'total_pedido'           => $validated['total_pedido'],
                'total_abonado'          => $total_abonado,
                'saldo_pendiente'        => $saldo_pendiente,
                'fecha_estimada_entrega' => $validated['fecha_estimada_entrega'] ?? null,
                'hora_estimada_entrega'  => $validated['hora_estimada_entrega'] ?? null,
                'notas'                  => $validated['notas'] ?? null,
            ]);

            \App\Models\PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'usuario_id'      => auth()->id(),
                'estado_anterior' => null,
                'estado_nuevo'    => 'Pendiente',
            ]);

            // ── Detalles + Reserva de stock ────────────────────────────────────
            foreach ($validated['detalles'] as $item) {
                $detalleData = [
                    'pedido_id'     => $pedido->id,
                    'tipo_producto' => $item['tipo_producto'],
                    'cantidad'      => $item['cantidad'],
                    'precio_venta'  => $item['precio_venta'],
                    'subtotal'      => $item['cantidad'] * $item['precio_venta'],
                    'extras'        => $item['extras'] ?? null,
                ];

                if ($item['tipo_producto'] === 'Inventario') {
                    $variante = ProductoVariante::lockForUpdate()->findOrFail($item['producto_variante_id']);
                    $variante->reservar($item['cantidad']); // Incrementa stock_reservado

                    $detalleData['producto_variante_id'] = $variante->id;
                    $detalleData['nombre_snapshot']      = $variante->nombre_completo;
                    $detalleData['sku_snapshot']         = $variante->sku;
                    $detalleData['precio_unitario']      = $variante->precio;
                } else {
                    $detalleData['nombre_libre']      = $item['nombre_libre'];
                    $detalleData['descripcion_libre'] = $item['descripcion_libre'] ?? null;
                    $detalleData['precio_unitario']   = $item['precio_venta']; // Coste no rastreado
                }

                PedidoDetalle::create($detalleData);
            }

            // ── Archivos ───────────────────────────────────────────────────────
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    $ruta = \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->putFile('pedidos/disenos', $file);
                    PedidoArchivo::create([
                        'pedido_id'       => $pedido->id,
                        'ruta'            => $ruta,
                        'nombre_original' => $file->getClientOriginalName(),
                        'tipo'            => $file->extension(),
                    ]);
                }
            }

            // ── Pago a Tesorería eliminado ─────────────────────────────────────
            // El registro del abono inicial se eliminó porque ahora todo abono se procesa
            // explícitamente desde la pantalla de POS.

            // ── Total gastado del cliente ──────────────────────────────────────
            $pedido->cliente->increment('total_gastado', $validated['total_pedido']);

            DB::commit();

            // Notificar al administrador sobre el nuevo pedido pendiente
            $admin = \App\Models\User::find(1);
            if ($admin) {
                $admin->notify(new \App\Notifications\PedidoPendienteNotification(
                    $pedido->id,
                    $pedido->numero_orden,
                    $pedido->cliente->nombre,
                    $pedido->total_pedido
                ));
            }

            $whatsapp_url = '';
            if ($pedido->cliente && $pedido->cliente->telefono) {
                $plantilla = \App\Models\MensajePlantilla::where('evento', 'Pedido Creado')->first();
                $mensaje = $plantilla ? $plantilla->contenido : "Hola {cliente}. Tu pedido #{orden} ha sido registrado exitosamente.";
                
                $mensaje = str_replace('{cliente}', $pedido->cliente->nombre, $mensaje);
                $mensaje = str_replace('{orden}', $pedido->numero_orden, $mensaje);
                $mensaje = str_replace('{fecha_entrega}', $pedido->fecha_estimada_entrega ? $pedido->fecha_estimada_entrega->format('d/m/Y') : 'Pendiente', $mensaje);
                $mensaje = str_replace('{total}', number_format($pedido->total_pedido, 2), $mensaje);
                $mensaje = str_replace('{abonado}', number_format($pedido->total_abonado, 2), $mensaje);
                $mensaje = str_replace('{saldo}', number_format($pedido->saldo_pendiente, 2), $mensaje);
                $mensaje = str_replace('{empresa}', get_setting('nombre_comercial', 'Safa Digital'), $mensaje);

                $telefono = preg_replace('/[^0-9]/', '', $pedido->cliente->telefono);
                $whatsapp_url = "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado exitosamente.',
                'pedido'  => $pedido->load(['cliente', 'detalles.variante', 'archivos']),
                'ticket_url' => route('pedidos.ticket', $pedido->id),
                'whatsapp_url' => $whatsapp_url
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:Pendiente,Diseño,Esperando Aprobación,Producción,Pausado,Listo para Entrega,Entregado,Cancelado'
        ]);

        $pedido = Pedido::with('detalles.variante')->findOrFail($id);
        $estadoAnterior = $pedido->estado;
        $estadoNuevo    = $validated['estado'];

        try {
            DB::beginTransaction();

            // ── Lógica de movimiento de stock (sólo Inventario) ──────────────────
            if ($estadoNuevo === 'Entregado' && $estadoAnterior !== 'Entregado') {
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->tipo_producto === 'Inventario' && $detalle->variante) {
                        $detalle->variante->confirmarEntrega($detalle->cantidad);
                    }
                }
            } elseif ($estadoNuevo === 'Cancelado' && !in_array($estadoAnterior, ['Cancelado', 'Entregado'])) {
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->tipo_producto === 'Inventario' && $detalle->variante) {
                        $detalle->variante->liberarReserva($detalle->cantidad);
                    }
                }
            } elseif ($estadoAnterior === 'Cancelado' && !in_array($estadoNuevo, ['Cancelado', 'Entregado'])) {
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->tipo_producto === 'Inventario' && $detalle->variante) {
                        $detalle->variante->reservar($detalle->cantidad);
                    }
                }
            }

            $pedido->update(['estado' => $estadoNuevo]);

            \App\Models\PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'usuario_id'      => auth()->id(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $estadoNuevo,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'estado' => $pedido->estado]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function descargarTicket($id)
    {
        ini_set('memory_limit', '512M');
        $pedido = Pedido::with(['cliente', 'detalles.variante.producto'])->findOrFail($id);
        
        try {
            $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(120)->margin(0)->generate(route('pedidos.track', $pedido->numero_orden)));
        } catch (\Exception $e) {
            $qrCode = null;
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket_80mm', compact('pedido', 'qrCode'));
        $pdf->setPaper([0, 0, 226.77, 800], 'portrait');

        return $pdf->stream('ticket_'.$pedido->numero_orden.'.pdf');
    }

    public function descargarA4($id)
    {
        ini_set('memory_limit', '512M');
        $pedido = Pedido::with(['cliente', 'detalles.variante.producto'])->findOrFail($id);
        
        try {
            $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->margin(0)->generate(route('pedidos.track', $pedido->numero_orden)));
        } catch (\Exception $e) {
            $qrCode = null;
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.orden_a4', compact('pedido', 'qrCode'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('orden_'.$pedido->numero_orden.'.pdf');
    }

    public function cancelar(Request $request, $id)
    {
        $pedido = Pedido::with('detalles.variante')->findOrFail($id);

        if ($pedido->estado === 'Cancelado') {
            return back()->with('error', 'El pedido ya está cancelado.');
        }

        DB::beginTransaction();
        try {
            $estadoAnterior = $pedido->estado;

            // 1. Cambiar estado a Cancelado y guardar motivo
            $pedido->update([
                'estado' => 'Cancelado',
                'motivo_cancelacion' => $request->input('motivo_cancelacion'),
            ]);

            // 2. Liberar stock reservado si no estaba ya entregado ni cancelado
            if ($estadoAnterior !== 'Entregado') {
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->tipo_producto === 'Inventario' && $detalle->variante) {
                        $detalle->variante->liberarReserva($detalle->cantidad);
                    }
                }
            }

            // 3. Registrar en historial
            \App\Models\PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'usuario_id'      => auth()->id(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'Cancelado',
            ]);

            DB::commit();

            return back()->with('success', 'Pedido cancelado con éxito y stock liberado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar el pedido: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::with('detalles.variante')->findOrFail($id);

            // Liberar stock reservado si el pedido no estaba ya entregado ni cancelado
            if (!in_array($pedido->estado, ['Entregado', 'Cancelado'])) {
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->tipo_producto === 'Inventario' && $detalle->variante) {
                        $detalle->variante->liberarReserva($detalle->cantidad);
                    }
                }
            }

            // Eliminar archivos físicos si existen
            foreach ($pedido->archivos as $archivo) {
                Storage::disk(config('filesystems.default'))->delete($archivo->ruta);
            }

            // Eliminar registro del pedido (detalles y archivos se eliminan en cascada si está configurado, o manualmente aquí)
            $pedido->archivos()->delete();
            $pedido->detalles()->delete();
            $pedido->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pedido eliminado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 422);
        }
    }

    public function track($numero_orden)
    {
        $pedido = Pedido::with(['cliente', 'detalles.variante.producto', 'historiales.usuario', 'movimientosCaja'])
            ->where('numero_orden', $numero_orden)
            ->first();

        if (!$pedido) {
            return response()->view('pedidos.track_404', [], 404);
        }

        return view('pedidos.track', compact('pedido'));
    }

    public function updateFechaEntrega(Request $request, $id)
    {
        $request->validate([
            'fecha_entrega' => 'required|date',
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido->update([
            'fecha_entrega' => $request->fecha_entrega
        ]);

        return response()->json([
            'success' => true,
            'fecha_entrega' => $pedido->fecha_entrega->format('Y-m-d'),
            'pedido' => $pedido->load(['cliente', 'detalles.variante.producto', 'archivos']),
        ]);
    }

    public function uploadFiles(Request $request, $id)
    {
        $request->validate([
            'archivos' => 'required|array|min:1',
            'archivos.*' => 'required|file|max:10240', // Max 10MB
        ]);

        $pedido = Pedido::findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($request->file('archivos') as $file) {
                $nombreOriginal = $file->getClientOriginalName();
                $ext = $file->getClientOriginalExtension();
                $ruta = $file->store('pedidos_archivos', 'public');

                $pedido->archivos()->create([
                    'ruta' => $ruta,
                    'nombre_original' => $nombreOriginal,
                    'tipo' => $ext,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'archivos' => $pedido->archivos()->get(),
                'pedido' => $pedido->load(['cliente', 'detalles.variante.producto', 'archivos']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function descargarArchivo($id)
    {
        $archivo = \App\Models\PedidoArchivo::findOrFail($id);

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($archivo->ruta)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($archivo->ruta, $archivo->nombre_original);
        }

        return abort(404, 'El archivo no se encontró en el disco del servidor.');
    }
}
