@extends('pdf.layout_a4')

@section('title', 'Reporte de Flujo de Tesorería')
@section('report_name', 'Flujo de Tesorería')

@section('content')
<div class="summary-card">
    <table>
        <tr>
            <td>
                <span class="summary-label">Total Depósitos (+) :</span><br>
                <span class="summary-value" style="color: #10b981;">L. {{ number_format($total_ingresos, 2) }}</span>
            </td>
            <td class="text-center">
                <span class="summary-label">Total Retiros (-) :</span><br>
                <span class="summary-value" style="color: #ef4444;">L. {{ number_format($total_egresos, 2) }}</span>
            </td>
            <td class="text-right">
                <span class="summary-label">Balance Neto del Período:</span><br>
                <span class="summary-value" style="color: {{ $balance_neto >= 0 ? '#10b981' : '#ef4444' }};">
                    L. {{ number_format($balance_neto, 2) }}
                </span>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Cuenta Financiera</th>
            <th>Tipo Movimiento</th>
            <th>Concepto / Referencia</th>
            <th>Operador</th>
            <th class="text-right">Monto (L.)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movimientos as $mov)
        <tr>
            <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
            <td class="font-bold">{{ $mov->cuenta->nombre ?? 'Cuenta Principal' }}</td>
            <td>
                @if($mov->tipo === 'ingreso')
                    <span style="color: #10b981; font-weight: bold;">Depósito</span>
                @else
                    <span style="color: #ef4444; font-weight: bold;">Retiro</span>
                @endif
            </td>
            <td>
                <span class="font-bold">{{ $mov->referencia_modulo ?? 'Tesorería' }}</span> - {{ $mov->concepto }}
            </td>
            <td>{{ $mov->usuario->name ?? 'Sistema' }}</td>
            <td class="text-right font-bold" style="color: {{ $mov->tipo === 'ingreso' ? '#10b981' : '#ef4444' }}">
                {{ $mov->tipo === 'ingreso' ? '+' : '-' }} L. {{ number_format($mov->monto, 2) }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center" style="color: #777; font-style: italic; padding: 20px;">
                No se registraron movimientos de tesorería en el período seleccionado.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
