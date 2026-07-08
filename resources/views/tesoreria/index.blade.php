@extends('layouts.app')

@section('header_title', 'Tesorería y Finanzas')

@section('content')
<div x-data="{
    modalMovimiento: false,
    modalTraslado: false,
    modalEditar: false,
    movimientoForm: {
        cuenta_id: '',
        tipo: 'ingreso',
        monto: '',
        concepto: ''
    },
    editForm: {
        id: '',
        monto: '',
        concepto: '',
        tipo: '',
        cuenta: ''
    },
    abrirMovimiento(tipo) {
        this.movimientoForm = {
            cuenta_id: '',
            tipo: tipo,
            monto: '',
            concepto: ''
        };
        this.modalMovimiento = true;
    },
    abrirEdicion(mov) {
        this.editForm = {
            id: mov.id,
            monto: mov.monto,
            concepto: mov.concepto,
            tipo: mov.tipo === 'ingreso' ? 'Depósito' : 'Retiro',
            cuenta: mov.cuenta.nombre
        };
        this.modalEditar = true;
    }
}" class="max-w-6xl mx-auto space-y-6">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Encabezado y Acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-neutral-500">Monitorea los balances de cuentas y el flujo de caja central de la empresa.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="abrirMovimiento('ingreso')" class="px-4 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white text-sm font-bold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Depósito
            </button>
            <button @click="abrirMovimiento('egreso')" class="px-4 py-2.5 bg-white border border-neutral-200 hover:bg-neutral-50 text-neutral-700 text-sm font-bold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                Nuevo Retiro
            </button>
            <button @click="modalTraslado = true" class="px-4 py-2.5 bg-white border border-neutral-200 hover:bg-neutral-50 text-neutral-700 text-sm font-bold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Traslado de Fondos
            </button>
        </div>
    </div>

    <!-- Alertas de Flujo Pendiente -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Cuentas por Cobrar -->
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl flex items-center justify-between text-blue-800">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 block">Cuentas por Cobrar (Clientes)</span>
                    <span class="text-sm font-semibold text-blue-900 block mt-0.5">Saldo pendiente de liquidación por clientes activos</span>
                </div>
            </div>
            <span class="text-lg font-black text-blue-900">L. {{ number_format($deudasPorCobrar, 2) }}</span>
        </div>

        <!-- Cuentas por Pagar -->
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl flex items-center justify-between text-red-800">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-red-600 block">Cuentas por Pagar (Proveedores)</span>
                    <span class="text-sm font-semibold text-red-900 block mt-0.5">Órdenes valorizadas pendientes de liberación de pago</span>
                </div>
            </div>
            <span class="text-lg font-black text-red-900">L. {{ number_format($deudasPorPagar, 2) }}</span>
        </div>
    </div>

    <!-- Panel de Tarjetas / Dashboard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Capital Neto -->
        <div class="bg-white border border-neutral-200 p-6 rounded-2xl shadow-sm space-y-2">
            <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Capital Neto</span>
            <span class="text-2xl font-black text-neutral-955 block">L. {{ number_format($capitalNeto, 2) }}</span>
            <p class="text-xs text-neutral-400">Balance consolidado global</p>
        </div>

        <!-- Bancos -->
        <div class="bg-white border border-neutral-200 p-6 rounded-2xl shadow-sm space-y-2">
            <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Total en Bancos</span>
            <span class="text-2xl font-black text-neutral-955 block">L. {{ number_format($totalBancos, 2) }}</span>
            <p class="text-xs text-neutral-400">Saldo en cuentas bancarias</p>
        </div>

        <!-- Efectivo -->
        <div class="bg-white border border-neutral-200 p-6 rounded-2xl shadow-sm space-y-2">
            <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Total en Efectivo</span>
            <span class="text-2xl font-black text-neutral-955 block">L. {{ number_format($totalEfectivo, 2) }}</span>
            <p class="text-xs text-neutral-400">Tesorería y caja fuerte central</p>
        </div>

        <!-- Ganancias del Mes -->
        <div class="bg-white border border-neutral-200 p-6 rounded-2xl shadow-sm space-y-2">
            <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Ganancias del Mes</span>
            <span class="text-2xl font-black block {{ $gananciasMes >= 0 ? 'text-green-600' : 'text-red-600' }}">
                L. {{ number_format($gananciasMes, 2) }}
            </span>
            <p class="text-xs text-neutral-400">Flujo neto del mes en curso</p>
        </div>
    </div>

    <!-- Cuentas Financieras -->
    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50/50">
            <h3 class="text-sm font-bold text-neutral-800">Cuentas Financieras</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-neutral-100">
            @foreach($cuentas as $cuenta)
            <div class="p-6 flex items-center justify-between">
                <div>
                    <span class="text-sm font-bold text-neutral-900">{{ $cuenta->nombre }}</span>
                    <span class="block text-xs text-neutral-400 mt-0.5 capitalize">{{ $cuenta->tipo === 'banco' ? '🏦 Cuenta Bancaria' : '💵 Efectivo' }}</span>
                </div>
                <div class="text-right">
                    <span class="text-lg font-black text-neutral-900">L. {{ number_format($cuenta->saldo_actual, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Historial de Movimientos -->
    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-100 bg-neutral-50/50">
            <h3 class="text-sm font-bold text-neutral-800">Historial de Movimientos Financieros</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Cuenta</th>
                        <th class="px-6 py-4">Tipo</th>
                        <th class="px-6 py-4">Monto</th>
                        <th class="px-6 py-4">Concepto</th>
                        <th class="px-6 py-4">Usuario</th>
                        @if(auth()->user()->rol == 'admin')
                        <th class="px-6 py-4">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm text-neutral-700">
                    @forelse($movimientos as $mov)
                    <tr class="hover:bg-neutral-50/40 transition-colors">
                        <td class="px-6 py-4 text-neutral-500">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 font-semibold text-neutral-900">{{ $mov->cuenta->nombre }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-xs font-bold rounded-md
                                {{ $mov->tipo === 'ingreso' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}
                            ">
                                {{ $mov->tipo === 'ingreso' ? 'Depósito' : 'Retiro' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold {{ $mov->tipo === 'ingreso' ? 'text-green-600' : 'text-red-500' }}">
                            {{ $mov->tipo === 'ingreso' ? '+' : '−' }} L. {{ number_format($mov->monto, 2) }}
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate" title="{{ $mov->concepto }}">
                            {{ $mov->concepto }}
                            @if($mov->referencia_modulo === 'Gasto Operativo POS')
                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-neutral-100 text-neutral-600 border border-neutral-200">
                                    POS (Solo Lectura)
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-neutral-500">{{ $mov->usuario->name ?? 'Usuario' }}</td>
                        @if(auth()->user()->rol == 'admin')
                        <td class="px-6 py-4 space-x-2 whitespace-nowrap">
                            <button @click="abrirEdicion({{ json_encode($mov) }})" 
                                    class="text-blue-600 hover:text-blue-900 font-semibold text-xs inline-flex items-center gap-1"
                                    title="Editar concepto o monto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </button>
                            <form action="{{ route('tesoreria.revertir', $mov->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('¿Estás seguro de revertir este movimiento? Esto eliminará el registro y ajustará los fondos en la caja activa o banco correspondientes de forma irreversible.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs inline-flex items-center gap-1"
                                        title="Revertir y eliminar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Revertir
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->rol == 'admin' ? 7 : 6 }}" class="px-6 py-12 text-center text-neutral-400 italic">
                            No se han registrado movimientos financieros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Registrar Movimiento (Depósito / Retiro) -->
    <div x-show="modalMovimiento" class="relative z-50" x-cloak>
        <div x-show="modalMovimiento" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalMovimiento"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="modalMovimiento = false"
                     class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                        <h3 class="text-lg font-bold text-neutral-900" x-text="movimientoForm.tipo === 'ingreso' ? 'Nuevo Depósito' : 'Nuevo Retiro'"></h3>
                        <button @click="modalMovimiento = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('tesoreria.movimiento') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="tipo" :value="movimientoForm.tipo">

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Cuenta Financiera *</label>
                            <select name="cuenta_id" x-model="movimientoForm.cuenta_id" required
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer">
                                <option value="">Seleccionar cuenta...</option>
                                @foreach($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }} ({{ ucfirst($cuenta->tipo) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Monto (L.) *</label>
                            <input type="number" step="0.01" name="monto" x-model="movimientoForm.monto" min="0.01" required placeholder="0.00"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Concepto / Descripción *</label>
                            <input type="text" name="concepto" x-model="movimientoForm.concepto" required placeholder="Ej. Pago de servicios, Transferencia interna..."
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalMovimiento = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Guardar Transacción
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Traslado de Fondos -->
    <div x-show="modalTraslado" x-cloak class="relative z-50" x-cloak>
        <div x-show="modalTraslado" x-transition.opacity class="fixed inset-0 bg-neutral-900/40 backdrop-blur-sm"></div>
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalTraslado"
                     @click.away="modalTraslado = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="bg-white rounded-3xl shadow-2xl w-full max-w-sm border border-neutral-100 overflow-hidden p-7 space-y-4">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                        <h3 class="text-base font-bold text-neutral-900">Traslado de Fondos</h3>
                        <button @click="modalTraslado = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('tesoreria.traslado') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Cuenta Origen (Egreso) *</label>
                            <select name="cuenta_origen_id" required
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer">
                                <option value="">Seleccionar origen...</option>
                                @foreach($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }} (Saldo: L. {{ number_format($cuenta->saldo_actual, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Cuenta Destino (Ingreso) *</label>
                            <select name="cuenta_destino_id" required
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer">
                                <option value="">Seleccionar destino...</option>
                                @foreach($cuentas as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->nombre }} (Saldo: L. {{ number_format($cuenta->saldo_actual, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Monto a Trasladar (L.) *</label>
                            <input type="number" step="0.01" name="monto" min="0.01" required placeholder="0.00"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalTraslado = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Confirmar Traslado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Movimiento -->
    <div x-show="modalEditar" class="relative z-50" x-cloak>
        <div x-show="modalEditar" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalEditar"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="modalEditar = false"
                     class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                        <h3 class="text-lg font-bold text-neutral-900">Editar Movimiento Financiero</h3>
                        <button @click="modalEditar = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/tesoreria/movimientos/' + editForm.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-semibold text-neutral-500 mb-1">Cuenta</label>
                            <span class="block text-sm font-bold text-neutral-800 bg-neutral-50 px-4 py-2.5 rounded-xl border border-neutral-200" x-text="editForm.cuenta"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-500 mb-1">Tipo de Movimiento</label>
                            <span class="block text-sm font-bold text-neutral-800 bg-neutral-50 px-4 py-2.5 rounded-xl border border-neutral-200" x-text="editForm.tipo"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Monto (L.) *</label>
                            <input type="number" step="0.01" name="monto" x-model="editForm.monto" min="0.01" required placeholder="0.00"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Concepto / Descripción *</label>
                            <input type="text" name="concepto" x-model="editForm.concepto" required placeholder="Concepto..."
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalEditar = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
