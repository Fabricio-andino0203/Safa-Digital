<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\Pedido;
use App\Models\Compra;
use App\Models\MovimientoTesoreria;
use App\Models\AjusteStock;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function cortesCaja()
    {
        $cortes = CorteCaja::with('usuario')->orderBy('fecha_cierre', 'desc')->get();
        return view('reportes.cortes_caja', compact('cortes'));
    }

    public function imprimirTicketCorte($id)
    {
        $corte = CorteCaja::with('usuario')->findOrFail($id);
        return view('reportes.impresion.corte_80mm', compact('corte'));
    }

    public function descargarA4Corte($id)
    {
        ini_set('memory_limit', '512M');
        $corte = CorteCaja::with('usuario')->findOrFail($id);
        
        $pdf = Pdf::loadView('reportes.impresion.corte_a4', compact('corte'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('corte_caja_' . $corte->id . '.pdf');
    }

    public function ventasPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);
        $inicio = $request->fecha_inicio . ' 00:00:00';
        $fin = $request->fecha_fin . ' 23:59:59';

        $pedidos = Pedido::with('cliente')
            ->whereBetween('created_at', [$inicio, $fin])
            ->where('estado', 'Entregado')
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $pedidos->sum('total_pedido');
        $totalAbonado = $pedidos->sum('total_abonado');

        $pdf = Pdf::loadView('reportes.impresion.ventas_pdf', [
            'pedidos' => $pedidos,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'total' => $total,
            'total_abonado' => $totalAbonado,
        ]);
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('reporte_ventas_' . $request->fecha_inicio . '_al_' . $request->fecha_fin . '.pdf');
    }

    public function topProductosPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);
        $inicio = $request->fecha_inicio . ' 00:00:00';
        $fin = $request->fecha_fin . ' 23:59:59';

        $detalles = DB::table('pedido_detalles')
            ->join('pedidos', 'pedido_detalles.pedido_id', '=', 'pedidos.id')
            ->leftJoin('producto_variantes', 'pedido_detalles.producto_variante_id', '=', 'producto_variantes.id')
            ->select(
                'pedido_detalles.producto_variante_id',
                DB::raw('COALESCE(pedido_detalles.nombre_snapshot, pedido_detalles.nombre_libre) as nombre_item'),
                DB::raw('COALESCE(pedido_detalles.sku_snapshot, "N/A") as sku_item'),
                DB::raw('SUM(pedido_detalles.cantidad) as total_vendido'),
                DB::raw('SUM(pedido_detalles.subtotal) as total_ventas')
            )
            ->whereBetween('pedidos.created_at', [$inicio, $fin])
            ->where('pedidos.estado', 'Entregado')
            ->groupBy('pedido_detalles.producto_variante_id', 'nombre_item', 'sku_item')
            ->orderByDesc('total_vendido')
            ->get();

        $pdf = Pdf::loadView('reportes.impresion.top_productos_pdf', [
            'detalles' => $detalles,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('reporte_productos_mas_vendidos_' . $request->fecha_inicio . '_al_' . $request->fecha_fin . '.pdf');
    }

    public function rentabilidadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);
        $inicio = \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay()->toDateTimeString();
        $fin = \Carbon\Carbon::parse($request->fecha_fin)->endOfDay()->toDateTimeString();

        // 1. Obtener detalles de pedidos tradicionales entregados
        $detallesPedidos = DB::table('pedido_detalles')
            ->join('pedidos', 'pedido_detalles.pedido_id', '=', 'pedidos.id')
            ->leftJoin('producto_variantes', 'pedido_detalles.producto_variante_id', '=', 'producto_variantes.id')
            ->select(
                DB::raw('COALESCE(pedido_detalles.nombre_snapshot, pedido_detalles.nombre_libre) as nombre_item'),
                'pedido_detalles.cantidad',
                'pedido_detalles.precio_venta as precio_venta',
                'producto_variantes.costo as costo_unitario'
            )
            ->whereBetween('pedidos.created_at', [$inicio, $fin])
            ->where('pedidos.estado', 'Entregado')
            ->get();

        // 2. Obtener detalles de ventas directas realizadas desde el POS
        $detallesVentasPos = DB::table('venta_pos_detalles')
            ->join('ventas_pos', 'venta_pos_detalles.venta_pos_id', '=', 'ventas_pos.id')
            ->leftJoin('producto_variantes', 'venta_pos_detalles.variante_id', '=', 'producto_variantes.id')
            ->select(
                'venta_pos_detalles.nombre_snapshot as nombre_item',
                'venta_pos_detalles.cantidad',
                'venta_pos_detalles.precio_unitario as precio_venta',
                DB::raw('COALESCE(venta_pos_detalles.costo_unitario, producto_variantes.costo, 0.00) as costo_unitario')
            )
            ->whereBetween('ventas_pos.created_at', [$inicio, $fin])
            ->where('ventas_pos.estado', 'completada')
            ->get();

        // Combinar ambas colecciones
        $detalles = $detallesPedidos->concat($detallesVentasPos);

        $totalVentas = 0;
        $totalCostos = 0;
        $totalGanancias = 0;
        $items = [];

        foreach ($detalles as $d) {
            $cant = (int) $d->cantidad;
            $precio = (float) $d->precio_venta;
            $costo = (float) ($d->costo_unitario ?? 0.00); // Si costo es null o cero, no se excluye, se asume 0.00

            $subVenta = $precio * $cant;
            $subCosto = $costo * $cant;
            $ganancia = $subVenta - $subCosto;

            $totalVentas += $subVenta;
            $totalCostos += $subCosto;
            $totalGanancias += $ganancia;

            $items[] = [
                'nombre' => $d->nombre_item,
                'cantidad' => $cant,
                'precio' => $precio,
                'costo' => $costo,
                'subtotal_venta' => $subVenta,
                'subtotal_costo' => $subCosto,
                'ganancia' => $ganancia,
            ];
        }

        $pdf = Pdf::loadView('reportes.impresion.rentabilidad_pdf', [
            'items' => $items,
            'total_ventas' => $totalVentas,
            'total_costos' => $totalCostos,
            'total_ganancias' => $totalGanancias,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('reporte_rentabilidad_' . $request->fecha_inicio . '_al_' . $request->fecha_fin . '.pdf');
    }

    public function flujoTesoreriaPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);
        $inicio = $request->fecha_inicio . ' 00:00:00';
        $fin = $request->fecha_fin . ' 23:59:59';

        $movimientos = MovimientoTesoreria::with(['cuenta', 'usuario'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalIngresos = $movimientos->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $movimientos->where('tipo', 'egreso')->sum('monto');
        $balanceNeto = $totalIngresos - $totalEgresos;

        $pdf = Pdf::loadView('reportes.impresion.flujo_tesoreria_pdf', [
            'movimientos' => $movimientos,
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'balance_neto' => $balanceNeto,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('reporte_flujo_tesoreria_' . $request->fecha_inicio . '_al_' . $request->fecha_fin . '.pdf');
    }

    public function comprasPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);
        $inicio = $request->fecha_inicio . ' 00:00:00';
        $fin = $request->fecha_fin . ' 23:59:59';

        $compras = Compra::with(['proveedor'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->where('estado', 'Pagada')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalCompras = $compras->sum('total');

        $pdf = Pdf::loadView('reportes.impresion.compras_pdf', [
            'compras' => $compras,
            'total_compras' => $totalCompras,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('reporte_compras_' . $request->fecha_inicio . '_al_' . $request->fecha_fin . '.pdf');
    }

    public function ajustesStockPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);
        $inicio = $request->fecha_inicio . ' 00:00:00';
        $fin = $request->fecha_fin . ' 23:59:59';

        $ajustes = AjusteStock::with(['variante.producto', 'usuario'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalEntradas = $ajustes->where('cantidad', '>', 0)->sum('cantidad');
        $totalMermas = abs($ajustes->where('cantidad', '<', 0)->sum('cantidad'));

        $pdf = Pdf::loadView('reportes.impresion.ajustes_stock_pdf', [
            'ajustes' => $ajustes,
            'total_entradas' => $totalEntradas,
            'total_mermas' => $totalMermas,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);
        $pdf->setPaper('letter', 'portrait');
        return $pdf->stream('reporte_ajustes_y_mermas_' . $request->fecha_inicio . '_al_' . $request->fecha_fin . '.pdf');
    }
}
