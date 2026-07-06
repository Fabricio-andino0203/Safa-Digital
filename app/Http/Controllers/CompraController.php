<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Proveedor;
use App\Models\ProductoVariante;
use App\Models\CajaMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor', 'detalles.variante.producto')
            ->orderBy('created_at', 'desc')
            ->get();

        $proveedores = Proveedor::orderBy('nombre')->get();

        // Obtener variantes activas con sus productos para el buscador de Alpine
        $variantes = ProductoVariante::with('producto')
            ->where('activo', true)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'nombre_completo' => $v->nombre_completo,
                    'costo' => (float) $v->costo,
                    'precio' => (float) $v->precio,
                ];
            });

        return view('compras.index', compact('compras', 'proveedores', 'variantes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'notas' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_variante_id' => 'required|exists:producto_variantes,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Generar número de orden OC-XXXXX
            $lastCompra = Compra::orderBy('id', 'desc')->first();
            $nextNum = $lastCompra ? ((int) str_replace('OC-', '', $lastCompra->numero_orden)) + 1 : 1;
            $numeroOrden = 'OC-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            $compra = Compra::create([
                'numero_orden' => $numeroOrden,
                'proveedor_id' => $request->proveedor_id,
                'fecha' => $request->fecha,
                'notas' => $request->notas,
                'total' => 0,
                'estado' => 'Solicitada',
            ]);

            foreach ($request->detalles as $det) {
                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'producto_variante_id' => $det['producto_variante_id'],
                    'cantidad' => $det['cantidad'],
                    'costo_unitario' => 0.00,
                    'subtotal' => 0.00,
                ]);
            }

            DB::commit();
            return redirect()->route('compras.index')->with('success', "Solicitud de compra {$numeroOrden} registrada con éxito.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar la solicitud de compra: ' . $e->getMessage())->withInput();
        }
    }

    public function quickStoreProveedor(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        $proveedor = Proveedor::create($request->only('nombre', 'empresa', 'telefono'));

        return response()->json([
            'success' => true,
            'proveedor' => $proveedor,
        ]);
    }

    public function valorar(Request $request, $id)
    {
        if (auth()->id() !== 1) {
            return back()->with('error', 'No tienes permiso para valorar esta orden.');
        }

        $compra = Compra::findOrFail($id);
        if ($compra->estado !== 'Solicitada') {
            return back()->with('error', 'La orden ya no está en estado Solicitada.');
        }

        $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*.id' => 'required|exists:compra_detalles,id',
            'detalles.*.costo_proveedor' => 'required|numeric|min:0',
            'detalles.*.costo_extra' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($request->detalles as $det) {
                $detalle = CompraDetalle::findOrFail($det['id']);
                $costoProveedor = floatval($det['costo_proveedor']);
                $costoExtra = floatval($det['costo_extra']);
                $costoTotal = $costoProveedor + $costoExtra;

                $subtotal = $detalle->cantidad * $costoProveedor;
                $total += $subtotal;

                $detalle->update([
                    'costo_proveedor' => $costoProveedor,
                    'costo_extra'     => $costoExtra,
                    'costo_total'     => $costoTotal,
                    'costo_unitario'  => $costoProveedor, // para compatibilidad
                    'subtotal'        => $subtotal,       // para compatibilidad
                ]);
            }

            $compra->update([
                'total' => $total,
                'estado' => 'Valorizada',
            ]);

            DB::commit();
            return redirect()->route('compras.index')->with('success', "Orden {$compra->numero_orden} valorizada con éxito por L. " . number_format($total, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al valorar la orden: ' . $e->getMessage());
        }
    }

    public function recibir($id)
    {
        $compra = Compra::with('detalles.variante')->findOrFail($id);

        if ($compra->estado !== 'Valorizada') {
            return back()->with('error', 'Esta orden debe estar en estado Valorizada para poder recibirla.');
        }

        if (auth()->id() !== 1 && !auth()->user()->tienePermiso('caja') && !auth()->user()->tienePermiso('compras')) {
            return back()->with('error', 'No tienes permiso de tesorería para liberar este pago.');
        }

        DB::beginTransaction();
        try {
            // 1. Aumentar stock de las variantes y actualizar costo interno
            foreach ($compra->detalles as $detalle) {
                $variante = $detalle->variante;
                $variante->increment('stock_fisico', $detalle->cantidad);
                $variante->update(['costo' => $detalle->costo_total]);
            }

            // 2. Registrar el Egreso Contable (Bancos)
            CajaMovimiento::create([
                'tipo' => 'egreso',
                'monto' => $compra->total,
                'concepto' => 'Pago de Orden de Compra ' . $compra->numero_orden,
                'referencia' => 'Bancos',
                'fecha' => now()->toDateString(),
            ]);

            // Descontar del saldo bancario en Tesorería
            $cuentaBanco = \App\Models\CuentaFinanciera::where('nombre', 'Banco Principal')->first();
            if (!$cuentaBanco) {
                $cuentaBanco = \App\Models\CuentaFinanciera::where('tipo', 'banco')->first();
            }

            if ($cuentaBanco) {
                \App\Models\MovimientoTesoreria::create([
                    'cuenta_id' => $cuentaBanco->id,
                    'tipo' => 'egreso',
                    'monto' => $compra->total,
                    'concepto' => 'Pago de Orden de Compra ' . $compra->numero_orden,
                    'usuario_id' => auth()->id() ?? 1,
                ]);

                $cuentaBanco->decrement('saldo_actual', $compra->total);
            }

            // 3. Cambiar estado a Pagada
            $compra->update(['estado' => 'Pagada']);

            DB::commit();
            return redirect()->route('compras.index')->with('success', "Pago liberado y orden {$compra->numero_orden} recibida con éxito.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la recepción: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);

        if ($compra->estado !== 'Solicitada') {
            abort(403, 'Acceso denegado: Solo se pueden editar órdenes en estado Solicitada.');
        }

        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'notas' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_variante_id' => 'required|exists:producto_variantes,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar datos generales
            $compra->update([
                'proveedor_id' => $request->proveedor_id,
                'fecha' => $request->fecha,
                'notas' => $request->notas,
            ]);

            // Obtener ids de las variantes enviadas en la petición
            $detallesEnviados = $request->detalles;
            $variantesEnviadasIds = collect($detallesEnviados)->pluck('producto_variante_id')->toArray();

            // Eliminar detalles que ya no están en la lista
            $compra->detalles()->whereNotIn('producto_variante_id', $variantesEnviadasIds)->delete();

            // Actualizar o crear detalles
            foreach ($detallesEnviados as $det) {
                $compra->detalles()->updateOrCreate(
                    ['producto_variante_id' => $det['producto_variante_id']],
                    [
                        'cantidad' => $det['cantidad'],
                        'costo_unitario' => 0.00,
                        'subtotal' => 0.00,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('compras.index')->with('success', "Orden de compra {$compra->numero_orden} actualizada con éxito.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la orden: ' . $e->getMessage());
        }
    }

    public function descargarPDF($id)
    {
        ini_set('memory_limit', '512M');
        $compra = Compra::with('proveedor', 'detalles.variante.producto')->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.orden_compra', compact('compra'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('orden_compra_' . $compra->numero_orden . '.pdf');
    }
}
