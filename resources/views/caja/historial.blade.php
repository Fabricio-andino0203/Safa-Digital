@extends('layouts.app')

@section('header_title', 'Historial de Caja')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Historial y Auditoría</h2>
            <p class="text-neutral-500 text-sm mt-1">Historial completo de ventas, abonos, liquidaciones y movimientos de caja.</p>
        </div>
        
        <!-- Filtros de Tipo -->
        <div class="flex gap-2 bg-neutral-100 p-1 rounded-xl border border-neutral-200 text-xs font-semibold">
            <a href="{{ route('caja.historial') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ !request()->filled('tipo') ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-800' }}">Todos</a>
            <a href="{{ route('caja.historial', ['tipo' => 'venta']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('tipo') === 'venta' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-800' }}">Ventas POS</a>
            <a href="{{ route('caja.historial', ['tipo' => 'abono']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('tipo') === 'abono' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-800' }}">Abonos</a>
            <a href="{{ route('caja.historial', ['tipo' => 'liquidacion']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('tipo') === 'liquidacion' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-800' }}">Liquidaciones</a>
            <a href="{{ route('caja.historial', ['tipo' => 'deposito']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('tipo') === 'deposito' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-800' }}">Depósitos</a>
            <a href="{{ route('caja.historial', ['tipo' => 'retiro']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('tipo') === 'retiro' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-800' }}">Retiros</a>
        </div>
    </div>

    <!-- Tabla de Auditoría -->
    <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-neutral-500 bg-[#FAFAFA]">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Fecha / Hora</th>
                        <th class="px-6 py-4 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Folio / Concepto</th>
                        <th class="px-6 py-4 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Cliente</th>
                        <th class="px-6 py-4 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Tipo de Movimiento</th>
                        <th class="px-6 py-4 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Método</th>
                        <th class="px-6 py-4 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100 text-right">Total</th>
                        <th class="px-6 py-4 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-50 bg-white">
                    @forelse($movimientos as $m)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <!-- Fecha / Hora -->
                        <td class="px-6 py-4 text-xs font-mono text-neutral-500">
                            {{ $m->fecha->format('d/m/Y') }}
                            <span class="block text-neutral-400">{{ $m->created_at->format('h:i A') }}</span>
                        </td>
                        
                        <!-- Folio / Concepto -->
                        <td class="px-6 py-4">
                            <div class="font-medium text-neutral-900">{{ $m->concepto }}</div>
                            <span class="text-[10px] text-neutral-400 font-mono">ID Transacción: #{{ $m->id }}</span>
                        </td>
                        
                        <!-- Cliente -->
                        <td class="px-6 py-4 text-neutral-700">
                            @if($m->pedido && $m->pedido->cliente)
                                {{ $m->pedido->cliente->nombre }}
                            @elseif(preg_match('/Venta POS/i', $m->concepto))
                                <span class="text-xs text-neutral-400">Mostrador (POS)</span>
                            @else
                                <span class="text-xs text-neutral-400">—</span>
                            @endif
                        </td>
                        
                        <!-- Tipo de Movimiento -->
                        <td class="px-6 py-4">
                            @php
                                $typeLabel = 'Movimiento';
                                $badgeClass = 'bg-neutral-100 text-neutral-600 border-neutral-200';
                                
                                if (preg_match('/Venta POS/i', $m->concepto)) {
                                    $typeLabel = 'Venta POS';
                                    $badgeClass = 'bg-neutral-900 text-white border-neutral-900';
                                } elseif (preg_match('/Abono/i', $m->concepto)) {
                                    $typeLabel = 'Abono';
                                    $badgeClass = 'bg-blue-50 text-blue-700 border-blue-100';
                                } elseif (preg_match('/Liquidación/i', $m->concepto)) {
                                    $typeLabel = 'Liquidación';
                                    $badgeClass = 'bg-green-50 text-green-700 border-green-100';
                                } elseif ($m->tipo === 'egreso') {
                                    $typeLabel = 'Retiro';
                                    $badgeClass = 'bg-red-50 text-red-700 border-red-100';
                                } elseif ($m->tipo === 'ingreso') {
                                    $typeLabel = 'Depósito';
                                    $badgeClass = 'bg-yellow-50 text-yellow-800 border-yellow-100';
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        
                        <!-- Método -->
                        <td class="px-6 py-4">
                            <span class="text-xs text-neutral-600 bg-neutral-100 px-2 py-0.5 rounded">
                                {{ $m->referencia ?? 'Efectivo' }}
                            </span>
                        </td>
                        
                        <!-- Total -->
                        <td class="px-6 py-4 text-right font-bold text-sm {{ $m->tipo === 'ingreso' ? 'text-neutral-900' : 'text-red-600' }}">
                            {{ $m->tipo === 'ingreso' ? '+' : '-' }} L.{{ number_format($m->monto, 2) }}
                        </td>
                        
                        <!-- Acciones (Reimprimir) -->
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('caja.historial.reimprimir', $m->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-neutral-700 bg-neutral-100 hover:bg-neutral-900 hover:text-white rounded-lg transition-colors shadow-sm"
                               title="Reimprimir Recibo">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Ticket
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-neutral-400">
                            <svg class="w-10 h-10 mx-auto mb-2.5 text-neutral-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm font-medium">No se encontraron movimientos registrados.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        @if($movimientos->hasPages())
        <div class="px-6 py-4 border-t border-neutral-100 bg-[#FAFAFA]">
            {{ $movimientos->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
