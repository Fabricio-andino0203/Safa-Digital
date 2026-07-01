@extends('pdf.layout_a4')

@section('title', 'Reporte de Ajustes de Stock y Mermas')
@section('report_name', 'Ajustes de Inventario y Mermas')

@section('content')
<div class="summary-card">
    <table>
        <tr>
            <td>
                <span class="summary-label">Total Ajustes Auditados:</span><br>
                <span class="summary-value">{{ $ajustes->count() }}</span>
            </td>
            <td class="text-center">
                <span class="summary-label">Entradas de Stock (+):</span><br>
                <span class="summary-value" style="color: #10b981;">+{{ number_format($total_entradas) }} unidades</span>
            </td>
            <td class="text-right">
                <span class="summary-label">Salidas por Merma (-):</span><br>
                <span class="summary-value" style="color: #ef4444;">-{{ number_format($total_mermas) }} unidades</span>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Fecha Ajuste</th>
            <th>Producto / Variante</th>
            <th>Tipo Ajuste</th>
            <th class="text-center">Cantidad</th>
            <th>Motivo / Razón</th>
            <th>Usuario Autorizador</th>
        </tr>
    </thead>
    <tbody>
        @forelse($ajustes as $ajuste)
        <tr>
            <td>{{ $ajuste->created_at->format('d/m/Y H:i') }}</td>
            <td class="font-bold">{{ $ajuste->variante->producto->nombre ?? 'N/A' }} - {{ $ajuste->variante->nombre ?? 'Variante' }}</td>
            <td>
                @if($ajuste->cantidad > 0)
                    <span style="color: #10b981; font-weight: bold;">Entrada</span>
                @else
                    <span style="color: #ef4444; font-weight: bold;">Merma / Salida</span>
                @endif
            </td>
            <td class="text-center font-bold" style="color: {{ $ajuste->cantidad > 0 ? '#10b981' : '#ef4444' }}">
                {{ $ajuste->cantidad > 0 ? '+' : '' }}{{ $ajuste->cantidad }}
            </td>
            <td>{{ $ajuste->motivo }}</td>
            <td>{{ $ajuste->usuario->name ?? 'Sistema' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center" style="color: #777; font-style: italic; padding: 20px;">
                No se registraron ajustes de inventario o mermas en el período seleccionado.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
