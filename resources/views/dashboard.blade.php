@extends('layouts.app')

@section('header_title', 'Tablero Principal')

@section('content')
<div class="space-y-8">
    
    <!-- Sección 1: Tarjetas de Resumen (KPIs) -->
    <!-- Fila 1: Caja y Contabilidad -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Efectivo en Caja (Hoy) -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:border-neutral-900 transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Efectivo en Caja (Hoy)</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div>
                <span class="text-3xl font-extrabold text-emerald-600 tracking-tight">L. {{ number_format($efectivoHoy, 2) }}</span>
                <p class="text-xs text-neutral-500 mt-1">Ventas y abonos cobrados en físico hoy</p>
            </div>
        </div>

        <!-- Total Adeudado -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:border-neutral-900 transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Total Adeudado</span>
                <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </span>
            </div>
            <div>
                <span class="text-3xl font-extrabold text-blue-600 tracking-tight">L. {{ number_format($cuentasCobrar, 2) }}</span>
                <p class="text-xs text-neutral-500 mt-1">Saldo pendiente de pedidos activos</p>
            </div>
        </div>

        <!-- Ingresos por Transferencia (Hoy) -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:border-neutral-900 transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Ingresos por Transferencia (Hoy)</span>
                <span class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
            </div>
            <div>
                <span class="text-3xl font-extrabold text-purple-600 tracking-tight">L. {{ number_format($transferenciaHoy, 2) }}</span>
                <p class="text-xs text-neutral-500 mt-1">Cobrado digitalmente hoy en bancos</p>
            </div>
        </div>
    </div>

    <!-- Fila 2: Operaciones -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pedidos Activos -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:border-neutral-900 transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Pedidos Activos</span>
                <span class="p-2 bg-neutral-100 text-neutral-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div>
                <span class="text-3xl font-extrabold text-neutral-900 tracking-tight">{{ $pedidosActivos }}</span>
                <p class="text-xs text-neutral-500 mt-1">Pedidos en curso o en producción</p>
            </div>
        </div>

        <!-- Total Clientes -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:border-neutral-900 transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Total Clientes</span>
                <span class="p-2 bg-neutral-100 text-neutral-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
            </div>
            <div>
                <span class="text-3xl font-extrabold text-neutral-900 tracking-tight">{{ $clientesTotales }}</span>
                <p class="text-xs text-neutral-500 mt-1">Clientes en base de datos</p>
            </div>
        </div>

        <!-- Alertas de Stock -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:border-neutral-900 transition-all">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Alertas de Stock</span>
                <span class="p-2 {{ $alertasStock > 0 ? 'bg-orange-50 text-orange-600' : 'bg-neutral-100 text-neutral-600' }} rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div>
                <span class="text-3xl font-extrabold tracking-tight {{ $alertasStock > 0 ? 'text-orange-600' : 'text-neutral-900' }}">
                    {{ $alertasStock }}
                </span>
                <p class="text-xs text-neutral-500 mt-1">Variantes con menos de 5 unidades</p>
            </div>
        </div>
    </div>

    <!-- Sección 2: Pedidos Recientes -->
    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-neutral-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-neutral-900">Últimos Pedidos</h3>
                <p class="text-xs text-neutral-500">Acceso rápido a las transacciones más recientes</p>
            </div>
            <a href="{{ route('pedidos.index') }}" class="px-4 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold rounded-xl transition-all">
                Ver Tablero Kanban
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Orden</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm text-neutral-700">
                    @forelse($pedidosRecientes as $pedido)
                    <tr class="hover:bg-neutral-50/40 transition-colors">
                        <td class="px-6 py-4 font-bold">
                            <!-- Enlace que redirige al Kanban abriendo directamente el pedido -->
                            <a href="{{ route('pedidos.index') }}?id={{ $pedido->id }}" class="text-neutral-900 hover:underline">
                                {{ $pedido->numero_orden }}
                            </a>
                        </td>
                        <td class="px-6 py-4 font-medium text-neutral-900">{{ $pedido->cliente->nombre ?? 'Sin Cliente' }}</td>
                        <td class="px-6 py-4 text-neutral-500">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            @php
                                $badgeColor = match($pedido->estado) {
                                    'Pendiente' => 'bg-amber-50 text-amber-700 border-amber-100',
                                    'Diseño' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                    'Esperando Aprobación' => 'bg-purple-50 text-purple-700 border-purple-100',
                                    'Producción' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'Entregado' => 'bg-green-50 text-green-700 border-green-100',
                                    'Liquidado' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'Cancelado' => 'bg-red-50 text-red-700 border-red-100',
                                    default => 'bg-neutral-50 text-neutral-700 border-neutral-100'
                                };
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg border {{ $badgeColor }}">
                                {{ $pedido->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-neutral-950">
                            L. {{ number_format($pedido->total_pedido, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-neutral-400 italic">
                            No se han registrado pedidos en el sistema.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
