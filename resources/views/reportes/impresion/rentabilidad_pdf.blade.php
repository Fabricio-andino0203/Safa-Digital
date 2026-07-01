@extends('pdf.layout_a4')

@section('title', 'Reporte de Rentabilidad')
@section('report_name', 'Reporte de Rentabilidad')

@section('content')
<div class="summary-card">
    <table>
        <tr>
            <td>
                <span class="summary-label">Ingreso Total por Ventas:</span><br>
                <span class="summary-value">L. {{ number_format($total_ventas, 2) }}</span>
            </td>
            <td class="text-right">
                <span class="summary-label">Costo Total del Inventario:</span><br>
                <span class="summary-value" style="color: #666;">L. {{ number_format($total_costos, 2) }}</span>
            </td>
            <td class="text-right">
                <span class="summary-label">Ganancia Neta Calculada:</span><br>
                <span class="summary-value" style="color: #10b981;">L. {{ number_format($total_ganancias, 2) }}</span>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Producto / Variante</th>
            <th class="text-center" style="width: 10%">Cant.</th>
            <th class="text-right" style="width: 15%">Costo Unit. (L.)</th>
            <th class="text-right" style="width: 15%">Precio Unit. (L.)</th>
            <th class="text-right" style="width: 20%">Total Costo (L.)</th>
            <th class="text-right" style="width: 20%">Total Venta (L.)</th>
            <th class="text-right" style="width: 20%">Utilidad (L.)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
        <tr>
            <td>{{ $item['nombre'] }}</td>
            <td class="text-center">{{ $item['cantidad'] }}</td>
            <td class="text-right">L. {{ number_format($item['costo'], 2) }}</td>
            <td class="text-right font-medium">L. {{ number_format($item['precio'], 2) }}</td>
            <td class="text-right text-neutral-500">L. {{ number_format($item['subtotal_costo'], 2) }}</td>
            <td class="text-right font-medium">L. {{ number_format($item['subtotal_venta'], 2) }}</td>
            <td class="text-right font-bold text-green-600">L. {{ number_format($item['ganancia'], 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center" style="color: #777; font-style: italic; padding: 20px;">
                No se registraron transacciones para evaluar rentabilidad en el período seleccionado.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
