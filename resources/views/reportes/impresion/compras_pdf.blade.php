@extends('pdf.layout_a4')

@section('title', 'Reporte de Compras a Proveedores')
@section('report_name', 'Compras a Proveedores')

@section('content')
<div class="summary-card">
    <table>
        <tr>
            <td>
                <span class="summary-label">Total Órdenes Pagadas:</span><br>
                <span class="summary-value">{{ $compras->count() }}</span>
            </td>
            <td class="text-right">
                <span class="summary-label">Total Invertido en Compras (L.):</span><br>
                <span class="summary-value" style="color: #ef4444;">L. {{ number_format($total_compras, 2) }}</span>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Fecha Compra</th>
            <th>No. Orden</th>
            <th>Proveedor</th>
            <th>Estado</th>
            <th class="text-right">Monto Total (L.)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($compras as $compra)
        <tr>
            <td>{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
            <td class="font-bold">{{ $compra->numero_orden }}</td>
            <td>{{ $compra->proveedor->nombre ?? 'N/A' }}</td>
            <td>
                <span style="color: #10b981; font-weight: bold;">{{ $compra->estado }}</span>
            </td>
            <td class="text-right font-bold" style="color: #ef4444;">
                L. {{ number_format($compra->total, 2) }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center" style="color: #777; font-style: italic; padding: 20px;">
                No se registraron compras liquidadas en el período seleccionado.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
