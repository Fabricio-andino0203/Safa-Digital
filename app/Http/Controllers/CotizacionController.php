<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Cliente;
use App\Models\ProductoVariante;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\PedidoHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CotizacionController extends Controller
{
    public function index()
    {
        $cotizaciones = Cotizacion::with(['cliente', 'detalles.variante.producto', 'pedido'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $clientes = Cliente::all();
        
        // Cargar variantes activas
        $variantes = ProductoVariante::with('producto')
            ->where('activo', true)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'nombre_completo' => $v->nombre_completo,
                'precio' => (float) $v->precio,
                'costo' => (float) $v->costo,
            ]);

        return view('cotizaciones.index', compact('cotizaciones', 'clientes', 'variantes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descuento' => 'nullable|numeric|min:0',
            'validez_dias' => 'nullable|integer|min:1',
            'notas' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.tipo_producto' => 'required|in:Inventario,Libre',
            'detalles.*.producto_variante_id' => 'required_if:detalles.*.tipo_producto,Inventario|nullable|exists:producto_variantes,id',
            'detalles.*.nombre_libre' => 'required_if:detalles.*.tipo_producto,Libre|nullable|string',
            'detalles.*.descripcion_libre' => 'nullable|string',
            'detalles.*.costo_libre' => 'required_if:detalles.*.tipo_producto,Libre|nullable|numeric|min:0',
            'detalles.*.precio_venta' => 'required|numeric|min:0',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.extras' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Generar número de cotización
            $ultimo = Cotizacion::orderBy('id', 'desc')->first();
            $nextNum = $ultimo ? ((int) str_replace('COT-', '', $ultimo->numero_cotizacion)) + 1 : 1;
            $numero_cotizacion = 'COT-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            // Calcular montos
            $subtotal = 0;
            $detallesValidos = [];

            // Receptor/Listener: realiza el cálculo de costo silencioso en backend para seguridad financiera
            foreach ($request->detalles as $det) {
                $lineSubtotal = $det['precio_venta'] * $det['cantidad'];
                $subtotal += $lineSubtotal;

                $costoTotal = 0;
                if ($det['tipo_producto'] === 'Inventario' && !empty($det['producto_variante_id'])) {
                    $variante = ProductoVariante::find($det['producto_variante_id']);
                    if ($variante) {
                        $costoTotal = floatval($variante->costo ?? 0);
                        if (!empty($det['extras'])) {
                            foreach ($det['extras'] as $ex) {
                                $extraRecord = \DB::table('producto_extras')->find($ex['id'] ?? null);
                                $costoExtra = $extraRecord ? floatval($extraRecord->costo) : 0;
                                $cantidadExtra = max(1, intval($ex['cantidad'] ?? 1));
                                $costoTotal += $costoExtra * $cantidadExtra;
                            }
                        }
                    }
                } else {
                    $costoTotal = $det['costo_libre'] ?? 0;
                }

                $detallesValidos[] = [
                    'tipo_producto' => $det['tipo_producto'],
                    'producto_variante_id' => $det['producto_variante_id'] ?? null,
                    'nombre_libre' => $det['nombre_libre'] ?? null,
                    'descripcion_libre' => $det['descripcion_libre'] ?? null,
                    'costo_libre' => $costoTotal,
                    'precio_venta' => $det['precio_venta'],
                    'cantidad' => $det['cantidad'],
                    'subtotal' => $lineSubtotal,
                    'extras' => $det['extras'] ?? null,
                ];
            }

            $descuento = $request->descuento ?? 0;
            $total = max(0, $subtotal - $descuento);

            // Crear cotización (NO afecta inventario ni caja)
            $cotizacion = Cotizacion::create([
                'numero_cotizacion' => $numero_cotizacion,
                'cliente_id' => $request->cliente_id,
                'fecha_emision' => now()->toDateString(),
                'validez_dias' => $request->validez_dias ?? 15,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'estado' => 'Borrador',
                'notas' => $request->notas,
            ]);

            foreach ($detallesValidos as $detalleData) {
                $cotizacion->detalles()->create($detalleData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización guardada correctamente.',
                'cotizacion' => $cotizacion,
                'pdf_url' => route('cotizaciones.pdf', $cotizacion->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Borrador,Enviada,Aceptada,Rechazada'
        ]);

        $cotizacion = Cotizacion::with(['cliente', 'detalles.variante'])->findOrFail($id);

        if ($request->estado === 'Aceptada' && !$cotizacion->pedido_id) {
            try {
                DB::transaction(function() use ($cotizacion) {
                    // 1. Crear Pedido
                    $pedido = Pedido::create([
                        'cliente_id'             => $cotizacion->cliente_id,
                        'prioridad'              => 'Normal',
                        'estado'                 => 'Pendiente',
                        'subtotal'               => $cotizacion->subtotal,
                        'descuento'              => $cotizacion->descuento,
                        'total_pedido'           => $cotizacion->total,
                        'total_abonado'          => 0,
                        'saldo_pendiente'        => $cotizacion->total,
                        'fecha_estimada_entrega' => null,
                        'hora_estimada_entrega'  => null,
                        'notas'                  => "Generado automáticamente desde la Cotización " . $cotizacion->numero_cotizacion . ($cotizacion->notas ? "\nNotas: " . $cotizacion->notas : ""),
                    ]);

                    // Historial de Pedido
                    PedidoHistorial::create([
                        'pedido_id'       => $pedido->id,
                        'usuario_id'      => auth()->id(),
                        'estado_anterior' => null,
                        'estado_nuevo'    => 'Pendiente',
                    ]);

                    // 2. Crear detalles y reservar stock
                    foreach ($cotizacion->detalles as $detalle) {
                        $detalleData = [
                            'pedido_id'     => $pedido->id,
                            'tipo_producto' => $detalle->tipo_producto === 'Inventario' ? 'Inventario' : 'Libre',
                            'cantidad'      => $detalle->cantidad,
                            'precio_venta'  => $detalle->precio_venta,
                            'subtotal'      => $detalle->subtotal,
                        ];

                        if ($detalle->tipo_producto === 'Inventario') {
                            $variante = ProductoVariante::lockForUpdate()->findOrFail($detalle->producto_variante_id);
                            $variante->reservar($detalle->cantidad); // Incrementa stock_reservado

                            $detalleData['producto_variante_id'] = $variante->id;
                            
                            $nombreSnapshot = $variante->nombre_completo;
                            if (!empty($detalle->extras)) {
                                $partesExtras = [];
                                foreach ($detalle->extras as $ex) {
                                    $qty = intval($ex['cantidad'] ?? 1);
                                    if ($qty > 1) {
                                        $partesExtras[] = "{$qty}x {$ex['nombre']}";
                                    } else {
                                        $partesExtras[] = $ex['nombre'];
                                    }
                                }
                                $nombreSnapshot .= ' (' . implode(', ', $partesExtras) . ')';
                            }
                            
                            $detalleData['nombre_snapshot']      = $nombreSnapshot;
                            $detalleData['sku_snapshot']         = $variante->sku;
                            $detalleData['precio_unitario']      = $variante->precio;
                            $detalleData['extras']               = $detalle->extras;
                        } else {
                            $detalleData['nombre_libre']      = $detalle->nombre_libre;
                            $detalleData['descripcion_libre'] = $detalle->descripcion_libre;
                            $detalleData['precio_unitario']   = $detalle->precio_venta;
                        }

                        PedidoDetalle::create($detalleData);
                    }

                    // Incrementar total gastado
                    $pedido->cliente->increment('total_gastado', $pedido->total_pedido);

                    // Automatización de Maquila / Subcontratación
                    \App\Services\MaquilaAutomationService::procesarPedido($pedido);

                    // 4. Vincular Pedido a Cotización
                    $cotizacion->update([
                        'estado' => 'Aceptada',
                        'pedido_id' => $pedido->id
                    ]);
                });
            } catch (\Exception $e) {
                $message = $e->getMessage();
                return response()->json([
                    'success' => false,
                    'message' => str_contains($message, 'Stock insuficiente') ? $message : 'Error al convertir cotización en pedido: ' . $message
                ], 422);
            }
        } else {
            $cotizacion->update(['estado' => $request->estado]);
        }

        $cotizacion->load('pedido');

        return response()->json([
            'success' => true,
            'message' => 'Estado de cotización actualizado a: ' . $cotizacion->estado,
            'cotizacion' => $cotizacion
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descuento' => 'nullable|numeric|min:0',
            'validez_dias' => 'nullable|integer|min:1',
            'notas' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.tipo_producto' => 'required|in:Inventario,Libre',
            'detalles.*.producto_variante_id' => 'required_if:detalles.*.tipo_producto,Inventario|nullable|exists:producto_variantes,id',
            'detalles.*.nombre_libre' => 'required_if:detalles.*.tipo_producto,Libre|nullable|string',
            'detalles.*.descripcion_libre' => 'nullable|string',
            'detalles.*.costo_libre' => 'required_if:detalles.*.tipo_producto,Libre|nullable|numeric|min:0',
            'detalles.*.precio_venta' => 'required|numeric|min:0',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.extras' => 'nullable|array',
        ]);

        $cotizacion = Cotizacion::findOrFail($id);

        if ($cotizacion->estado === 'Aceptada' && $cotizacion->pedido_id) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar una cotización que ya fue aceptada y convertida a pedido.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calcular montos
            $subtotal = 0;
            $detallesValidos = [];

            foreach ($request->detalles as $det) {
                $lineSubtotal = $det['precio_venta'] * $det['cantidad'];
                $subtotal += $lineSubtotal;

                $costoTotal = 0;
                if ($det['tipo_producto'] === 'Inventario' && !empty($det['producto_variante_id'])) {
                    $variante = ProductoVariante::find($det['producto_variante_id']);
                    if ($variante) {
                        $costoTotal = floatval($variante->costo ?? 0);
                        if (!empty($det['extras'])) {
                            foreach ($det['extras'] as $ex) {
                                $extraRecord = \DB::table('producto_extras')->find($ex['id'] ?? null);
                                $costoExtra = $extraRecord ? floatval($extraRecord->costo) : 0;
                                $cantidadExtra = max(1, intval($ex['cantidad'] ?? 1));
                                $costoTotal += $costoExtra * $cantidadExtra;
                            }
                        }
                    }
                } else {
                    $costoTotal = $det['costo_libre'] ?? 0;
                }

                $detallesValidos[] = [
                    'tipo_producto' => $det['tipo_producto'],
                    'producto_variante_id' => $det['producto_variante_id'] ?? null,
                    'nombre_libre' => $det['nombre_libre'] ?? null,
                    'descripcion_libre' => $det['descripcion_libre'] ?? null,
                    'costo_libre' => $costoTotal,
                    'precio_venta' => $det['precio_venta'],
                    'cantidad' => $det['cantidad'],
                    'subtotal' => $lineSubtotal,
                    'extras' => $det['extras'] ?? null,
                ];
            }

            $descuento = $request->descuento ?? 0;
            $total = max(0, $subtotal - $descuento);

            // Actualizar cotización (NO afecta inventario ni caja)
            $cotizacion->update([
                'cliente_id' => $request->cliente_id,
                'validez_dias' => $request->validez_dias ?? 15,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'notas' => $request->notes ?? $request->notas,
            ]);

            // Eliminar detalles anteriores y volver a crearlos
            $cotizacion->detalles()->delete();

            foreach ($detallesValidos as $detalleData) {
                $cotizacion->detalles()->create($detalleData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización actualizada correctamente.',
                'cotizacion' => $cotizacion,
                'pdf_url' => route('cotizaciones.pdf', $cotizacion->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function descargarPDF($id)
    {
        ini_set('memory_limit', '512M');
        $cotizacion = Cotizacion::with(['cliente', 'detalles.variante.producto'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.cotizacion_base', compact('cotizacion'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('cotizacion_'.$cotizacion->numero_cotizacion.'.pdf');
    }
}
