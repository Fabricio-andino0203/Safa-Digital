@extends('layouts.app')

@section('header_title', 'Tablero de Pedidos')

@section('content')
<div x-data="kanbanBoard()" @paste.window="handlePaste($event)" class="h-full flex flex-col">
    <!-- Header de Controles -->
    <div class="flex justify-between items-center mb-8 flex-shrink-0">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Pedidos Activos</h2>
            <div class="flex items-center gap-2 mt-3">
                <button @click="filtroActual = 'todos'" :class="filtroActual === 'todos' ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Todos</button>
                <button @click="filtroActual = 'urgentes'" :class="filtroActual === 'urgentes' ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">🔥 Urgentes</button>
                <button @click="filtroActual = 'hoy'" :class="filtroActual === 'hoy' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5">⏱️ Para Hoy</button>
            </div>
        </div>
        <button @click="openModal()" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 active:scale-95 transition-all shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nuevo Pedido
        </button>
    </div>

    <!-- Cuadrícula Kanban Estricta -->
    <div class="flex-1 flex gap-6 overflow-x-auto pb-4">
        @php
            $columnas = ['Pendiente', 'Diseño', 'Esperando Aprobación', 'Producción', 'Pausado', 'Listo para Entrega', 'Entregado', 'Cancelado'];
        @endphp

        @foreach($columnas as $columna)
        <div class="flex flex-col w-80 flex-shrink-0 bg-neutral-50/50 rounded-2xl p-2 border border-neutral-100/50">
            <div class="flex items-center justify-between mb-4 px-2 pt-2">
                <h3 class="text-sm font-bold text-neutral-800">{{ $columna }}</h3>
                <span class="text-xs font-bold bg-white border border-neutral-200 text-neutral-600 px-2.5 py-0.5 rounded-full shadow-sm">
                    {{ isset($pedidos[$columna]) ? count($pedidos[$columna]) : 0 }}
                </span>
            </div>

            <div class="kanban-col flex-1 space-y-3 overflow-y-auto px-1 pb-2" data-estado="{{ $columna }}">
                @if(isset($pedidos[$columna]))
                    @foreach($pedidos[$columna] as $pedido)
                    <!-- Tarjeta de Pedido (Estilo Linear) -->
                    <div x-show="filtroActual === 'todos' || 
                               (filtroActual === 'urgentes' && ('{{ $pedido->prioridad }}' === 'Urgente' || '{{ $pedido->prioridad }}' === 'Alta Prioridad')) || 
                               (filtroActual === 'hoy' && '{{ $pedido->fecha_estimada_entrega ? $pedido->fecha_estimada_entrega->format('Y-m-d') : '' }}' === new Date().toLocaleDateString('en-CA'))"
                         data-id="{{ $pedido->id }}"
                         @click="abrirDetalles({{ $pedido->id }})"
                         class="bg-white border border-neutral-200/80 p-4 rounded-xl shadow-[0_2px_8px_-4px_rgba(0,0,0,0.05)] cursor-pointer hover:border-neutral-300 hover:shadow-md transition-all group">
                        
                        <!-- Top: Orden y Prioridad -->
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-xs font-bold text-neutral-900 bg-neutral-100 px-2 py-1 rounded-md">{{ $pedido->numero_orden }}</span>
                            @if($pedido->prioridad === 'Urgente')
                                <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-md uppercase tracking-wider">Urgente</span>
                            @elseif($pedido->prioridad === 'Alta Prioridad')
                                <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-1 rounded-md uppercase tracking-wider">Alta</span>
                            @endif
                        </div>

                        <!-- Cliente y Resumen -->
                        <h4 class="text-sm font-bold text-neutral-900 line-clamp-1">{{ $pedido->cliente->nombre }}</h4>
                        
                        <div class="mt-2 space-y-1">
                            @foreach($pedido->detalles->take(2) as $detalle)
                                <p class="text-xs text-neutral-500 line-clamp-1 flex items-center gap-1.5">
                                    <span class="w-1 h-1 rounded-full bg-neutral-300"></span>
                                    {{ $detalle->cantidad }}x {{ $detalle->nombre_snapshot ?? $detalle->nombre_libre }}
                                </p>
                            @endforeach
                            @if($pedido->detalles->count() > 2)
                                <p class="text-[10px] text-neutral-400 font-medium pl-2.5">+{{ $pedido->detalles->count() - 2 }} ítem(s) más</p>
                            @endif
                        </div>

                        @if($pedido->fecha_estimada_entrega || $pedido->hora_estimada_entrega)
                            <div class="mt-3 flex items-center gap-1.5 text-xs font-medium text-neutral-500">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $pedido->fecha_estimada_entrega ? $pedido->fecha_estimada_entrega->format('d M, Y') : 'Sin fecha' }}
                                @if($pedido->hora_estimada_entrega)
                                    &bull; {{ \Carbon\Carbon::parse($pedido->hora_estimada_entrega)->format('h:i A') }}
                                @endif
                            </div>
                        @endif
                        
                        <!-- Bottom: Finanzas e Indicador de Pago -->
                        <div class="mt-4 pt-3 border-t border-neutral-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <!-- Semáforo de pago -->
                                @if($pedido->estado_pago === 'Pagado')
                                    <div class="flex items-center gap-1.5" title="Pagado">
                                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]"></span>
                                        <span class="text-[11px] font-bold text-green-700">Pagado</span>
                                    </div>
                                @elseif($pedido->estado_pago === 'Parcial')
                                    <div class="flex items-center gap-1.5" title="Pago Parcial">
                                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.4)]"></span>
                                        <span class="text-[11px] font-bold text-yellow-700">Parcial</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5" title="Pendiente de Pago">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]"></span>
                                        <span class="text-[11px] font-bold text-red-700">Pendiente</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="text-right">
                                <div class="text-[10px] text-neutral-400 font-medium uppercase tracking-wider">Total</div>
                                <div class="text-sm font-bold text-neutral-900"> L.{{ number_format($pedido->total_pedido, 2) }}</div>
                            </div>
                        </div>

                        <!-- Acciones Rapidas -->
                        <div class="mt-3 pt-3 border-t border-neutral-100 flex items-center gap-2">
                            <button type="button" @click.stop="abrirDetalles({{ $pedido->id }})" class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-md transition-colors" title="Ver Detalles">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            <a href="{{ route('pedidos.ticket', $pedido->id) }}" @click.stop target="_blank" class="p-1.5 text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100 rounded-md transition-colors" title="Ticket PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </a>
                            <a href="{{ route('pedidos.a4', $pedido->id) }}" @click.stop target="_blank" class="p-1.5 text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100 rounded-md transition-colors" title="A4 PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </a>
                            
                            @if($pedido->saldo_pendiente > 0)
                                <a href="/pos?orden={{ $pedido->numero_orden }}" @click.stop class="ml-auto text-xs px-3 py-1.5 bg-neutral-900 text-white rounded-lg font-bold hover:bg-neutral-800 transition-colors flex items-center justify-center">
                                    Registrar Abono
                                </a>
                            @endif
                        </div>

                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Modal: Nuevo Pedido -->
    <div x-show="openSlideOver" class="fixed inset-0 z-50 overflow-hidden" x-cloak>
        <div x-show="openSlideOver" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 transition-opacity"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
            <div x-show="openSlideOver" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">
                            <!-- Header -->
                            <div class="px-8 py-6 border-b border-neutral-100 flex items-center justify-between bg-[#FAFAFA]">
                                <h2 class="text-xl font-bold text-neutral-900">Nuevo Pedido</h2>
                                <button @click="openSlideOver = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-200 rounded-xl transition-all">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            
                            <!-- Body -->
                            <div class="relative flex-1 px-8 py-6 overflow-y-auto">
                                <form class="space-y-8" @submit.prevent="submitPedido">
                                    
                                    <!-- Datos Principales -->
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="relative" x-data="{ openMenu: false }">
                                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Cliente *</label>
                                            
                                            <!-- Cliente Seleccionado -->
                                            <div x-show="form.cliente_id" class="flex items-center justify-between w-full rounded-xl border border-green-500 bg-green-50 px-4 py-3 text-sm transition-colors">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-green-800" x-text="clienteSeleccionadoObj?.nombre"></span>
                                                    <span class="text-xs text-green-600" x-text="clienteSeleccionadoObj?.telefono || 'Sin teléfono'"></span>
                                                </div>
                                                <button type="button" @click="quitarCliente()" class="text-green-700 hover:text-green-900 font-bold p-1 bg-green-200 rounded-md">✕</button>
                                            </div>

                                            <!-- Buscador de Clientes -->
                                            <div x-show="!form.cliente_id && !creandoCliente" class="flex gap-2">
                                                <div class="relative flex-1">
                                                    <input type="text" x-model="buscarClienteTerm" @input="openMenu = true" @focus="openMenu = true" @click.away="openMenu = false"
                                                           placeholder="Buscar cliente por nombre o teléfono..."
                                                           class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors">
                                                    
                                                    <div x-show="openMenu && buscarClienteTerm.length > 0" x-cloak class="absolute left-0 right-0 z-50 mt-1 bg-white border border-neutral-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                                                        <template x-for="c in clientesFiltrados" :key="c.id">
                                                            <button type="button" @click="seleccionarCliente(c); openMenu = false" class="w-full text-left px-4 py-3 hover:bg-neutral-50 border-b border-neutral-100 last:border-0 transition-colors">
                                                                <div class="font-bold text-neutral-900" x-text="c.nombre"></div>
                                                                <div class="text-xs text-neutral-500" x-text="c.telefono || c.email || 'Sin datos de contacto'"></div>
                                                            </button>
                                                        </template>
                                                        <div x-show="clientesFiltrados.length === 0" class="px-4 py-3 text-sm text-neutral-500 text-center bg-neutral-50 flex flex-col items-center">
                                                            <span>No se encontró ningún cliente.</span>
                                                            <button type="button" @click="crearClienteOnTheFly(); openMenu = false" class="mt-2 px-3 py-1.5 bg-neutral-900 text-white rounded-lg font-bold text-xs hover:bg-neutral-800 transition-colors">
                                                                + Crear Nuevo Cliente
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" @click="modalQuickCliente = true; newClient.nombre = buscarClienteTerm" class="px-4 bg-neutral-100 text-neutral-700 hover:bg-neutral-200 transition-colors rounded-xl flex items-center justify-center" title="Crear Cliente Rápido">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </button>
                                            </div>
                                            
                                            <!-- Formulario Cliente Nuevo -->
                                            <div x-show="creandoCliente" x-cloak class="p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-3">
                                                <div class="flex justify-between items-center mb-1">
                                                    <span class="text-xs font-bold text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Nuevo Cliente
                                                    </span>
                                                    <button type="button" @click="cancelarCreacionCliente()" class="text-blue-600 hover:text-blue-800 text-xs font-bold px-2 py-1 bg-blue-100 rounded-md">Cancelar</button>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-neutral-600 mb-1">Nombre *</label>
                                                    <input type="text" x-model="nuevoCliente.nombre" required class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none bg-white">
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-neutral-600 mb-1">Teléfono</label>
                                                        <input type="text" x-model="nuevoCliente.telefono" class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-neutral-600 mb-1">Email</label>
                                                        <input type="email" x-model="nuevoCliente.email" class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none bg-white">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Prioridad</label>
                                            <select x-model="form.prioridad" class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors">
                                                <option value="Normal">Normal</option>
                                                <option value="Urgente">Urgente</option>
                                                <option value="Alta Prioridad">Alta Prioridad</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Fecha Estimada de Entrega</label>
                                            <input type="date" x-model="form.fecha_estimada_entrega" class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Hora Estimada</label>
                                            <input type="time" x-model="form.hora_estimada_entrega" class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors">
                                        </div>
                                    </div>

                                    <!-- Detalles (Productos) -->
                                    <div>
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-base font-bold text-neutral-900">Detalles del Pedido</h3>
                                            <button type="button" @click="agregarDetalle()" class="text-sm font-medium text-blue-600 hover:text-blue-700">+ Agregar Ítem</button>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <template x-for="(item, index) in form.detalles" :key="index">
                                                <div class="p-4 rounded-2xl border border-neutral-200 bg-[#FAFAFA] relative group">
                                                    <button type="button" @click="form.detalles.splice(index, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold shadow-sm">✕</button>
                                                    
                                                    <div class="grid grid-cols-12 gap-4">
                                                        <div class="col-span-12 md:col-span-4">
                                                            <label class="block text-xs font-semibold text-neutral-500 mb-1">Tipo</label>
                                                            <select x-model="item.tipo_producto" class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white">
                                                                <option value="Inventario">Catálogo (Inventario)</option>
                                                                <option value="Libre">Ítem Libre / Personalizado</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="col-span-12 md:col-span-8">
                                                            <template x-if="item.tipo_producto === 'Inventario'">
                                                                <div class="space-y-2">
                                                                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Variante *</label>

                                                                    <!-- Variante Seleccionada (chip) -->
                                                                    <template x-if="item._varianteSeleccionada">
                                                                        <div class="flex items-center gap-2 p-2.5 bg-neutral-900 text-white rounded-xl">
                                                                            <div class="flex-1 min-w-0">
                                                                                <p class="text-xs font-bold truncate" x-text="item._varianteSeleccionada.nombre_completo"></p>
                                                                                <p class="text-[10px] text-neutral-400 font-mono" x-text="item._varianteSeleccionada.sku + ' · L. ' + Number(item._varianteSeleccionada.precio).toFixed(2)"></p>
                                                                            </div>
                                                                            <button type="button" @click="quitarVariante(index)" class="flex-shrink-0 w-5 h-5 flex items-center justify-center text-neutral-400 hover:text-white transition-colors rounded-full">
                                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                            </button>
                                                                        </div>
                                                                    </template>

                                                                    <!-- Buscador -->
                                                                    <template x-if="!item._varianteSeleccionada">
                                                                        <div class="relative">
                                                                            <div class="relative">
                                                                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                                                <input
                                                                                    type="text"
                                                                                    x-model="item._varianteBusqueda"
                                                                                    @focus="item._showDropdown = true"
                                                                                    @click.away="item._showDropdown = false"
                                                                                    @input="item._showDropdown = true"
                                                                                    placeholder="Buscar por nombre o SKU..."
                                                                                    autocomplete="off"
                                                                                    class="w-full pl-8 pr-3 py-2 rounded-lg border border-neutral-200 text-sm focus:border-neutral-900 focus:outline-none bg-white transition-colors placeholder-neutral-300"
                                                                                />
                                                                            </div>

                                                                            <!-- Dropdown de Resultados -->
                                                                            <div x-show="item._showDropdown"
                                                                                 class="absolute top-full left-0 right-0 mt-1 bg-white border border-neutral-200 rounded-xl shadow-xl z-40 overflow-hidden"
                                                                                 style="max-height: 220px; overflow-y: auto;">
                                                                                
                                                                                <template x-if="variantesFiltradas(item._varianteBusqueda).length === 0">
                                                                                    <div class="py-4 text-center text-xs text-neutral-400">Sin resultados para "<span x-text="item._varianteBusqueda"></span>"</div>
                                                                                </template>

                                                                                <template x-for="v in variantesFiltradas(item._varianteBusqueda)" :key="v.id">
                                                                                    <button
                                                                                        type="button"
                                                                                        @click="seleccionarVariante(index, v); item._showDropdown = false"
                                                                                        class="w-full flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-neutral-50 transition-colors text-left border-b border-neutral-50 last:border-0 group">
                                                                                        <div class="flex-1 min-w-0">
                                                                                            <p class="text-xs font-semibold text-neutral-800 truncate" x-text="v.nombre_completo"></p>
                                                                                            <p class="text-[10px] text-neutral-400 font-mono mt-0.5" x-text="v.sku"></p>
                                                                                        </div>
                                                                                        <div class="flex items-center gap-2 flex-shrink-0">
                                                                                            <div class="text-right">
                                                                                                <p class="text-xs font-bold text-neutral-900" x-text="'L. ' + Number(v.precio).toFixed(2)"></p>
                                                                                                <p class="text-[9px] text-neutral-400" x-text="v.stock + ' disp.'"></p>
                                                                                            </div>
                                                                                            <span class="w-6 h-6 rounded-full bg-neutral-900 text-white flex items-center justify-center text-xs font-bold group-hover:bg-neutral-700 transition-colors">
                                                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                                                            </span>
                                                                                        </div>
                                                                                    </button>
                                                                                </template>
                                                                            </div>
                                                                        </div>
                                                                    </template>

                                                                    <!-- Extras -->
                                                                    <template x-if="item.producto_variante_id && variantesExtrasMap[item.producto_variante_id] && variantesExtrasMap[item.producto_variante_id].length > 0">
                                                                        <div class="mt-2 space-y-1 bg-white border border-neutral-100 rounded-xl p-2">
                                                                            <span class="text-[9px] font-bold text-neutral-400 uppercase tracking-wider block">Extras Disponibles:</span>
                                                                            <div class="flex flex-wrap gap-1.5 mt-1">
                                                                                <template x-for="extra in variantesExtrasMap[item.producto_variante_id]" :key="extra.id">
                                                                                    <label class="inline-flex items-center gap-1 px-2 py-0.5 bg-neutral-50 hover:bg-neutral-100 rounded-lg cursor-pointer transition-colors text-[10px] font-medium text-neutral-600 border border-neutral-100">
                                                                                        <input type="checkbox" :value="extra"
                                                                                               @change="togglePedidoExtra(index, extra)"
                                                                                               class="rounded text-neutral-900 focus:ring-neutral-900 border-neutral-300 w-3 h-3"/>
                                                                                        <span x-text="extra.nombre + ' (+L. ' + Number(extra.precio).toFixed(0) + ')'"></span>
                                                                                    </label>
                                                                                </template>
                                                                            </div>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="item.tipo_producto === 'Libre'">
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-neutral-500 mb-1">Descripción del Ítem *</label>
                                                                    <input type="text" x-model="item.nombre_libre" required placeholder="Ej. Lona publicitaria 2x2m" class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white">
                                                                </div>
                                                            </template>
                                                        </div>

                                                        <div class="col-span-4 md:col-span-3">
                                                            <label class="block text-xs font-semibold text-neutral-500 mb-1">Cant.</label>
                                                            <input type="number" x-model.number="item.cantidad" min="1" required class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white text-center">
                                                        </div>
                                                        <div class="col-span-8 md:col-span-4">
                                                            <label class="block text-xs font-semibold text-neutral-500 mb-1">Precio Unit. ($)</label>
                                                            <input type="number" step="0.01" x-model.number="item.precio_venta" required class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white text-right">
                                                        </div>
                                                        <div class="col-span-12 md:col-span-5 flex items-end justify-end">
                                                            <div class="text-right w-full bg-neutral-100 rounded-lg px-4 py-2 border border-neutral-200">
                                                                <span class="text-xs text-neutral-400 font-medium mr-2">Subtotal</span>
                                                                <span class="text-sm font-bold text-neutral-900" x-text="'L.' + (item.cantidad * item.precio_venta).toFixed(2)"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <div x-show="form.detalles.length === 0" class="text-center py-6 bg-[#FAFAFA] rounded-2xl border border-neutral-200 border-dashed">
                                                <p class="text-sm text-neutral-500 font-medium">Aún no hay ítems en este pedido.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Archivos -->
                                    <div>
                                        <h3 class="text-base font-bold text-neutral-900 mb-4">Archivos Adjuntos (Diseños, Logos)</h3>
                                        
                                        <!-- Drag and Drop Area -->
                                        <div class="relative group">
                                            <input type="file" x-ref="archivosInput" multiple @change="handleFiles" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*,application/pdf">
                                            <div class="flex flex-col items-center justify-center w-full h-32 border-2 border-neutral-300 border-dashed rounded-2xl bg-[#FAFAFA] group-hover:bg-neutral-100 group-hover:border-neutral-400 transition-all p-4 text-center">
                                                <svg class="w-8 h-8 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                <p class="text-sm font-medium text-neutral-600">Arrastra archivos aquí, pégalos con Ctrl+V, o haz clic para subir</p>
                                                <p class="text-xs text-neutral-400 mt-1">Imágenes o PDFs hasta 10MB</p>
                                                <div x-show="archivosLista.length > 0" class="mt-3 text-sm text-green-600 font-bold" x-text="archivosLista.length + ' archivo(s) listo(s) para adjuntar'"></div>
                                            </div>
                                        </div>

                                        <!-- Lista de Archivos -->
                                        <div class="mt-4 space-y-2" x-show="archivosLista.length > 0">
                                            <template x-for="(file, index) in archivosLista" :key="index">
                                                <div class="flex items-center justify-between p-3 bg-white border border-neutral-200 rounded-xl shadow-sm">
                                                    <div class="flex items-center gap-3 overflow-hidden">
                                                        <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                        </div>
                                                        <span class="text-sm font-medium text-neutral-700 truncate" x-text="file.name"></span>
                                                    </div>
                                                    <button type="button" @click="quitarArchivo(index)" class="text-xs font-bold text-red-500 hover:text-red-700 px-2 py-1 bg-red-50 rounded-md">Eliminar</button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Resumen Financiero -->
                                    <div class="bg-neutral-900 p-6 rounded-3xl text-white space-y-4 shadow-lg">
                                        <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-400 mb-2">Resumen Financiero</h3>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-neutral-300">Subtotal de Ítems</span>
                                            <span class="text-sm font-medium" x-text="'L.' + calculoSubtotal.toFixed(2)"></span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-neutral-300">Descuento Global ($)</span>
                                            <input type="number" step="0.01" x-model.number="form.descuento" class="w-24 text-right rounded-lg bg-neutral-800 border border-neutral-700 px-3 py-1.5 text-sm focus:border-white focus:outline-none transition-colors">
                                        </div>

                                        <div class="pt-4 border-t border-neutral-800 flex justify-between items-center">
                                            <span class="text-base font-bold">Total del Pedido</span>
                                            <span class="text-2xl font-bold" x-text="'L.' + calculoTotal.toFixed(2)"></span>
                                        </div>

                                        <div class="pt-4 border-t border-neutral-800 flex justify-between items-center" :class="calculoTotal > 0 ? 'text-yellow-400' : 'text-green-400'">
                                            <span class="text-sm font-bold uppercase tracking-wider">Saldo Pendiente</span>
                                            <span class="text-xl font-bold" x-text="'L.' + calculoTotal.toFixed(2)"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-neutral-700 mb-2">Notas Adicionales</label>
                                        <textarea x-model="form.notas" rows="2" class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors" placeholder="Indicaciones especiales..."></textarea>
                                    </div>
                                    
                                    <!-- Mensaje de Error -->
                                    <div x-show="errorMensaje" class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm font-medium text-red-600" x-text="errorMensaje"></div>
                                </form>
                            </div>
                            
                            <!-- Footer -->
                            <div class="px-8 py-5 border-t border-neutral-100 bg-white flex justify-end gap-3 flex-shrink-0">
                                <button @click="openSlideOver = false" class="px-6 py-3 text-sm font-bold text-neutral-500 hover:bg-neutral-100 rounded-xl transition-colors">Cancelar</button>
                                <button @click="submitPedido" :disabled="guardando || form.detalles.length === 0" class="px-8 py-3 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 active:scale-95 transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                    <svg x-show="guardando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span x-text="guardando ? 'Guardando...' : 'Crear Pedido'"></span>
                                </button>
                            </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalles de Pedido -->
    <div x-show="modalDetalles" class="relative z-50" x-cloak>
        <div x-show="modalDetalles" x-transition.opacity class="fixed inset-0 bg-neutral-900/40 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalDetalles"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-5xl transform overflow-hidden rounded-3xl bg-white shadow-2xl">
                    
                     <template x-if="pedidoSeleccionado">
                        <div class="bg-neutral-50/50">
                            <!-- Header -->
                            <div class="px-8 py-5 border-b border-neutral-100 flex items-center justify-between bg-white sticky top-0 z-10">
                                <div>
                                    <h3 class="text-lg font-bold text-neutral-900">Detalles del Pedido</h3>
                                </div>
                                <button @click="modalDetalles = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-xl transition-all">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="p-8">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    
                                    <!-- COLUMNA IZQUIERDA (2/3) -->
                                    <div class="lg:col-span-2 space-y-6">
                                        
                                        <!-- Encabezado / Estado -->
                                        <div class="bg-white border border-neutral-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                                            <div>
                                                <h3 class="text-2xl font-black text-neutral-900 flex items-center gap-3">
                                                    <span x-text="pedidoSeleccionado.numero_orden" class="bg-neutral-100 text-neutral-600 px-3 py-1 rounded-lg text-sm border border-neutral-200"></span>
                                                    <span x-text="pedidoSeleccionado.cliente?.nombre"></span>
                                                </h3>
                                                <p class="text-sm text-neutral-500 mt-1 font-medium" x-text="pedidoSeleccionado.cliente?.telefono ? 'Tel: ' + pedidoSeleccionado.cliente.telefono : ''"></p>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3">
                                                <select x-model="pedidoSeleccionado.estado" class="rounded-xl border border-neutral-200 px-4 py-2.5 text-sm font-bold text-neutral-900 focus:outline-none focus:border-neutral-400 bg-neutral-50 flex-shrink-0">
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="Diseño">Diseño</option>
                                                    <option value="Esperando Aprobación">Esperando Aprobación</option>
                                                    <option value="Producción">Producción</option>
                                                    <option value="Pausado">Pausado</option>
                                                    <option value="Listo para Entrega">Listo para Entrega</option>
                                                    <option value="Entregado">Entregado</option>
                                                    <option value="Cancelado">Cancelado</option>
                                                </select>
                                                <button @click="cambiarEstado(pedidoSeleccionado)" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 transition-colors shadow-sm whitespace-nowrap flex-shrink-0" :disabled="guardandoEstado">
                                                    <span x-text="guardandoEstado ? '...' : 'Actualizar'"></span>
                                                </button>
                                                <button @click="enviarWhatsApp(pedidoSeleccionado)" class="px-4 py-2.5 bg-green-500 text-white text-sm font-bold rounded-xl hover:bg-green-600 transition-colors shadow-sm flex items-center gap-1 whitespace-nowrap flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.384 0 12.032c0 2.115.548 4.184 1.591 6.002L.039 24l6.113-1.605A11.968 11.968 0 0012.031 24c6.645 0 12.03-5.384 12.03-12.032C24.061 5.384 18.676 0 12.031 0zm3.87 17.262c-.22.617-1.282 1.157-1.802 1.258-.521.1-1.218.176-2.923-.523-2.035-.833-3.327-2.936-3.427-3.072-.101-.137-2.183-2.909-2.183-5.551 0-2.641 1.365-3.935 1.865-4.471.5-.536 1.08-.67 1.441-.67.36 0 .72.015.98.027.28.013.66.082 1.03.972.37.89 1.26 3.09 1.37 3.32.11.23.18.5.05.77-.13.27-.2.43-.4.67-.2.23-.42.52-.6.71-.2.2-.41.42-.18.82.23.4 1.02 1.69 2.19 2.73 1.5 1.35 2.74 1.77 3.14 1.93.4.17.64.13.88-.13.24-.27 1.02-1.19 1.3-1.6.28-.4.56-.34.93-.2.37.13 2.33 1.1 2.73 1.3.4.2.66.3.76.47.1.17.1.99-.12 1.6z"/></svg>
                                                    WhatsApp
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Fechas y Notas -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
                                                <p class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-1">Fecha de Entrega</p>
                                                <p class="text-lg font-bold text-neutral-900" x-text="(pedidoSeleccionado.fecha_estimada_entrega ? pedidoSeleccionado.fecha_estimada_entrega.substring(0,10) : 'No definida') + (pedidoSeleccionado.hora_estimada_entrega ? ' a las ' + pedidoSeleccionado.hora_estimada_entrega.substring(0,5) : '')"></p>
                                                
                                                <div class="mt-3 pt-3 border-t border-neutral-100 space-y-2">
                                                    <label class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Fecha Real de Entrega</label>
                                                    <input type="date" 
                                                           :value="pedidoSeleccionado.fecha_entrega ? pedidoSeleccionado.fecha_entrega.substring(0,10) : ''"
                                                           @change="actualizarFechaEntrega($event.target.value)" 
                                                           class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-xs text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                                                </div>
                                                
                                                <template x-if="pedidoSeleccionado.prioridad === 'Urgente'">
                                                    <span class="inline-block mt-2 text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-1.5 rounded-md uppercase tracking-wider border border-orange-100">Prioridad Urgente</span>
                                                </template>
                                                <template x-if="pedidoSeleccionado.prioridad === 'Alta Prioridad'">
                                                    <span class="inline-block mt-2 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-1.5 rounded-md uppercase tracking-wider border border-red-100">Alta Prioridad</span>
                                                </template>
                                            </div>
                                            <div class="bg-neutral-50/80 border border-neutral-200 rounded-2xl p-5 shadow-sm">
                                                <p class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-1">Notas del Pedido</p>
                                                <p class="text-sm text-neutral-700 font-medium whitespace-pre-wrap" x-text="pedidoSeleccionado.notas || 'Sin observaciones.'"></p>
                                            </div>
                                        </div>

                                        <!-- Productos -->
                                        <div class="bg-white border border-neutral-200 rounded-2xl p-6 shadow-sm">
                                            <h4 class="text-xs font-bold text-neutral-400 mb-4 uppercase tracking-wider">Productos Solicitados</h4>
                                            <div class="overflow-hidden rounded-xl border border-neutral-100">
                                                <table class="w-full text-left text-sm">
                                                    <thead class="bg-neutral-50 text-neutral-500 font-bold text-xs uppercase">
                                                        <tr>
                                                            <th class="px-4 py-3">Ítem</th>
                                                            <th class="px-4 py-3 text-center">Cant.</th>
                                                            <th class="px-4 py-3 text-right">Precio</th>
                                                            <th class="px-4 py-3 text-right">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-neutral-100">
                                                        <template x-for="d in pedidoSeleccionado.detalles" :key="d.id">
                                                            <tr class="hover:bg-neutral-50 transition-colors">
                                                                <td class="px-4 py-3 font-bold text-neutral-900" x-text="d.nombre_snapshot || d.nombre_libre"></td>
                                                                <td class="px-4 py-3 text-center font-bold text-neutral-700" x-text="d.cantidad"></td>
                                                                <td class="px-4 py-3 text-right text-neutral-500" x-text="'L.' + Number(d.precio_unitario).toFixed(2)"></td>
                                                                <td class="px-4 py-3 text-right font-black text-neutral-900" x-text="'L.' + Number(d.subtotal).toFixed(2)"></td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- COLUMNA DERECHA (1/3) -->
                                    <div class="lg:col-span-1 space-y-6">
                                        
                                        <!-- Archivos Adjuntos (CRÍTICO) -->
                                        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm space-y-4">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Diseños & Archivos</h4>
                                                <div>
                                                    <label class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                        Subir Diseños
                                                        <input type="file" multiple class="hidden" @change="subirArchivos($event)">
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div x-show="!pedidoSeleccionado.archivos || pedidoSeleccionado.archivos.length === 0" class="text-center py-6 bg-neutral-50 rounded-xl border border-dashed border-neutral-200">
                                                <p class="text-xs text-neutral-500 font-medium">No hay archivos adjuntos.</p>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-3" x-show="pedidoSeleccionado.archivos && pedidoSeleccionado.archivos.length > 0">
                                                <template x-for="a in pedidoSeleccionado.archivos" :key="a.id">
                                                    <div class="relative group rounded-xl overflow-hidden border border-neutral-200 bg-neutral-50 aspect-square flex flex-col items-center justify-center">
                                                        <template x-if="a.ruta.match(/\.(jpeg|jpg|gif|png|webp)$/i) != null">
                                                            <div class="absolute inset-0 w-full h-full">
                                                                <img :src="a.url" @@error="$event.target.style.display='none'; $event.target.nextElementSibling.style.display='flex'" class="absolute inset-0 w-full h-full object-cover" />
                                                                <div style="display:none" class="absolute inset-0 flex flex-col items-center justify-center bg-neutral-100 text-neutral-400">
                                                                    <svg class="w-8 h-8 text-neutral-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <!-- Preview Documento -->
                                                        <template x-if="a.ruta.match(/\.(jpeg|jpg|gif|png|webp)$/i) == null">
                                                            <svg class="w-8 h-8 text-neutral-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                        </template>
                                                        
                                                        <!-- Overlay Oscuro en Hover -->
                                                        <div class="absolute inset-0 bg-neutral-900/80 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-3">
                                                            <span class="text-[10px] text-white font-medium truncate w-full text-center mb-3" x-text="a.nombre_original"></span>
                                                            <a :href="a.url" target="_blank" download class="p-2.5 bg-white rounded-full text-neutral-900 hover:scale-110 transition-transform shadow-lg">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Resumen Financiero -->
                                        <div class="bg-neutral-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                                            <!-- Decoración sutil -->
                                            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-24 h-24 bg-white/5 rounded-full blur-2xl"></div>
                                            
                                            <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-4 relative">Resumen Financiero</h4>
                                            
                                            <div class="space-y-2 relative">
                                                <div class="flex justify-between items-center text-sm">
                                                    <span class="text-neutral-400">Total Pedido</span>
                                                    <span class="font-bold text-neutral-200" x-text="'L.' + Number(pedidoSeleccionado.total_pedido).toFixed(2)"></span>
                                                </div>
                                                <div class="flex justify-between items-center text-sm pb-4 border-b border-neutral-700/50">
                                                    <span class="text-neutral-400">Total Abonado</span>
                                                    <span class="font-bold text-green-400" x-text="'L.' + Number(pedidoSeleccionado.total_abonado).toFixed(2)"></span>
                                                </div>
                                                <div class="flex justify-between items-end pt-2">
                                                    <span class="text-xs font-bold text-neutral-400 uppercase">Saldo Pendiente</span>
                                                    <span class="text-3xl font-black" :class="Number(pedidoSeleccionado.saldo_pendiente) > 0 ? 'text-red-400' : 'text-white'" x-text="'L.' + Number(pedidoSeleccionado.saldo_pendiente).toFixed(2)"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Funciones Disponibles -->
                                        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm space-y-2">
                                            <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-3">Funciones Disponibles</h4>
                                            
                                            <template x-if="Number(pedidoSeleccionado.saldo_pendiente) > 0">
                                                <a :href="'/pos?orden=' + pedidoSeleccionado.numero_orden" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-neutral-200 text-neutral-900 text-sm font-bold rounded-xl hover:bg-neutral-50 transition-colors shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Registrar Abono
                                                </a>
                                            </template>
                                            
                                            <a :href="'/pedidos/' + pedidoSeleccionado.id + '/a4'" target="_blank" download class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-neutral-200 text-neutral-700 text-sm font-bold rounded-xl hover:bg-neutral-50 transition-colors shadow-sm mt-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                Descargar PDF (A4)
                                            </a>
                                            
                                            <a :href="'/pedidos/' + pedidoSeleccionado.id + '/ticket'" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-neutral-200 text-neutral-700 text-sm font-bold rounded-xl hover:bg-neutral-50 transition-colors shadow-sm mt-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                Imprimir Ticket (80mm)
                                            </a>
                                            
                                            <template x-if="pedidoSeleccionado.cliente?.telefono">
                                                <a :href="'https://wa.me/' + pedidoSeleccionado.cliente.telefono.replace(/\D/g,'') + '?text=Hola ' + pedidoSeleccionado.cliente.nombre + ', aquí tienes el enlace a tu pedido: ' + encodeURIComponent('{{ url('/') }}/pedidos/track/' + pedidoSeleccionado.numero_orden)" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm font-bold rounded-xl hover:bg-green-100 transition-colors shadow-sm mt-2">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51h-.57c-.199 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                                    Compartir por WhatsApp
                                                </a>
                                            </template>

                                            <!-- Acciones Destructivas -->
                                            <div class="pt-4 mt-2 border-t border-neutral-100 flex gap-2">
                                                <button @click="motivoCancelacion = ''; modalCancelarPedido = true;" class="flex-1 px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-xl hover:bg-red-100 transition-colors flex items-center justify-center gap-1.5 border border-red-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Cancelar
                                                </button>

                                                @if(auth()->check() && (auth()->user()->rol === 'admin' || auth()->user()->id === 1))
                                                    <button @click="pedidoAEliminarId = pedidoSeleccionado.id; modalEliminarPedido = true;" class="px-3 py-2 bg-white text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition-colors" title="Eliminar Pedido (Admin)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar WhatsApp (Fase 2) -->
    <div x-show="modalConfirmarWhatsapp" class="relative z-[60]" x-cloak>
        <div x-show="modalConfirmarWhatsapp" x-transition.opacity class="fixed inset-0 bg-neutral-900/50 backdrop-blur-sm"></div>
        <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4">
            <div x-show="modalConfirmarWhatsapp" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl border border-neutral-200 p-6 max-w-sm w-full shadow-xl">
                
                <h3 class="text-lg font-bold text-neutral-900 mb-2">Notificar al Cliente</h3>
                <p class="text-sm text-neutral-600 mb-6">¿Deseas notificar al cliente sobre este cambio de estado a <span class="font-bold text-neutral-900" x-text="estadoTemp"></span> por WhatsApp?</p>
                
                <div class="space-y-2">
                    <button @click="confirmarYEnviarWhatsapp()" class="w-full py-2.5 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition-colors shadow-sm flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.384 0 12.032c0 2.115.548 4.184 1.591 6.002L.039 24l6.113-1.605A11.968 11.968 0 0012.031 24c6.645 0 12.03-5.384 12.03-12.032C24.061 5.384 18.676 0 12.031 0zm3.87 17.262c-.22.617-1.282 1.157-1.802 1.258-.521.1-1.218.176-2.923-.523-2.035-.833-3.327-2.936-3.427-3.072-.101-.137-2.183-2.909-2.183-5.551 0-2.641 1.365-3.935 1.865-4.471.5-.536 1.08-.67 1.441-.67.36 0 .72.015.98.027.28.013.66.082 1.03.972.37.89 1.26 3.09 1.37 3.32.11.23.18.5.05.77-.13.27-.2.43-.4.67-.2.23-.42.52-.6.71-.2.2-.41.42-.18.82.23.4 1.02 1.69 2.19 2.73 1.5 1.35 2.74 1.77 3.14 1.93.4.17.64.13.88-.13.24-.27 1.02-1.19 1.3-1.6.28-.4.56-.34.93-.2.37.13 2.33 1.1 2.73 1.3.4.2.66.3.76.47.1.17.1.99-.12 1.6z"/></svg>
                        Cambiar Estado y Enviar WhatsApp
                    </button>
                    <button @click="confirmarSoloCambiar()" class="w-full py-2.5 bg-neutral-900 text-white font-bold rounded-xl hover:bg-neutral-800 transition-colors shadow-sm text-sm">
                        Solo Cambiar Estado
                    </button>
                    <button @click="cancelarCambioEstado()" class="w-full py-2.5 bg-neutral-100 text-neutral-600 font-semibold rounded-xl hover:bg-neutral-200 transition-colors text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal: Nuevo Cliente Rápido -->
    <div x-show="modalQuickCliente" class="relative z-[60]" x-cloak>
        <div x-show="modalQuickCliente" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalQuickCliente"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="modalQuickCliente = false"
                     class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                        <h3 class="text-lg font-bold text-neutral-900">Nuevo Cliente</h3>
                        <button @click="modalQuickCliente = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-if="quickClientError">
                            <p class="text-xs text-red-500" x-text="quickClientError"></p>
                        </template>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Nombre Completo *</label>
                            <input type="text" x-model="newClient.nombre" required placeholder="Nombre..."
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Teléfono</label>
                            <input type="text" x-model="newClient.telefono" placeholder="Ej. 9988-7766"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Email</label>
                            <input type="email" x-model="newClient.email" placeholder="correo@ejemplo.com"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalQuickCliente = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="button" @click="guardarQuickCliente()" :disabled="guardandoQuickClient" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm disabled:opacity-50">
                                <span x-show="!guardandoQuickClient">Guardar Cliente</span>
                                <span x-show="guardandoQuickClient">Guardando...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Cancelar Pedido (Motivo) -->
    <div x-show="modalCancelarPedido" class="relative z-[60]" x-cloak>
        <div x-show="modalCancelarPedido" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4">
            <div x-show="modalCancelarPedido" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-3xl border border-neutral-200 p-7 max-w-md w-full shadow-2xl space-y-5">
                
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                    <h3 class="text-lg font-bold text-neutral-900">Cancelar Pedido</h3>
                    <button @click="modalCancelarPedido = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <p class="text-sm text-neutral-600">Por favor, indica el motivo por el cual estás cancelando este pedido. El stock reservado se liberará automáticamente.</p>
                    
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Motivo de Cancelación</label>
                        <textarea x-model="motivoCancelacion" rows="3" placeholder="Ej. El cliente decidió retirar su solicitud / error en cantidades..."
                                  class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"></textarea>
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                    <button type="button" @click="modalCancelarPedido = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Volver</button>
                    <button type="button" @click="ejecutarCancelar()" :disabled="guardando" class="px-5 py-2.5 bg-red-650 hover:bg-red-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm disabled:opacity-50">
                        Confirmar Cancelación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Eliminar Pedido (Confirmación) -->
    <div x-show="modalEliminarPedido" class="relative z-[60]" x-cloak>
        <div x-show="modalEliminarPedido" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4">
            <div x-show="modalEliminarPedido" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-3xl border border-neutral-200 p-7 max-w-md w-full shadow-2xl space-y-5">
                
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                    <h3 class="text-lg font-bold text-neutral-900">¿Eliminar definitivamente?</h3>
                    <button @click="modalEliminarPedido = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="space-y-3">
                    <p class="text-sm text-neutral-600">Esta acción no se puede deshacer. Se borrará permanentemente el historial de este pedido y se liberará cualquier stock reservado en el inventario.</p>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                    <button type="button" @click="modalEliminarPedido = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Volver</button>
                    <button type="button" @click="ejecutarEliminar()" :disabled="guardando" class="px-5 py-2.5 bg-red-650 hover:bg-red-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm disabled:opacity-50">
                        Confirmar Eliminación
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@php
$dataVariantes = \App\Models\ProductoVariante::with(['producto.extras'])
    ->where('activo', true)
    ->whereHas('producto', function($q) { $q->where('activo', true); })
    ->get()
    ->map(function($v) {
        return [
            'id'              => $v->id,
            'sku'             => $v->sku,
            'nombre_completo' => $v->nombre_completo,
            'precio'          => (float) $v->precio,
            'stock'           => $v->stock_disponible,
            'extras'          => $v->producto->extras->map(function($e) {
                return [
                    'id'     => $e->id,
                    'nombre' => $e->nombre,
                    'costo'  => (float) $e->costo,
                    'precio' => (float) $e->precio,
                ];
            })->values(),
        ];
    });
@endphp
<script>
    const plantillasConfig = @json($plantillas);
</script>
<script>
    function kanbanBoard() {
        return {
            init() {
                // Construir mapa de extras por variante_id
                this.variantesExtrasMap = {};
                this.variantesData.forEach(v => { this.variantesExtrasMap[v.id] = v.extras; });

                const urlParams = new URLSearchParams(window.location.search);
                const pedidoId = urlParams.get('id');
                if (pedidoId) {
                    setTimeout(() => {
                        this.abrirDetalles(parseInt(pedidoId));
                    }, 200);
                }
                if (urlParams.get('crear') === 'true') {
                    setTimeout(() => {
                        this.openModal();
                    }, 200);
                }
            },
            filtroActual: 'todos',
            openSlideOver: false,
            guardando: false,
            errorMensaje: '',
            archivosFiles: [], // File objects reales
            archivosLista: [], // Para UI preview
            form: {
                cliente_id: '',
                prioridad: 'Normal',
                fecha_estimada_entrega: '',
                hora_estimada_entrega: '',
                notas: '',
                descuento: 0,
                detalles: []
            },
            
            clientesList: @json(\App\Models\Cliente::all()),
            pedidosList: @json($pedidos->flatten()),
            variantesData: @json($dataVariantes),
            variantesExtrasMap: {},
            pedidoSeleccionado: null,
            modalDetalles: false,
            modalConfirmarWhatsapp: false,
            modalQuickCliente: false,
            modalCancelarPedido: false,
            motivoCancelacion: '',
            modalEliminarPedido: false,
            pedidoAEliminarId: null,
            newClient: { nombre: '', telefono: '', email: '' },
            guardandoQuickClient: false,
            quickClientError: '',
            estadoTemp: '',
            guardandoEstado: false,
            buscarClienteTerm: '',
            clienteSeleccionadoObj: null,
            creandoCliente: false,
            nuevoCliente: { nombre: '', telefono: '', email: '' },

            get clientesFiltrados() {
                if(!this.buscarClienteTerm) return this.clientesList.slice(0, 5);
                const term = this.buscarClienteTerm.toLowerCase();
                return this.clientesList.filter(c => 
                    c.nombre.toLowerCase().includes(term) || 
                    (c.telefono && c.telefono.includes(term)) ||
                    (c.email && c.email.toLowerCase().includes(term))
                ).slice(0, 10);
            },

            seleccionarCliente(c) {
                this.form.cliente_id = c.id;
                this.clienteSeleccionadoObj = c;
                this.creandoCliente = false;
                this.buscarClienteTerm = '';
            },

            quitarCliente() {
                this.form.cliente_id = '';
                this.clienteSeleccionadoObj = null;
            },

            crearClienteOnTheFly() {
                this.creandoCliente = true;
                this.nuevoCliente.nombre = this.buscarClienteTerm;
                this.quitarCliente();
            },

            cancelarCreacionCliente() {
                this.creandoCliente = false;
                this.nuevoCliente = { nombre: '', telefono: '', email: '' };
                this.buscarClienteTerm = '';
            },

            get calculoSubtotal() {
                return this.form.detalles.reduce((acc, item) => acc + (item.cantidad * item.precio_venta), 0);
            },
            get calculoTotal() {
                return Math.max(0, this.calculoSubtotal - (Number(this.form.descuento) || 0));
            },


            openModal() {
                this.errorMensaje = '';
                this.archivosFiles = [];
                this.archivosLista = [];
                this.buscarClienteTerm = '';
                this.clienteSeleccionadoObj = null;
                this.creandoCliente = false;
                this.nuevoCliente = { nombre: '', telefono: '', email: '' };
                this.form = {
                    cliente_id: '',
                    prioridad: 'Normal',
                    fecha_estimada_entrega: '',
                    hora_estimada_entrega: '',
                    notas: '',
                    descuento: 0,
                    detalles: [
                        { tipo_producto: 'Inventario', producto_variante_id: '', nombre_libre: '', descripcion_libre: '', cantidad: 1, precio_venta: 0 }
                    ]
                };
                this.openSlideOver = true;
            },

            abrirDetalles(id) {
                const pedido = this.pedidosList.find(p => p.id === id);
                if(pedido) {
                    this.pedidoSeleccionado = JSON.parse(JSON.stringify(pedido)); // clone
                    this.modalDetalles = true;
                }
            },

            actualizarPedidoEnTablero(updatedPedido) {
                const idx = this.pedidosList.findIndex(p => p.id === updatedPedido.id);
                if (idx !== -1) {
                    this.pedidosList[idx] = updatedPedido;
                }
            },

            async guardarQuickCliente() {
                if (!this.newClient.nombre.trim()) {
                    this.quickClientError = 'El nombre es obligatorio.';
                    return;
                }
                this.guardandoQuickClient = true;
                this.quickClientError = '';

                try {
                    const res = await fetch('{{ route('clientes.quickStore') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.newClient)
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.clientesList.push(data.cliente);
                        this.seleccionarCliente(data.cliente);
                        this.modalQuickCliente = false;
                        this.newClient = { nombre: '', telefono: '', email: '' };
                    } else {
                        this.quickClientError = data.message || 'Error al guardar.';
                    }
                } catch (e) {
                    this.quickClientError = 'Error de conexión.';
                } finally {
                    this.guardandoQuickClient = false;
                }
            },

            async actualizarFechaEntrega(value) {
                if (!value) return;
                try {
                    const res = await fetch(`/pedidos/${this.pedidoSeleccionado.id}/fecha-entrega`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ fecha_entrega: value })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.pedidoSeleccionado.fecha_entrega = data.fecha_entrega;
                        this.actualizarPedidoEnTablero(data.pedido);
                    } else {
                        alert(data.message || 'Error al actualizar la fecha');
                    }
                } catch (e) {
                    alert('Error de conexión.');
                }
            },

            async ejecutarCancelar() {
                if (this.guardando) return;
                this.guardando = true;
                try {
                    const res = await fetch(`/pedidos/${this.pedidoSeleccionado.id}/cancelar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ motivo_cancelacion: this.motivoCancelacion })
                    });
                    if (res.redirected) {
                        window.location.href = res.url;
                    } else {
                        window.location.reload();
                    }
                } catch (err) {
                    alert('Error al cancelar el pedido.');
                } finally {
                    this.guardando = false;
                    this.modalCancelarPedido = false;
                }
            },

            async ejecutarEliminar() {
                if (this.guardando) return;
                this.guardando = true;
                try {
                    const res = await fetch(`/pedidos/${this.pedidoAEliminarId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.modalEliminarPedido = false;
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar el pedido.');
                    }
                } catch (err) {
                    alert('Error al eliminar el pedido.');
                } finally {
                    this.guardando = false;
                }
            },

            async subirArchivos(event) {
                let files = event.target.files;
                if (files.length === 0) return;
                
                let formData = new FormData();
                for (let i = 0; i < files.length; i++) {
                    formData.append('archivos[]', files[i]);
                }
                
                try {
                    const res = await fetch(`/pedidos/${this.pedidoSeleccionado.id}/archivos`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.pedidoSeleccionado.archivos = data.archivos;
                        this.actualizarPedidoEnTablero(data.pedido);
                    } else {
                        alert(data.message || 'Error al subir archivos');
                    }
                } catch (err) {
                    alert('Error al subir archivos');
                }
            },

            cambiarEstado(pedido) {
                const original = this.pedidosList.find(p => p.id === pedido.id);
                if (original && original.estado === pedido.estado) {
                    this.modalDetalles = false;
                    return;
                }
                this.estadoTemp = pedido.estado;
                this.modalConfirmarWhatsapp = true;
            },

            async ejecutarCambioEstado(pedido, enviarWA = false) {
                this.guardandoEstado = true;
                try {
                    const res = await fetch(`/pedidos/${pedido.id}/estado`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ estado: this.estadoTemp })
                    });
                    const data = await res.json();
                    if(data.success) {
                        if (enviarWA) {
                            pedido.estado = this.estadoTemp;
                            this.enviarWhatsApp(pedido);
                        }
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al cambiar el estado.');
                    }
                } catch(e) {
                    alert('Error de red al cambiar el estado.');
                } finally {
                    this.guardandoEstado = false;
                    this.modalConfirmarWhatsapp = false;
                }
            },

            confirmarYEnviarWhatsapp() {
                this.ejecutarCambioEstado(this.pedidoSeleccionado, true);
            },

            confirmarSoloCambiar() {
                this.ejecutarCambioEstado(this.pedidoSeleccionado, false);
            },

            cancelarCambioEstado() {
                this.modalConfirmarWhatsapp = false;
                const original = this.pedidosList.find(p => p.id === this.pedidoSeleccionado.id);
                if (original) {
                    this.pedidoSeleccionado.estado = original.estado;
                }
            },

            agregarDetalle() {
                this.form.detalles.push({
                    tipo_producto: 'Inventario',
                    producto_variante_id: '',
                    _varianteBusqueda: '',
                    _varianteSeleccionada: null,
                    _showDropdown: false,
                    nombre_libre: '',
                    descripcion_libre: '',
                    cantidad: 1,
                    precio_venta: 0,
                    extras: [],
                });
            },

            variantesFiltradas(term) {
                if (!term || term.length < 1) return this.variantesData.slice(0, 8);
                const q = term.toLowerCase();
                return this.variantesData.filter(v =>
                    v.nombre_completo.toLowerCase().includes(q) ||
                    v.sku.toLowerCase().includes(q)
                ).slice(0, 10);
            },

            seleccionarVariante(index, variante) {
                const item = this.form.detalles[index];
                item.producto_variante_id = variante.id;
                item._varianteSeleccionada = variante;
                item._varianteBusqueda = '';
                item._showDropdown = false;
                item.extras = [];
                item.precio_venta = variante.precio;
            },

            quitarVariante(index) {
                const item = this.form.detalles[index];
                item.producto_variante_id = '';
                item._varianteSeleccionada = null;
                item._varianteBusqueda = '';
                item._showDropdown = false;
                item.extras = [];
                item.precio_venta = 0;
            },

            togglePedidoExtra(index, extra) {
                const item = this.form.detalles[index];
                if (!item.extras) item.extras = [];
                const idx = item.extras.findIndex(e => e.id === extra.id);
                if (idx > -1) {
                    item.extras.splice(idx, 1);
                } else {
                    item.extras.push(extra);
                }
                const base = item._varianteSeleccionada ? parseFloat(item._varianteSeleccionada.precio) : 0;
                const extrasPrecio = item.extras.reduce((s, e) => s + parseFloat(e.precio), 0);
                item.precio_venta = base + extrasPrecio;
            },

            handlePaste(e) {
                // Solo escuchar si el modal de creación (SlideOver) está abierto
                if (!this.openSlideOver) return;

                const items = (e.clipboardData || e.originalEvent.clipboardData).items;
                let imagePasted = false;
                const dataTransfer = new DataTransfer();
                
                // Mantener los archivos que ya estaban en el input (si los hay)
                if (this.$refs.archivosInput && this.$refs.archivosInput.files) {
                    for (let i = 0; i < this.$refs.archivosInput.files.length; i++) {
                        dataTransfer.items.add(this.$refs.archivosInput.files[i]);
                    }
                }

                // Buscar si hay una imagen en el portapapeles
                for (let index in items) {
                    const item = items[index];
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        
                        // Generar un nombre único para el archivo pegado
                        const extension = item.type.split('/')[1] || 'png';
                        const newFile = new File([file], 'diseno_pegado_' + Date.now() + '.' + extension, { type: item.type });
                        
                        dataTransfer.items.add(newFile);
                        imagePasted = true;
                    }
                }

                // Si se pegó una imagen, actualizar el input y notificar a Alpine
                if (imagePasted && this.$refs.archivosInput) {
                    this.$refs.archivosInput.files = dataTransfer.files;
                    // Disparar evento change para que se actualice cualquier preview visual
                    this.$refs.archivosInput.dispatchEvent(new Event('change', { bubbles: true })); 
                }
            },

            handleFiles(e) {
                const files = e.target.files;
                for(let i=0; i<files.length; i++) {
                    this.archivosFiles.push(files[i]);
                    this.archivosLista.push({ name: files[i].name, size: files[i].size });
                }
                e.target.value = ''; // Reset input
            },

            quitarArchivo(index) {
                this.archivosFiles.splice(index, 1);
                this.archivosLista.splice(index, 1);
            },

            async submitPedido() {
                if((!this.form.cliente_id && !this.creandoCliente) || (this.creandoCliente && !this.nuevoCliente.nombre) || this.form.detalles.length === 0) {
                    this.errorMensaje = 'El cliente (existente o nuevo) y al menos un detalle son requeridos.';
                    return;
                }

                this.guardando = true;
                this.errorMensaje = '';

                let formData = new FormData();
                if(this.form.cliente_id) {
                    formData.append('cliente_id', this.form.cliente_id);
                } else if(this.creandoCliente) {
                    formData.append('nuevo_cliente_nombre', this.nuevoCliente.nombre);
                    if(this.nuevoCliente.telefono) formData.append('nuevo_cliente_telefono', this.nuevoCliente.telefono);
                    if(this.nuevoCliente.email) formData.append('nuevo_cliente_email', this.nuevoCliente.email);
                }
                
                formData.append('prioridad', this.form.prioridad);
                formData.append('fecha_estimada_entrega', this.form.fecha_estimada_entrega);
                formData.append('hora_estimada_entrega', this.form.hora_estimada_entrega);
                formData.append('notas', this.form.notas);
                formData.append('subtotal', this.calculoSubtotal);
                formData.append('descuento', this.form.descuento || 0);
                formData.append('total_pedido', this.calculoTotal);

                this.form.detalles.forEach((det, i) => {
                    formData.append(`detalles[${i}][tipo_producto]`, det.tipo_producto);
                    formData.append(`detalles[${i}][cantidad]`, det.cantidad);
                    formData.append(`detalles[${i}][precio_venta]`, det.precio_venta);
                    if(det.tipo_producto === 'Inventario') {
                        formData.append(`detalles[${i}][producto_variante_id]`, det.producto_variante_id);
                    } else {
                        formData.append(`detalles[${i}][nombre_libre]`, det.nombre_libre);
                        formData.append(`detalles[${i}][descripcion_libre]`, det.descripcion_libre || '');
                    }
                });

                this.archivosFiles.forEach((file, i) => {
                    formData.append(`archivos[${i}]`, file);
                });

                try {
                    const res = await fetch('/pedidos', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    if (res.status === 419) {
                        this.errorMensaje = 'Tu sesión ha expirado por inactividad. Por favor, recarga la página completamente (F5) y vuelve a intentarlo.';
                        this.guardando = false;
                        return;
                    }

                    // Verificar si la respuesta es JSON antes de parsear (evita SyntaxError en HTML)
                    const isJson = res.headers.get('content-type')?.includes('application/json');
                    if (!isJson) {
                        const errText = await res.text();
                        console.error("Respuesta inesperada (no JSON):", errText.substring(0, 500));
                        this.errorMensaje = 'El servidor devolvió una respuesta no válida (HTML). Revisa la consola o asegúrate de que todos los datos estén completos.';
                        this.guardando = false;
                        return;
                    }

                    const rawText = await res.text();
                    let data;
                    try {
                        // Limpiar cualquier HTML/Warning de PHP que se cuele antes del JSON
                        const jsonStart = rawText.indexOf('{');
                        const cleanText = jsonStart >= 0 ? rawText.substring(jsonStart) : rawText;
                        data = JSON.parse(cleanText);
                    } catch (err) {
                        console.error("Error al parsear JSON. Texto original:", rawText);
                        this.errorMensaje = 'El servidor devolvió datos con formato incorrecto. Revisa la consola.';
                        this.guardando = false;
                        return;
                    }

                    if(res.ok && data.success) {
                        if (data.ticket_url) {
                            window.open(data.ticket_url, '_blank');
                        }
                        if (data.whatsapp_url) {
                            window.open(data.whatsapp_url, '_blank');
                        }
                        window.location.reload();
                    } else {
                        this.errorMensaje = data.message || 'Error al guardar el pedido. Verifica los datos.';
                    }
                } catch(e) {
                    console.error(e);
                    this.errorMensaje = 'Error de red. Verifica la consola para más detalles.';
                } finally {
                    this.guardando = false;
                }
            },
            enviarWhatsApp(pedido) {
                if (!pedido.cliente || !pedido.cliente.telefono) {
                    alert("El cliente no tiene un teléfono registrado.");
                    return;
                }
                
                // Buscar la plantilla que coincida con el evento de estado actual, si no, buscar "Pedido Creado" por defecto
                let plantilla = plantillasConfig.find(p => p.evento === pedido.estado);
                if(!plantilla) {
                    // Fallback
                    plantilla = plantillasConfig.find(p => p.evento === 'Pedido Creado');
                }

                if(!plantilla) {
                    alert("No hay plantilla configurada para este estado ni una por defecto activa.");
                    return;
                }

                let mensaje = plantilla.contenido;
                
                // Reemplazar variables dinámicas
                mensaje = mensaje.replace(/{cliente}/g, pedido.cliente.nombre);
                mensaje = mensaje.replace(/{orden}/g, pedido.numero_orden);
                mensaje = mensaje.replace(/{fecha_entrega}/g, pedido.fecha_estimada_entrega ? pedido.fecha_estimada_entrega.substring(0,10) : 'Pendiente');
                mensaje = mensaje.replace(/{total}/g, Number(pedido.total_pedido).toFixed(2));
                mensaje = mensaje.replace(/{abonado}/g, Number(pedido.total_abonado).toFixed(2));
                mensaje = mensaje.replace(/{saldo}/g, Number(pedido.saldo_pendiente).toFixed(2));
                mensaje = mensaje.replace(/{empresa}/g, 'SAFA DIGITAL');

                const telefono = pedido.cliente.telefono.replace(/[^0-9]/g, '');
                window.open(`https://wa.me/${telefono}?text=${encodeURIComponent(mensaje)}`, '_blank');
            }
        }
    }
</script>
@endpush
