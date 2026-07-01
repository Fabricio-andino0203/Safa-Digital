@extends('layouts.app')

@section('header_title', 'Historial de Cortes de Caja')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
        <div>
            <p class="text-sm text-neutral-500">Historial completo y auditorías de cierre de turnos de los cajeros.</p>
        </div>
        <a href="{{ route('reportes.index') }}" class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold rounded-xl transition-colors">
            Volver a Reportes
        </a>
    </div>

    <!-- Tabla Historial -->
    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Fecha Cierre</th>
                        <th class="px-6 py-4">Cajero</th>
                        <th class="px-6 py-4">Fondo Inicial</th>
                        <th class="px-6 py-4">Ventas Efectivo</th>
                        <th class="px-6 py-4">Efectivo Real</th>
                        <th class="px-6 py-4">Diferencia</th>
                        <th class="px-6 py-4">Enviado a Tesorería</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm text-neutral-700">
                    @forelse($cortes as $corte)
                    <tr class="hover:bg-neutral-50/40 transition-colors">
                        <td class="px-6 py-4 text-neutral-500 font-medium">{{ $corte->fecha_cierre->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 font-bold text-neutral-900">{{ $corte->usuario->name ?? 'Cajero' }}</td>
                        <td class="px-6 py-4 font-medium text-neutral-600">L. {{ number_format($corte->fondo_inicial, 2) }}</td>
                        <td class="px-6 py-4 font-medium text-neutral-600">L. {{ number_format($corte->ventas_efectivo, 2) }}</td>
                        <td class="px-6 py-4 font-bold text-neutral-900">L. {{ number_format($corte->efectivo_real, 2) }}</td>
                        <td class="px-6 py-4 font-bold">
                            @if($corte->diferencia == 0)
                                <span class="text-green-600">L. 0.00 (Cuadrado)</span>
                            @elseif($corte->diferencia > 0)
                                <span class="text-blue-600">+ L. {{ number_format($corte->diferencia, 2) }} (Sobrante)</span>
                            @else
                                <span class="text-red-500">L. {{ number_format($corte->diferencia, 2) }} (Faltante)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-green-600">L. {{ number_format($corte->retiro_tesoreria, 2) }}</td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('reportes.corte.ticket', $corte->id) }}" target="_blank"
                               class="text-neutral-500 hover:text-neutral-900 font-semibold text-xs transition-colors">
                                Ticket (80mm)
                            </a>
                            <a href="{{ route('reportes.corte.pdf', $corte->id) }}" target="_blank"
                               class="text-blue-600 hover:text-blue-800 font-semibold text-xs transition-colors">
                                PDF (A4)
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-neutral-400 italic">
                            No se han registrado cierres o cortes de caja en el sistema.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
