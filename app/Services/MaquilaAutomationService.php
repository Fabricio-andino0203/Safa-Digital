<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Proveedor;

class MaquilaAutomationService
{
    /**
     * Procesa un Pedido y genera una Orden de Compra (Maquila) automática si contiene ítems subcontratados.
     */
    public static function procesarPedido(Pedido $pedido): ?Compra
    {
        // 1. Evitar generar orden duplicada si ya existe para este pedido
        if (Compra::where('pedido_id', $pedido->id)->exists()) {
            return Compra::where('pedido_id', $pedido->id)->first();
        }

        $pedido->load(['detalles.variante.producto.categoria', 'cliente']);

        $detallesSubcontratados = [];

        foreach ($pedido->detalles as $detalle) {
            if (self::esItemSubcontratado($detalle)) {
                $detallesSubcontratados[] = $detalle;
            }
        }

        if (empty($detallesSubcontratados)) {
            return null;
        }

        // 2. Obtener o crear proveedor de maquila por defecto
        $proveedor = Proveedor::where('nombre', 'LIKE', '%Maquila%')
            ->orWhere('nombre', 'LIKE', '%Subcontratado%')
            ->first();

        if (!$proveedor) {
            $proveedor = Proveedor::create([
                'nombre'   => 'Proveedor Maquila General',
                'empresa'  => 'Servicios de Maquila y Subcontratación',
                'telefono' => '2200-0000',
            ]);
        }

        // 3. Autogenerar número de orden de compra
        $lastCompra = Compra::orderBy('id', 'desc')->first();
        $nextNum = $lastCompra ? ((int) str_replace('OC-', '', $lastCompra->numero_orden)) + 1 : 1;
        $numeroOrden = 'OC-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        // 4. Crear la Orden de Compra en estado 'Solicitada' (Pendiente)
        $compra = Compra::create([
            'numero_orden' => $numeroOrden,
            'pedido_id'    => $pedido->id,
            'proveedor_id' => $proveedor->id,
            'fecha'        => now()->toDateString(),
            'total'        => 0.00,
            'estado'       => 'Solicitada',
            'notas'        => "Generada automáticamente desde Venta Maquila (Orden: {$pedido->numero_orden}). Cliente: " . ($pedido->cliente->nombre ?? 'General'),
            'extras'       => [],
        ]);

        // 5. Crear los detalles de compra asociados y calcular el total real
        $totalCompra = 0;

        foreach ($detallesSubcontratados as $det) {
            $nombreSnapshot = $det->nombre_snapshot ?? $det->nombre_libre ?? 'Item Maquila';
            if ($det->descripcion_libre) {
                $nombreSnapshot .= " — " . $det->descripcion_libre;
            }

            // Extras del pedido (medidas/notas)
            if (!empty($det->extras) && is_array($det->extras)) {
                $extrasText = [];
                foreach ($det->extras as $ex) {
                    if (is_array($ex) && !empty($ex['nombre']) && $ex['nombre'] !== 'Costo Producción') {
                        $extrasText[] = $ex['nombre'];
                    }
                }
                if (!empty($extrasText)) {
                    $nombreSnapshot .= " (" . implode(', ', $extrasText) . ")";
                }
            }

            // Obtención del costo de producción real de maquila
            $costoUnitario = floatval($det->costo_unitario ?? 0);

            if ($costoUnitario <= 0 && !empty($det->extras) && is_array($det->extras)) {
                foreach ($det->extras as $ex) {
                    if (is_array($ex) && isset($ex['costo_unitario']) && floatval($ex['costo_unitario']) > 0) {
                        $costoUnitario = floatval($ex['costo_unitario']);
                        break;
                    }
                }
            }

            if ($costoUnitario <= 0 && $det->variante && floatval($det->variante->costo ?? 0) > 0) {
                $costoUnitario = floatval($det->variante->costo);
            }

            if ($costoUnitario <= 0 && floatval($det->precio_venta ?? 0) > 0) {
                $margenDefault = floatval(get_setting('calc_margen_ganancia_default', 50));
                $costoUnitario = round(floatval($det->precio_venta) / (1 + ($margenDefault / 100)), 2);
            }

            $cantidad = max(1, intval($det->cantidad ?? 1));
            $subtotalItem = round($costoUnitario * $cantidad, 2);
            $totalCompra += $subtotalItem;

            CompraDetalle::create([
                'compra_id'            => $compra->id,
                'producto_variante_id' => $det->producto_variante_id ?: null,
                'nombre_snapshot'      => $nombreSnapshot,
                'cantidad'             => $cantidad,
                'costo_unitario'       => $costoUnitario,
                'subtotal'             => $subtotalItem,
                'costo_proveedor'      => $costoUnitario,
                'costo_extra'          => 0.00,
                'costo_total'          => $subtotalItem,
            ]);
        }

        // TAREA 3: Recalcular y actualizar el total de la Orden de Compra
        $compra->update(['total' => $totalCompra]);

        return $compra;
    }

    /**
     * Evalúa si un detalle de pedido es subcontratado (Banner, PVC, Troquelado, Sticker Impreso, etc.)
     */
    private static function esItemSubcontratado($detalle): bool
    {
        // Regla 1: Si la categoría tiene la bandera es_subcontratado activada
        $categoria = $detalle->variante?->producto?->categoria;
        if ($categoria && $categoria->es_subcontratado) {
            return true;
        }

        // Regla 2: Coincidencia por palabras clave de maquila/subcontratación
        $palabrasMaquila = ['banner', 'pvc', 'troquelado', 'impreso', 'maquila', 'subcontratado', 'lona', 'rotulado'];

        $textoABuscar = strtolower(implode(' ', array_filter([
            $categoria?->nombre ?? '',
            $detalle->variante?->producto?->nombre ?? '',
            $detalle->nombre_snapshot ?? '',
            $detalle->nombre_libre ?? '',
            $detalle->descripcion_libre ?? '',
        ])));

        foreach ($palabrasMaquila as $kw) {
            if (str_contains($textoABuscar, $kw)) {
                return true;
            }
        }

        return false;
    }
}
