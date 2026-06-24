<?php

namespace App\Http\Controllers;

use App\Models\CajaSesion;
use App\Models\CajaMovimiento;
use App\Models\Producto;
use App\Models\VentaPos;
use App\Models\VentaPosDetalle;
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
     * Si no hay sesión abierta, muestra el modal de apertura bloqueando el POS.
     */
    public function index()
    {
        $sesion = CajaSesion::sesionAbierta();

        // Carga productos activos con stock > 0 para el grid del POS
        $productos = Producto::where('tipo', 'producto')
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio', 'stock', 'sku']);

        return view('pos.index', compact('sesion', 'productos'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GESTIÓN DE SESIÓN
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Abre un nuevo turno de caja.
     * Solo puede haber UNA sesión abierta a la vez.
     */
    public function abrirSesion(Request $request)
    {
        // Validación: no abrir si ya hay una sesión activa
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
            'usuario_id'     => Auth::id() ?? 1, // Fallback mientras no hay auth completo
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
    // BÚSQUEDA DE PRODUCTOS (Endpoint AJAX para Alpine.js)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Busca productos por nombre o SKU.
     * Retorna JSON — llamado reactivamente desde Alpine sin recargar la página.
     */
    public function buscarProductos(Request $request)
    {
        $query = $request->get('q', '');

        $productos = Producto::where('tipo', 'producto')
            ->where('stock', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->orderBy('nombre')
            ->limit(30)
            ->get(['id', 'nombre', 'precio', 'stock', 'sku']);

        return response()->json($productos);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PROCESAR COBRO (La operación más crítica del sistema)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Procesa el cobro de un carrito completo.
     *
     * Integración con Inventario: descuenta stock de cada producto vendido.
     * Integración con Tesorería:  registra el ingreso en caja_movimientos.
     *
     * Todo ocurre dentro de una transacción DB — si algo falla, se revierte todo.
     */
    public function procesarVenta(Request $request)
    {
        $request->validate([
            'caja_sesion_id'  => 'required|exists:caja_sesiones,id',
            'carrito'         => 'required|array|min:1',
            'carrito.*.id'    => 'required|exists:productos,id',
            'carrito.*.qty'   => 'required|integer|min:1',
            'descuento'       => 'nullable|numeric|min:0',
            'metodo_pago'     => 'required|in:efectivo,transferencia,tarjeta,mixto',
            'monto_entregado' => 'nullable|numeric|min:0',
            'cliente_id'      => 'nullable|exists:clientes,id',
            'notas'           => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // ── 1. Verificar sesión abierta ───────────────────────────────────
            $sesion = CajaSesion::findOrFail($request->caja_sesion_id);
            if ($sesion->estado !== 'abierta') {
                throw new \Exception('La sesión de caja está cerrada. No se pueden procesar ventas.');
            }

            // ── 2. Calcular montos ────────────────────────────────────────────
            $subtotal  = 0;
            $itemsValidos = [];

            foreach ($request->carrito as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item['id']);

                // Verificar stock suficiente
                if ($producto->stock < $item['qty']) {
                    throw new \Exception(
                        "Stock insuficiente para '{$producto->nombre}'. Disponible: {$producto->stock}"
                    );
                }

                $precioUnitario = $producto->precio;
                $linea = $precioUnitario * $item['qty'];
                $subtotal += $linea;

                $itemsValidos[] = [
                    'producto'       => $producto,
                    'cantidad'       => $item['qty'],
                    'precio_unitario'=> $precioUnitario,
                    'subtotal'       => $linea,
                ];
            }

            $descuento = $request->descuento ?? 0;
            $total     = max(0, $subtotal - $descuento);
            $cambio    = null;

            if ($request->metodo_pago === 'efectivo' && $request->monto_entregado) {
                $cambio = $request->monto_entregado - $total;
            }

            // ── 3. Crear el ticket de venta ───────────────────────────────────
            $venta = VentaPos::create([
                'caja_sesion_id'  => $sesion->id,
                'cliente_id'      => $request->cliente_id,
                'subtotal'        => $subtotal,
                'descuento'       => $descuento,
                'total'           => $total,
                'metodo_pago'     => $request->metodo_pago,
                'monto_entregado' => $request->monto_entregado,
                'cambio'          => $cambio,
                'estado'          => 'completada',
                'notas'           => $request->notas,
            ]);

            // ── 4. Crear detalles y descontar stock (INTEGRACIÓN INVENTARIO) ──
            foreach ($itemsValidos as $item) {
                VentaPosDetalle::create([
                    'venta_pos_id'    => $venta->id,
                    'producto_id'     => $item['producto']->id,
                    'nombre_producto' => $item['producto']->nombre, // snapshot histórico
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento_linea' => 0,
                    'subtotal'        => $item['subtotal'],
                ]);

                // ✅ Descuento de stock en tiempo real
                $item['producto']->decrement('stock', $item['cantidad']);
            }

            // ── 5. Registrar en Tesorería (INTEGRACIÓN CAJA_MOVIMIENTOS) ──────
            // Regla: el ingreso va a la cuenta que corresponde al método de pago
            CajaMovimiento::create([
                'tipo'      => 'ingreso',      // → Depósito (según terminología UI)
                'monto'     => $total,
                'concepto'  => 'Venta POS #' . $venta->id,
                'referencia'=> strtoupper($request->metodo_pago),
                'pedido_id' => null,
                'fecha'     => now()->toDateString(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'venta'   => $venta->load('detalles'),
                'cambio'  => $cambio,
                'message' => 'Venta procesada correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CORTE DE CAJA (Vista resumen antes de cerrar)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Muestra la pantalla de Corte de Caja con el resumen del turno.
     */
    public function corteCaja()
    {
        $sesion = CajaSesion::sesionAbierta();

        if (!$sesion) {
            return redirect()->route('pos.index')
                ->with('error', 'No hay una sesión de caja abierta.');
        }

        $ventas          = $sesion->ventas()->where('estado', 'completada')->get();
        $totalVentas     = $ventas->sum('total');
        $ventasPorMetodo = $sesion->ventasPorMetodo();

        // Retiros (egresos) registrados durante este turno (por fecha)
        $retiros = CajaMovimiento::where('tipo', 'egreso')
            ->whereDate('fecha', $sesion->fecha_apertura->toDateString())
            ->sum('monto');

        $totalEsperado = $sesion->monto_inicial + $totalVentas - $retiros;

        return view('pos.corte', compact(
            'sesion',
            'ventas',
            'totalVentas',
            'ventasPorMetodo',
            'retiros',
            'totalEsperado'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CERRAR SESIÓN (Cierre oficial del turno)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Cierra la sesión de caja.
     * Calcula la diferencia entre lo esperado y el conteo físico.
     * NO registra el total en caja_movimientos aquí — ya se registra
     * en procesarVenta() por cada transacción individual.
     */
    public function cerrarSesion(Request $request)
    {
        $request->validate([
            'caja_sesion_id'      => 'required|exists:caja_sesiones,id',
            'monto_contado_fisico'=> 'required|numeric|min:0',
            'notas'               => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $sesion = CajaSesion::findOrFail($request->caja_sesion_id);

            if ($sesion->estado === 'cerrada') {
                throw new \Exception('Esta sesión ya fue cerrada anteriormente.');
            }

            $totalVentas   = $sesion->totalVentas();
            $retiros       = CajaMovimiento::where('tipo', 'egreso')
                ->whereDate('fecha', $sesion->fecha_apertura->toDateString())
                ->sum('monto');

            $montoFinalEsperado = $sesion->monto_inicial + $totalVentas - $retiros;
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
