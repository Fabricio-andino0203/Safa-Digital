@extends('pdf.layout_a4')

@section('title', 'Reporte de Ventas Generales')
@section('report_name', 'Reporte de Ventas Generales')

@section('content')
<div class="summary-card">
    <table>
        <tr>
            <td>
                <span class="summary-label">Total Pedidos Entregados:</span><br>
                <span class="summary-value">{{ $pedidos->count() }}</span>
            </td>
            <td class="text-right">
                <span class="summary-label">Total Facturado (L.):</span><br>
                <span class="summary-value">L. {{ number_format($total, 2) }}</span>
            </td>
            <td class="text-right">
                <span class="summary-label">Total Abonado (L.):</span><br>
                <span class="summary-value" style="color: #10b981;">L. {{ number_format($total_abonado, 2) }}</span>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>No. Orden</th>
            <th>Cliente</th>
            <th>Prioridad</th>
            <th class="text-right">Abonado (L.)</th>
            <th class="text-right">Total Pedido (L.)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pedidos as $pedido)
        <tr>
            <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
            <td class="font-bold">{{ $pedido->numero_orden }}</td>
            <td>{{ $pedido->cliente->nombre ?? 'Sin Cliente' }}</td>
            <td>{{ $pedido->prioridad }}</td>
            <td class="text-right">L. {{ number_format($pedido->total_abonado, 2) }}</td>
            <td class="text-right font-bold">L. {{ number_format($pedido->total_pedido, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center" style="color: #777; font-style: italic; padding: 20px;">
                No se registraron ventas en el período seleccionado.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
