<?php

namespace App\Http\Controllers;

use App\Models\CajaSesion;
use App\Models\CajaMovimiento;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\VentaPos;
use App\Models\VentaPosDetalle;
use App\Models\CuentaFinanciera;
use App\Models\MovimientoTesoreria;
use App\Models\CorteCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // PANTALLA PRINCIPAL DEL POS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Carga la pantalla principal del POS.
     * Envía los productos con sus variantes activas para el selector en Alpine.
     */
    public function index()
    {
        $sesion = CajaSesion::sesionAbierta();

        // Cargamos productos activos con variantes activas que tengan stock disponible.
        // Estructura para Alpine: array de productos, cada uno con sus variantes.
        $productos = Producto::with(['extras', 'categoria.extras', 'variantes' => function ($q) {
            $q->where('activo', true)->orderBy('sku');
        }])
        ->where('activo', true)
        ->whereHas('variantes', function ($q) {
            $q->where('activo', true)->whereRaw('(stock_fisico - stock_reservado) > 0');
        })
        ->orderBy('nombre')
        ->get()
        ->map(function ($producto) {
            // Unificación de consulta de extras directos y heredados (Tarea 2)
            $extrasDisponibles = $producto->extras->merge($producto->categoria ? $producto->categoria->extras : collect())->unique('id')->values();

            return [
                'id'       => $producto->id,
                'nombre'   => $producto->nombre,
                'categoria'=> $producto->categoria ? [
                    'nombre' => $producto->categoria->nombre
                ] : null,
                'imagen'   => $producto->imagen,
                'extras'   => $extrasDisponibles->map(function ($e) {
                    return [
                        'id'     => $e->id,
                        'nombre' => $e->nombre,
                        'costo'  => (float) $e->costo,
                        'precio' => (float) $e->precio,
                    ];
                })->values(),
                'variantes'=> $producto->variantes->map(function ($v) {
                    return [
                        'id'               => $v->id,
                        'sku'              => $v->sku,
                        'nombre_completo'  => $v->nombre_completo,
                        'atributos'        => $v->atributos ?? [],
                        'precio'           => (float) $v->precio,
                        'stock_disponible' => $v->stock_disponible,
                        'imagen'           => $v->imagen,
                    ];
                })->values(),
            ];
        });

        // ── 3. Cargar Pedidos Pendientes para el Modal de Liquidación ─────────────
        $pedidosPendientes = \App\Models\Pedido::with('cliente')
            ->where('saldo_pendiente', '>', 0)
            ->whereNotIn('estado', ['Entregado', 'Cancelado'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($pedido) {
                return [
                    'numero_orden'    => $pedido->numero_orden,
                    'cliente'         => $pedido->cliente ? $pedido->cliente->nombre : 'Venta de Mostrador',
                    'saldo_pendiente' => (float) $pedido->saldo_pendiente,
                    'total_pedido'    => (float) $pedido->total_pedido,
                ];
            });

        $clientes = \App\Models\Cliente::orderBy('nombre')->get();

        // Sugerir monto inicial (último fondo cerrado)
        $ultimaSesion = CajaSesion::where('estado', 'cerrada')->latest('fecha_cierre')->first();
        $fondoSugerido = $ultimaSesion ? (float) $ultimaSesion->monto_contado_fisico : 0.00;

        // Calcular dinero esperado en caja
        $dineroEsperado = 0;
        if ($sesion) {
            $totales = $this->obtenerTotalesSesion($sesion->id);
            $dineroEsperado = $totales['total_esperado'];
        }

        return view('pos.index', compact('sesion', 'productos', 'pedidosPendientes', 'clientes', 'dineroEsperado', 'fondoSugerido'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GESTIÓN DE SESIÓN
    // ══════════════════════════════════════════════════════════════════════════

    public function abrirSesion(Request $request)
    {
        if (CajaSesion::sesionAbierta()) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una sesión de caja abierta. Debes cerrarla primero.'
            ], 422);
        }

        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        $sesion = CajaSesion::create([
            'usuario_id'     => Auth::id() ?? 1,
            'estado'         => 'abierta',
            'monto_inicial'  => $request->monto_inicial,
            'fecha_apertura' => now(),
        ]);

        return response()->json([
            'success' => true,
            'sesion'  => $sesion,
            'message' => 'Caja abierta correctamente. ¡Listo para vender!'
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BÚSQUEDA DE VARIANTES (Endpoint AJAX)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Busca variantes por nombre de producto, SKU o atributos.
     * Retorna datos enriquecidos para el selector del POS.
     */
    public function buscarProductos(Request $request)
    {
        $query = $request->get('q', '');

        $variantes = ProductoVariante::with('producto')
            ->where('activo', true)
            ->whereRaw('(stock_fisico - stock_reservado) > 0')
            ->where(function ($q) use ($query) {
                $q->where('sku', 'LIKE', "%{$query}%")
                  ->orWhereHas('producto', function ($qp) use ($query) {
                      $qp->where('nombre', 'LIKE', "%{$query}%");
                  });
            })
            ->orderBy('sku')
            ->limit(30)
            ->get()
            ->map(fn($v) => [
                'id'              => $v->id,
                'sku'             => $v->sku,
                'nombre_completo' => $v->nombre_completo,
                'atributos'       => $v->atributos ?? [],
                'precio'          => (float) $v->precio,
                'stock_disponible'=> $v->stock_disponible,
                'imagen'          => $v->imagen,
            ]);

        return response()->json($variantes);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PROCESAR COBRO
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Procesa el cobro de un carrito. Ahora trabaja con variante_id.
     *
     * Integración Inventario: descuenta stock_fisico directamente (venta POS = sin reserva).
     * Integración Tesorería:  registra ingreso en caja_movimientos.
     */
    public function procesarVenta(Request $request)
    {
        $request->validate([
            'caja_sesion_id'      => 'required|exists:caja_sesiones,id',
            'carrito'             => 'required|array|min:1',
            'carrito.*.id'        => 'required|exists:producto_variantes,id',
            'carrito.*.qty'       => 'required|integer|min:1',
            'descuento'           => 'nullable|numeric|min:0',
            'metodo_pago'         => 'required|in:efectivo,transferencia,tarjeta,mixto',
            'monto_entregado'     => 'nullable|numeric|min:0',
            'cliente_id'          => 'nullable|exists:clientes,id',
            'notas'               => 'nullable|string|max:500',
            // Campos adicionales para tarjeta / transferencia / mixto
            'referencia_pago'     => 'nullable|string|max:255',
            'monto_efectivo'      => 'nullable|numeric|min:0',
            'monto_digital'       => 'nullable|numeric|min:0',
            'metodo_digital'      => 'nullable|in:tarjeta,transferencia',
            'referencia_digital'  => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $sesion = CajaSesion::findOrFail($request->caja_sesion_id);
            if ($sesion->estado !== 'abierta') {
                throw new \Exception('La sesión de caja está cerrada. No se pueden procesar ventas.');
            }

            // ── 1. Validar stock y calcular montos ────────────────────────────
            $subtotal    = 0;
            $itemsValidos = [];

            foreach ($request->carrito as $item) {
                $variante = ProductoVariante::lockForUpdate()->findOrFail($item['id']);

                if ($variante->stock_disponible < $item['qty']) {
                    throw new \Exception(
                        "Stock insuficiente para '{$variante->nombre_completo}'. " .
                        "Disponible: {$variante->stock_disponible}"
                    );
                }

                $extrasCost = 0;
                $extrasPrice = 0;
                $extrasSnap = [];
                if (!empty($item['extras'])) {
                    foreach ($item['extras'] as $ex) {
                        // Buscar el registro en base de datos para obtener el costo real del extra
                        $extraRecord = \DB::table('producto_extras')->find($ex['id'] ?? null);
                        $costoExtra = $extraRecord ? floatval($extraRecord->costo) : floatval($ex['costo'] ?? 0);
                        $precioExtra = $extraRecord ? floatval($extraRecord->precio) : floatval($ex['precio'] ?? 0);

                        // Obtener la cantidad seleccionada para el extra
                        $cantidadExtra = max(1, intval($ex['cantidad'] ?? 1));

                        $extrasCost += $costoExtra * $cantidadExtra;
                        $extrasPrice += $precioExtra * $cantidadExtra;
                        $extrasSnap[] = [
                            'id'       => $ex['id'] ?? null,
                            'nombre'   => $ex['nombre'],
                            'costo'    => $costoExtra,
                            'precio'   => $precioExtra,
                            'cantidad' => $cantidadExtra,
                        ];
                    }
                }

                $precioTotal = $variante->precio + $extrasPrice;
                // Costo acumulativo: costo base de la variante + costos de los extras (Tarea 1)
                $costoTotalUnitario = floatval($variante->costo ?? 0.00) + $extrasCost;
                $linea     = $precioTotal * $item['qty'];
                $subtotal += $linea;

                $itemsValidos[] = [
                    'variante' => $variante,
                    'cantidad' => $item['qty'],
                    'precio'   => (float) $precioTotal,
                    'costo'    => (float) $costoTotalUnitario,
                    'linea'    => $linea,
                    'extras'   => $extrasSnap,
                ];
            }

            $descuento = $request->descuento ?? 0;
            $total     = max(0, $subtotal - $descuento);
            $cambio    = null;

            // Almacenar detalles específicos del pago en las notas de la venta
            $notasVenta = $request->notas ?? '';
            $montoEntregadoVenta = null;

            if ($request->metodo_pago === 'efectivo') {
                $montoEntregadoVenta = $request->monto_entregado;
                if ($montoEntregadoVenta) {
                    $cambio = $montoEntregadoVenta - $total;
                }
            } elseif ($request->metodo_pago === 'tarjeta' || $request->metodo_pago === 'transferencia') {
                $montoEntregadoVenta = $total;
                $cambio = 0;
                if ($request->referencia_pago) {
                    $notasVenta = trim($notasVenta . " | Ref. Pago: " . $request->referencia_pago);
                }
            } elseif ($request->metodo_pago === 'mixto') {
                $montoEntregadoVenta = $request->monto_efectivo;
                $cambio = max(0, ($request->monto_efectivo + $request->monto_digital) - $total);
                $notasVenta = trim($notasVenta . " | Pago Mixto — Efectivo: L. " . number_format($request->monto_efectivo, 2) . ", " . ucfirst($request->metodo_digital) . ": L. " . number_format($request->monto_digital, 2) . " (Ref: " . $request->referencia_digital . ")");
            }

            // ── 2. Crear venta POS ────────────────────────────────────────────
            $clienteId = $request->cliente_id;
            if ($request->filled('pedido_id')) {
                $p = \App\Models\Pedido::find($request->pedido_id);
                if ($p) {
                    $clienteId = $p->cliente_id;
                }
            }

            $venta = VentaPos::create([
                'caja_sesion_id'  => $sesion->id,
                'cliente_id'      => $clienteId ?: null,
                'subtotal'        => $subtotal,
                'descuento'       => $descuento,
                'total'           => $total,
                'metodo_pago'     => $request->metodo_pago,
                'monto_entregado' => $montoEntregadoVenta,
                'cambio'          => $cambio,
                'estado'          => 'completada',
                'notas'           => $notasVenta,
            ]);

            // ── 3. Crear detalles y descontar stock ───────────────────────────
            foreach ($itemsValidos as $item) {
                // Generar nombre de la variante concatenando los extras para visualización en ticket y reportes
                $nombreSnapshot = $item['variante']->nombre_completo;
                if (!empty($item['extras'])) {
                    $partesExtras = [];
                    foreach ($item['extras'] as $ex) {
                        $qty = intval($ex['cantidad'] ?? 1);
                        if ($qty > 1) {
                            $partesExtras[] = "{$qty}x {$ex['nombre']}";
                        } else {
                            $partesExtras[] = $ex['nombre'];
                        }
                    }
                    $nombreSnapshot .= ' (' . implode(', ', $partesExtras) . ')';
                }

                VentaPosDetalle::create([
                    'venta_pos_id'   => $venta->id,
                    'variante_id'    => $item['variante']->id,
                    'nombre_snapshot'=> $nombreSnapshot,
                    'sku_snapshot'   => $item['variante']->sku,
                    'cantidad'       => $item['cantidad'],
                    'precio_unitario'=> $item['precio'],
                    'costo_unitario' => $item['costo'],
                    'descuento_linea'=> 0,
                    'subtotal'       => $item['linea'],
                    'extras'         => $item['extras'],
                ]);

                // ✅ Venta directa: descuenta solo stock_fisico (sin reserva)
                $item['variante']->venderDirecto($item['cantidad']);
            }

            // ── 4. Registrar en CajaMovimiento (con aislamiento de caja_sesion_id) ──
            if ($request->metodo_pago !== 'mixto') {
                CajaMovimiento::create([
                    'caja_sesion_id' => $sesion->id,
                    'tipo'           => 'ingreso',
                    'monto'          => $total,
                    'concepto'       => 'Venta POS #' . $venta->id . ($request->referencia_pago ? ' (Ref: ' . $request->referencia_pago . ')' : ''),
                    'referencia'     => strtoupper($request->metodo_pago),
                    'pedido_id'      => null,
                    'fecha'          => now()->toDateString(),
                ]);
            } else {
                // Registro por partes para cobro mixto
                if ($request->monto_efectivo > 0) {
                    CajaMovimiento::create([
                        'caja_sesion_id' => $sesion->id,
                        'tipo'           => 'ingreso',
                        'monto'          => $request->monto_efectivo,
                        'concepto'       => 'Venta POS #' . $venta->id . ' (Parte Efectivo)',
                        'referencia'     => 'EFECTIVO',
                        'pedido_id'      => null,
                        'fecha'          => now()->toDateString(),
                    ]);
                }
                if ($request->monto_digital > 0) {
                    CajaMovimiento::create([
                        'caja_sesion_id' => $sesion->id,
                        'tipo'           => 'ingreso',
                        'monto'          => $request->monto_digital,
                        'concepto'       => 'Venta POS #' . $venta->id . ' (Parte ' . ucfirst($request->metodo_digital) . ' Ref: ' . $request->referencia_digital . ')',
                        'referencia'     => strtoupper($request->metodo_digital),
                        'pedido_id'      => null,
                        'fecha'          => now()->toDateString(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'venta'   => $venta->load('detalles'),
                'cambio'  => $cambio,
                'message' => 'Venta procesada correctamente.',
                'ticket_url' => route('pos.ticket', $venta->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function ticketVenta($id)
    {
        ini_set('memory_limit', '512M');
        $venta = VentaPos::with(['cliente', 'detalles'])->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket_pos_80mm', compact('venta'));
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->stream('ticket_venta_' . $venta->id . '.pdf');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INTEGRACIÓN CON PEDIDOS
    // ══════════════════════════════════════════════════════════════════════════

    public function buscarPedido(Request $request)
    {
        $numero_orden = $request->get('numero_orden');
        $pedido = \App\Models\Pedido::with('cliente', 'detalles')
            ->where('numero_orden', $numero_orden)
            ->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado.'], 404);
        }

        return response()->json(['success' => true, 'pedido' => $pedido]);
    }

    public function pagarPedido(Request $request)
    {
        $request->validate([
            'caja_sesion_id'  => 'required|exists:caja_sesiones,id',
            'pedido_id'       => 'required|exists:pedidos,id',
            'monto_entregado' => 'nullable|numeric|min:0',
            'metodo_pago'     => 'required|in:efectivo,transferencia,tarjeta,mixto',
            'referencia_pago'    => 'nullable|string|max:255',
            'monto_efectivo'     => 'nullable|numeric|min:0',
            'monto_digital'      => 'nullable|numeric|min:0',
            'metodo_digital'     => 'nullable|in:tarjeta,transferencia',
            'referencia_digital' => 'nullable|string|max:255',
            'accion'             => 'required|in:abonar,liquidar,entregar_liquidar',
        ]);

        try {
            DB::beginTransaction();

            $sesion = CajaSesion::findOrFail($request->caja_sesion_id);
            if ($sesion->estado !== 'abierta') {
                throw new \Exception('La sesión de caja está cerrada.');
            }

            $pedido = \App\Models\Pedido::with('detalles.variante')->findOrFail($request->pedido_id);

            if ($pedido->saldo_pendiente <= 0) {
                throw new \Exception('Este pedido ya está pagado en su totalidad.');
            }

            $monto_a_cobrar = $pedido->saldo_pendiente;
            $cambio = null;

            if ($request->accion === 'liquidar' || $request->accion === 'entregar_liquidar') {
                $monto_pagado = $monto_a_cobrar;
                if ($request->metodo_pago === 'efectivo') {
                    $monto_entregado = $request->monto_entregado ?? $monto_a_cobrar;
                    $cambio = max(0, $monto_entregado - $monto_a_cobrar);
                } elseif ($request->metodo_pago === 'mixto') {
                    $total_mixto = ($request->monto_efectivo ?? 0) + ($request->monto_digital ?? 0);
                    $cambio = max(0, $total_mixto - $monto_a_cobrar);
                } else {
                    $cambio = 0;
                }
            } else { // abonar
                if ($request->metodo_pago === 'efectivo') {
                    $monto_entregado = $request->monto_entregado ?? 0;
                    if ($monto_entregado >= $monto_a_cobrar) {
                        $cambio = $monto_entregado - $monto_a_cobrar;
                        $monto_pagado = $monto_a_cobrar;
                    } else {
                        $cambio = 0;
                        $monto_pagado = $monto_entregado;
                    }
                } elseif ($request->metodo_pago === 'tarjeta' || $request->metodo_pago === 'transferencia') {
                    $monto_pagado = $monto_a_cobrar;
                    $cambio = 0;
                } elseif ($request->metodo_pago === 'mixto') {
                    $total_mixto = ($request->monto_efectivo ?? 0) + ($request->monto_digital ?? 0);
                    if ($total_mixto >= $monto_a_cobrar) {
                        $cambio = $total_mixto - $monto_a_cobrar;
                        $monto_pagado = $monto_a_cobrar;
                    } else {
                        $cambio = 0;
                        $monto_pagado = $total_mixto;
                    }
                } else {
                    $monto_pagado = $monto_a_cobrar;
                    $cambio = 0;
                }
            }

            if ($monto_pagado <= 0) {
                throw new \Exception('El monto a pagar debe ser mayor a 0.');
            }

            // Actualizar pedido
            $pedido->total_abonado += $monto_pagado;
            $pedido->saldo_pendiente -= $monto_pagado;
            
            // Lógica de inventario (descontar stock físico y liberar reserva) al Entregar
            if ($request->accion === 'entregar_liquidar') {
                $estadoAnterior = $pedido->estado;
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->tipo_producto === 'Inventario' && $detalle->variante) {
                        $detalle->variante->confirmarEntrega($detalle->cantidad);
                    }
                }
                $pedido->estado = 'Entregado';

                \App\Models\PedidoHistorial::create([
                    'pedido_id'       => $pedido->id,
                    'usuario_id'      => auth()->id(),
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo'    => 'Entregado',
                ]);
            }
            
            $pedido->save();

            // Registrar ingresos en CajaMovimiento (con aislamiento y desglose de cobro mixto)
            $primerMovimientoId = null;
            $conceptoBase = ($request->accion === 'abonar' ? 'Abono' : 'Liquidación') . ' Pedido ' . $pedido->numero_orden;

            if ($request->metodo_pago !== 'mixto') {
                $movimiento = CajaMovimiento::create([
                    'caja_sesion_id' => $sesion->id,
                    'tipo'           => 'ingreso',
                    'monto'          => $monto_pagado,
                    'concepto'       => $conceptoBase . ($request->referencia_pago ? ' (Ref: ' . $request->referencia_pago . ')' : ''),
                    'referencia'     => strtoupper($request->metodo_pago),
                    'pedido_id'      => $pedido->id,
                    'fecha'          => now()->toDateString(),
                ]);
                $primerMovimientoId = $movimiento->id;
            } else {
                // Distribución matemática exacta del pago mixto
                $efectivoAbonado = $request->monto_efectivo;
                $digitalAbonado = $request->monto_digital;

                if ($cambio > 0) {
                    $efectivoAbonado = max(0, $efectivoAbonado - $cambio);
                }

                if ($efectivoAbonado > 0) {
                    $movimientoEfectivo = CajaMovimiento::create([
                        'caja_sesion_id' => $sesion->id,
                        'tipo'           => 'ingreso',
                        'monto'          => $efectivoAbonado,
                        'concepto'       => $conceptoBase . ' (Parte Efectivo)',
                        'referencia'     => 'EFECTIVO',
                        'pedido_id'      => $pedido->id,
                        'fecha'          => now()->toDateString(),
                    ]);
                    $primerMovimientoId = $movimientoEfectivo->id;
                }

                if ($digitalAbonado > 0) {
                    $movimientoDigital = CajaMovimiento::create([
                        'caja_sesion_id' => $sesion->id,
                        'tipo'           => 'ingreso',
                        'monto'          => $digitalAbonado,
                        'concepto'       => $conceptoBase . ' (Parte ' . ucfirst($request->metodo_digital) . ' Ref: ' . $request->referencia_digital . ')',
                        'referencia'     => strtoupper($request->metodo_digital),
                        'pedido_id'      => $pedido->id,
                        'fecha'          => now()->toDateString(),
                    ]);
                    if (!$primerMovimientoId) {
                        $primerMovimientoId = $movimientoDigital->id;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'pedido'  => $pedido,
                'cambio'  => $cambio,
                'ticket_url' => route('pago.ticket', $primerMovimientoId),
                'message' => 'Pedido cobrado y marcado como Entregado.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function descargarTicketPago($id)
    {
        ini_set('memory_limit', '512M');
        $pago = CajaMovimiento::with('pedido.cliente')->findOrFail($id);
        
        $pedido = $pago->pedido;
        $tipo = 'Abono';
        $saldoAnterior = 0;
        $saldoActual = 0;
        
        if ($pedido) {
            $saldoActual = $pedido->saldo_pendiente;
            $saldoAnterior = $saldoActual + $pago->monto;
            if ($saldoActual <= 0) {
                $tipo = 'Liquidación';
            }
        }
        
        try {
            $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(120)->margin(0)->generate(route('pedidos.track', $pedido->numero_orden)));
        } catch (\Exception $e) {
            $qrCode = null;
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket_pago_80mm', compact('pago', 'pedido', 'tipo', 'saldoAnterior', 'saldoActual', 'qrCode'));
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->stream('recibo_pago_'.$pago->id.'.pdf');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CORTE DE CAJA
    // ══════════════════════════════════════════════════════════════════════════

    public function corteCaja()
    {
        $sesion = CajaSesion::sesionAbierta();

        if (!$sesion) {
            return redirect()->route('pos.index')->with('error', 'No hay una sesión de caja abierta.');
        }

        $totales = $this->obtenerTotalesSesion($sesion->id);

        $fondoInicial = $totales['fondo_inicial'];
        $ventasEfectivo = $totales['ventas_efectivo'];
        $ingresosBancos = $totales['ingresos_bancos'];
        $retiros = $totales['egresos'];
        $totalEsperado = $totales['total_esperado'];

        return view('pos.corte', compact(
            'sesion', 'fondoInicial', 'ventasEfectivo', 'ingresosBancos', 'retiros', 'totalEsperado'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CERRAR SESIÓN
    // ══════════════════════════════════════════════════════════════════════════

    public function cerrarSesion(Request $request)
    {
        $request->validate([
            'caja_sesion_id'       => 'required|exists:caja_sesiones,id',
            'monto_contado_fisico' => 'required|numeric|min:0',
            'notas'                => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $sesion = CajaSesion::findOrFail($request->caja_sesion_id);

            if ($sesion->estado === 'cerrada') {
                throw new \Exception('Esta sesión ya fue cerrada anteriormente.');
            }

            $totales = $this->obtenerTotalesSesion($sesion->id);
            $montoFinalEsperado = $totales['total_esperado'];
            $diferencia         = $request->monto_contado_fisico - $montoFinalEsperado;

            $sesion->update([
                'estado'               => 'cerrada',
                'fecha_cierre'         => now(),
                'monto_final_esperado' => $montoFinalEsperado,
                'monto_contado_fisico' => $request->monto_contado_fisico,
                'diferencia'           => $diferencia,
                'notas'                => $request->notas,
            ]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'diferencia' => $diferencia,
                'message'    => 'Caja cerrada correctamente.',
                'redirect'   => route('pos.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cerrarCaja(Request $request)
    {
        $request->validate([
            'caja_sesion_id'       => 'required|exists:caja_sesiones,id',
            'monto_contado_fisico' => 'required|numeric|min:0',
            'monto_a_retirar'      => 'required|numeric|min:0',
            'notas'                => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $sesion = CajaSesion::findOrFail($request->caja_sesion_id);
            if ($sesion->estado === 'cerrada') {
                throw new \Exception('Esta sesión ya fue cerrada.');
            }

            // 1. Obtener totales de la sesión con aislamiento estricto
            $totales = $this->obtenerTotalesSesion($sesion->id);
            $ventasEfectivo = $totales['ventas_efectivo'];
            $montoFinalEsperado = $totales['total_esperado'];
            $diferencia = $request->monto_contado_fisico - $montoFinalEsperado;

            // El remanente
            $montoRestante = max(0, $request->monto_contado_fisico - $request->monto_a_retirar);

            // Actualizar la sesión
            $sesion->update([
                'estado'               => 'cerrada',
                'fecha_cierre'         => now(),
                'monto_final_esperado' => $montoFinalEsperado,
                'monto_contado_fisico' => $montoRestante,
                'diferencia'           => $diferencia,
                'notas'                => trim(($request->notas ?? '') . " | Corte. Arqueo físico: L. " . number_format($request->monto_contado_fisico, 2) . " | Retiro Tesorería: L. " . number_format($request->monto_a_retirar, 2) . " | Remanente: L. " . number_format($montoRestante, 2)),
            ]);

            // 2. Registrar el movimiento en la Tesorería (Caja Fuerte / Tesorería)
            $cuentaTesoreria = CuentaFinanciera::where('nombre', 'Caja Fuerte / Tesorería')->first();
            if (!$cuentaTesoreria) {
                $cuentaTesoreria = CuentaFinanciera::where('tipo', 'efectivo')->first();
            }

            if ($cuentaTesoreria && $request->monto_a_retirar > 0) {
                MovimientoTesoreria::create([
                    'cuenta_id' => $cuentaTesoreria->id,
                    'tipo' => 'ingreso',
                    'monto' => $request->monto_a_retirar,
                    'concepto' => "Corte de caja de " . ($sesion->usuario->name ?? 'Cajero') . " - " . now()->format('d/m/Y'),
                    'referencia_modulo' => 'POS-Corte-Caja',
                    'usuario_id' => Auth::id() ?? 1,
                ]);

                $cuentaTesoreria->increment('saldo_actual', $request->monto_a_retirar);
            }

            // 3. Registrar el egreso de la caja del POS (aislado por caja_sesion_id)
            if ($request->monto_a_retirar > 0) {
                CajaMovimiento::create([
                    'caja_sesion_id' => $sesion->id,
                    'tipo' => 'egreso',
                    'monto' => $request->monto_a_retirar,
                    'concepto' => 'Retiro por Corte de Caja hacia Tesorería',
                    'referencia' => 'Efectivo',
                    'fecha' => now()->toDateString(),
                ]);
            }

            // 4. Registrar la auditoría del corte de caja
            $corte = CorteCaja::create([
                'usuario_id'       => $sesion->usuario_id ?? Auth::id() ?? 1,
                'fecha_apertura'   => $sesion->fecha_apertura,
                'fecha_cierre'     => now(),
                'fondo_inicial'    => $sesion->monto_inicial,
                'ventas_efectivo'  => $ventasEfectivo,
                'total_esperado'   => $montoFinalEsperado,
                'efectivo_real'    => $request->monto_contado_fisico,
                'diferencia'       => $diferencia,
                'retiro_tesoreria' => $request->monto_a_retirar,
                'notas'            => $request->notas,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'corte_id' => $corte->id,
                'monto_restante' => $montoRestante,
                'message' => 'Cierre / Corte de caja procesado con éxito.',
                'redirect' => route('pos.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Helper para obtener los totales de la sesión con aislamiento estricto
     */
    public function obtenerTotalesSesion($sesionId)
    {
        $sesion = CajaSesion::findOrFail($sesionId);

        // Ingresos en efectivo (ventas directas + abonos de pedidos)
        $ventasEfectivo = CajaMovimiento::where('caja_sesion_id', $sesion->id)
            ->where('tipo', 'ingreso')
            ->where('referencia', 'EFECTIVO')
            ->sum('monto');

        // Ingresos a bancos (ventas directas + abonos de pedidos por tarjeta/transferencia)
        $ingresosBancos = CajaMovimiento::where('caja_sesion_id', $sesion->id)
            ->where('tipo', 'ingreso')
            ->whereIn('referencia', ['TARJETA', 'TRANSFERENCIA'])
            ->sum('monto');

        // Egresos de efectivo (retiros de caja)
        $egresos = CajaMovimiento::where('caja_sesion_id', $sesion->id)
            ->where('tipo', 'egreso')
            ->where('referencia', 'Efectivo')
            ->sum('monto');

        $totalEsperado = $sesion->monto_inicial + $ventasEfectivo - $egresos;

        return [
            'fondo_inicial'   => (float) $sesion->monto_inicial,
            'ventas_efectivo' => (float) $ventasEfectivo,
            'ingresos_bancos' => (float) $ingresosBancos,
            'egresos'         => (float) $egresos,
            'total_esperado'  => (float) $totalEsperado,
        ];
    }

    /**
     * Endpoint JSON para retornar los totales de la sesión actual (tiempo real)
     */
    public function obtenerTotalesSesionJson(Request $request)
    {
        $request->validate([
            'caja_sesion_id' => 'required|exists:caja_sesiones,id',
        ]);

        $totales = $this->obtenerTotalesSesion($request->caja_sesion_id);
 
        return response()->json([
            'success' => true,
            'totales' => $totales
        ]);
    }

    public function registrarRetiro(Request $request)
    {
        $request->validate([
            'caja_sesion_id' => 'required|exists:caja_sesiones,id',
            'monto'          => 'required|numeric|min:0.01',
            'concepto'       => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $sesion = CajaSesion::findOrFail($request->caja_sesion_id);
            if ($sesion->estado !== 'abierta') {
                throw new \Exception('La sesión de caja está cerrada.');
            }

            // 1. Registrar movimiento de egreso de caja física (tipo: egreso, referencia: Efectivo)
            $movimiento = CajaMovimiento::create([
                'caja_sesion_id' => $sesion->id,
                'tipo'           => 'egreso',
                'monto'          => $request->monto,
                'concepto'       => 'Retiro de Efectivo: ' . $request->concepto,
                'referencia'     => 'Efectivo',
                'fecha'          => now()->toDateString(),
            ]);

            // 2. Integración Global con Tesorería: registrar egreso en el balance de efectivo central
            $cuentaEfectivo = \App\Models\CuentaFinanciera::where('tipo', 'efectivo')->first();
            if ($cuentaEfectivo) {
                \App\Models\MovimientoTesoreria::create([
                    'cuenta_id'         => $cuentaEfectivo->id,
                    'tipo'              => 'egreso',
                    'monto'             => $request->monto,
                    'concepto'          => 'Gasto Operativo POS: ' . $request->concepto,
                    'referencia_modulo' => 'Gasto Operativo POS',
                    'usuario_id'        => Auth::id() ?? 1,
                ]);

                // Decrementar saldo en la cuenta de efectivo de tesorería central
                $cuentaEfectivo->decrement('saldo_actual', $request->monto);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retiro registrado correctamente (descontado de Efectivo físico).',
                'movimiento' => $movimiento
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function obtenerExtras($productoId)
    {
        $producto = Producto::with(['extras', 'categoria.extras'])->findOrFail($productoId);
        $extrasDisponibles = $producto->extras->merge($producto->categoria ? $producto->categoria->extras : collect())->unique('id')->values();

        return response()->json($extrasDisponibles);
    }
}
