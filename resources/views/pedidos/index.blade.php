@extends('layouts.app')

@section('header_title', 'Tablero de Pedidos')

@section('content')
<div x-data="kanbanBoard()" class="h-full flex flex-col">
    <!-- Header de Controles -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Pedidos Activos</h2>
            <p class="text-neutral-500 text-sm mt-1">Arrastra las tarjetas para cambiar su estado.</p>
        </div>
        <button @click="openSlideOver = true" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nuevo Pedido
        </button>
    </div>

    <!-- Cuadrícula Kanban Estricta (CSS Grid / Flex) -->
    <div class="flex-1 flex gap-6 overflow-x-auto pb-4">
        @php
            $columnas = ['Pendiente', 'Diseño', 'Aprobación', 'Producción', 'Listo', 'Entregado'];
        @endphp

        @foreach($columnas as $columna)
        <!-- Columna Individual -->
        <div class="flex flex-col w-80 flex-shrink-0">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="text-sm font-semibold text-neutral-900">{{ $columna }}</h3>
                <span class="text-xs font-medium bg-neutral-100 text-neutral-500 px-2.5 py-0.5 rounded-full">
                    {{ isset($pedidos[$columna]) ? count($pedidos[$columna]) : 0 }}
                </span>
            </div>

            <div class="flex-1 space-y-4 rounded-xl">
                @if(isset($pedidos[$columna]))
                    @foreach($pedidos[$columna] as $pedido)
                    <!-- Tarjeta de Pedido (Estilo Notion) -->
                    <div class="bg-white border border-neutral-100 p-5 rounded-2xl shadow-sm cursor-grab hover:border-neutral-200 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-xs font-medium text-neutral-500">#{{ str_pad($pedido->id, 4, '0', STR_PAD_LEFT) }}</span>
                            <!-- Tags de estado minimalistas -->
                            <span class="w-2 h-2 rounded-full @if($columna == 'Pendiente') bg-yellow-400 @elseif($columna == 'Producción') bg-blue-500 @elseif($columna == 'Listo') bg-green-500 @else bg-neutral-300 @endif"></span>
                        </div>
                        <h4 class="text-base font-semibold text-neutral-900">{{ $pedido->cliente->nombre }}</h4>
                        <p class="text-sm text-neutral-500 mt-1 line-clamp-1">{{ $pedido->notas ?? 'Sin notas' }}</p>
                        
                        <div class="mt-4 pt-4 border-t border-neutral-50 flex items-center justify-between">
                            <div class="text-xs text-neutral-500">Saldo pendiente</div>
                            <div class="text-sm font-bold text-neutral-900">${{ number_format($pedido->saldo, 2) }}</div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Slide-over (Modal Lateral) usando Alpine.js -->
    <div x-show="openSlideOver" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
        <!-- Fondo Oscuro Desenfocado -->
        <div x-show="openSlideOver" x-transition.opacity class="fixed inset-0 bg-neutral-900/20 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <!-- Panel Deslizante -->
                    <div x-show="openSlideOver" 
                         x-transition:enter="transform transition ease-in-out duration-300" 
                         x-transition:enter-start="translate-x-full" 
                         x-transition:enter-end="translate-x-0" 
                         x-transition:leave="transform transition ease-in-out duration-300" 
                         x-transition:leave-start="translate-x-0" 
                         x-transition:leave-end="translate-x-full" 
                         class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col bg-white shadow-xl">
                            <!-- Header del Panel -->
                            <div class="px-8 py-6 border-b border-neutral-100 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-neutral-900" id="slide-over-title">Nuevo Pedido</h2>
                                <button @click="openSlideOver = false" class="text-neutral-400 hover:text-neutral-500 transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            
                            <div class="relative flex-1 px-8 py-6 overflow-y-auto">
                                <!-- Formulario Limpio y Simple -->
                                <form class="space-y-6" @submit.prevent="submitPedido">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-900">Cliente</label>
                                        <select class="mt-2 block w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors cursor-pointer">
                                            <option>Seleccionar cliente...</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-900">Producto / Detalle</label>
                                        <textarea rows="3" class="mt-2 block w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors" placeholder="Ej. 50 Camisas DTF Talla M"></textarea>
                                    </div>

                                    <!-- Cálculo Visual de Tesorería -->
                                    <div class="bg-[#FAFAFA] p-5 rounded-2xl border border-neutral-100 space-y-4">
                                        <h3 class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Resumen Financiero</h3>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-neutral-900">Precio Total ($)</label>
                                            <input type="number" x-model.number="form.total" class="mt-2 block w-full rounded-xl border border-neutral-200 px-4 py-2 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-neutral-900 flex justify-between">
                                                Adelanto ($)
                                                <span class="text-xs text-neutral-500 font-normal">Irá a Tesorería como Depósito</span>
                                            </label>
                                            <input type="number" x-model.number="form.adelanto" class="mt-2 block w-full rounded-xl border border-neutral-200 px-4 py-2 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors">
                                        </div>

                                        <!-- Cálculo Dinámico con Alpine -->
                                        <div class="pt-3 border-t border-neutral-200 flex justify-between items-center">
                                            <span class="text-sm font-medium text-neutral-900">Saldo Restante</span>
                                            <span class="text-lg font-bold text-neutral-900" x-text="'$' + Math.max(0, form.total - form.adelanto).toFixed(2)"></span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Footer del Panel -->
                            <div class="px-8 py-5 border-t border-neutral-100 bg-[#FAFAFA] flex justify-end gap-3">
                                <button @click="openSlideOver = false" class="px-5 py-2.5 text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors">Cancelar</button>
                                <button type="button" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm">
                                    Guardar Pedido
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function kanbanBoard() {
        return {
            openSlideOver: false,
            form: {
                total: 0,
                adelanto: 0
            },
            submitPedido() {
                // Se conecta con la ruta del PedidoController@store
            }
        }
    }
</script>
@endpush
