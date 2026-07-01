@extends('pdf.layout_a4')

@section('title', 'Productos Más Vendidos')
@section('report_name', 'Productos Más Vendidos')

@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 10%">Ranking</th>
            <th style="width: 15%">SKU</th>
            <th>Producto / Variante</th>
            <th class="text-center" style="width: 15%">Unidades Vendidas</th>
            <th class="text-right" style="width: 20%">Total Ventas (L.)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($detalles as $index => $item)
        <tr>
            <td class="text-center font-bold">#{{ $index + 1 }}</td>
            <td class="font-bold">{{ $item->sku_item }}</td>
            <td>{{ $item->nombre_item }}</td>
            <td class="text-center font-bold">{{ number_format($item->total_vendido) }}</td>
            <td class="text-right font-bold">L. {{ number_format($item->total_ventas, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center" style="color: #777; font-style: italic; padding: 20px;">
                No se registraron ventas de productos en el período seleccionado.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
