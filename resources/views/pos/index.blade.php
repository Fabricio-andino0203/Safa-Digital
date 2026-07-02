@extends('layouts.pos')

@section('pos_header_actions')
<div x-data x-show="$store.pos.sesionActiva" x-cloak class="flex items-center gap-3">
    <!-- Botón para Cobrar Pedido (Event dispatch al x-data de posApp) -->
    <button @click="$dispatch('abrir-cobro-pedido')"
            class="px-4 py-1.5 text-xs font-bold bg-neutral-900 text-white rounded-xl hover:bg-neutral-800 transition-all shadow-sm">
        Cobrar Pedido
    </button>
    
    <div class="flex items-center gap-2 text-sm ml-2 border-l border-neutral-200 pl-4">
        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
        <span class="font-medium text-neutral-700">Caja Abierta</span>
    </div>
    <button @click="$dispatch('abrir-corte-caja')"
       class="px-3.5 py-1.5 text-xs font-semibold border border-neutral-200 rounded-xl text-neutral-600 hover:bg-neutral-50 transition-all">
        Cierre / Corte de Caja
    </button>
</div>
@endsection

@section('pos_content')
{{-- ══════════════════════════════════════════════════════════════════
     RAÍZ ÚNICA de Alpine — todo el POS en un solo x-data
══════════════════════════════════════════════════════════════════ --}}
<div x-data="posApp()" x-init="init()" class="h-full flex relative">

    {{-- ══════════════════════════════════════════════════════════════
         MODAL: APERTURA DE CAJA
    ══════════════════════════════════════════════════════════════ --}}
    <div x-show="!sesionActiva"
         x-transition.opacity
         class="absolute inset-0 z-50 bg-white/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md border border-neutral-100"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="p-8 pb-0 text-center">
                <div class="w-16 h-16 bg-neutral-900 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-neutral-900">Abrir Caja</h2>
                <p class="text-sm text-neutral-500 mt-2">Ingresa el monto físico inicial para comenzar el turno.</p>
            </div>
            <div class="p-8 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">Monto Inicial en Caja ($)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 font-medium"> L.</span>
                        <input id="inp-monto-inicial" type="number" x-model.number="montoInicial" min="0" step="0.01"
                               placeholder="0.00" @keydown.enter="abrirCaja()"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 pl-8 text-center text-2xl font-bold"/>
                    </div>
                </div>
                <div x-show="errorApertura" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorApertura"></div>
                <button @click="abrirCaja()" :disabled="cargandoApertura"
                        class="w-full py-4 bg-neutral-900 text-white font-bold rounded-2xl hover:bg-neutral-800 active:scale-[0.98] transition-all shadow-sm disabled:opacity-60 flex items-center justify-center gap-2">
                    <svg x-show="cargandoApertura" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="cargandoApertura ? 'Abriendo...' : '🔓 Abrir Caja y Comenzar'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         MODAL: SELECTOR DE VARIANTE
         Se abre cuando el usuario clica un producto con múltiples variantes
    ══════════════════════════════════════════════════════════════ --}}
    <div x-show="modalVariante" x-cloak
         class="absolute inset-0 z-40 bg-neutral-900/30 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="modalVariante = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md border border-neutral-100"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-neutral-900" x-text="productoSeleccionado?.nombre"></h3>
                    <p class="text-xs text-neutral-400 mt-0.5">Selecciona la variante que deseas agregar al carrito</p>
                </div>
                <button @click="modalVariante = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-2 max-h-96 overflow-y-auto">
                <template x-for="variante in productoSeleccionado?.variantes ?? []" :key="variante.id">
                    <button @click="agregarVariante(variante); modalVariante = false"
                            :disabled="variante.stock_disponible <= 0"
                            class="w-full flex items-center gap-4 px-4 py-3.5 border border-neutral-100 rounded-2xl hover:border-neutral-900 hover:bg-neutral-50 transition-all text-left disabled:opacity-40 disabled:cursor-not-allowed group">
                        
                        <img :src="variante.imagen || productoSeleccionado?.imagen || 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%23d4d4d4\' stroke-width=\'1.5\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z\'/%3E%3C/svg%3E'"
                             class="w-12 h-12 object-cover rounded-xl bg-neutral-50 border border-neutral-100 flex-shrink-0" alt="Variante">

                        <div class="flex-1 min-w-0">
                            <p class="font-mono text-xs text-neutral-400" x-text="variante.sku"></p>
                            <div class="flex gap-1 flex-wrap mt-1">
                                <template x-if="variante.atributos && Object.keys(variante.atributos).length > 0">
                                    <div class="flex gap-1">
                                        <template x-for="[k, v] in Object.entries(variante.atributos)" :key="k">
                                            <span class="text-sm font-medium text-neutral-700"
                                                  x-text="v"></span>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!variante.atributos || Object.keys(variante.atributos).length === 0">
                                    <span class="text-sm text-neutral-700">Sin atributos</span>
                                </template>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-4">
                            <p class="text-base font-bold text-neutral-900" x-text="'L.' + Number(variante.precio).toFixed(2)"></p>
                            <p class="text-xs" :class="variante.stock_disponible > 0 ? 'text-neutral-400' : 'text-red-500'"
                               x-text="variante.stock_disponible > 0 ? variante.stock_disponible + ' disp.' : 'Sin stock'"></p>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         PANEL IZQUIERDO — Productos
    ══════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden border-r border-neutral-100 bg-[#FAFAFA]">

        {{-- Buscador --}}
        <div class="px-6 py-4 bg-white border-b border-neutral-100 flex-shrink-0">
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" x-model="busqueda" @input.debounce.200ms="filtrar()"
                       placeholder="Buscar producto o SKU... (F2)"
                       class="w-full pl-12 pr-10 py-2.5 rounded-lg border border-gray-200 bg-gray-50/50 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                <button x-show="busqueda" @click="busqueda=''; filtrar()" class="absolute right-4 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Grid productos --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div x-show="filtrados.length === 0" class="flex flex-col items-center justify-center h-48 text-neutral-300">
                <svg class="w-14 h-14 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-sm font-medium">Sin productos disponibles</p>
                <p class="text-xs mt-1">Agrega productos en el módulo de Inventario.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <template x-for="p in filtrados" :key="p.id">
                    <button @click="clickProducto(p)"
                            :disabled="p.variantes.length === 0"
                            class="group flex flex-col relative bg-white border border-neutral-200 rounded-xl text-left hover:border-neutral-300 hover:shadow-sm transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-neutral-900 focus:ring-offset-1 active:scale-95 overflow-hidden">

                        {{-- Badge múltiples variantes --}}
                        <div x-show="p.variantes.length > 1"
                             class="absolute top-2.5 right-2.5 z-10 bg-neutral-900/90 backdrop-blur text-white text-xs font-bold px-2 py-1 rounded-md flex items-center justify-center shadow-sm"
                             x-text="p.variantes.length + ' var.'"></div>

                        {{-- Contenedor de Imagen Superior --}}
                        <div class="aspect-square w-full bg-[#FAFAFA] border-b border-neutral-100 overflow-hidden flex items-center justify-center">
                            <img :src="p.imagen || 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%23d4d4d4\' stroke-width=\'1.5\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z\'/%3E%3C/svg%3E'"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 alt="Producto">
                        </div>

                        {{-- Información Inferior --}}
                        <div class="p-4 w-full flex flex-col flex-1">
                            <p class="text-[11px] font-medium text-neutral-400 uppercase tracking-wider truncate mb-1" x-text="p.categoria?.nombre || 'Sin categoría'"></p>
                            <p class="text-sm font-bold text-neutral-900 leading-tight line-clamp-2 mb-3" x-text="p.nombre"></p>

                            <div class="mt-auto flex items-end justify-between">
                                <div>
                                    <p class="text-[10px] text-neutral-400 uppercase tracking-wider mb-0.5">Precio desde</p>
                                    <span class="text-base font-black text-neutral-900"
                                          x-text="p.variantes.length > 0 ? 'L.' + Math.min(...p.variantes.map(v => v.precio)).toFixed(2) : '—'"></span>
                                </div>
                                <span class="text-[11px] font-semibold text-neutral-500 bg-neutral-100 border border-neutral-200 px-2 py-1 rounded-md"
                                      x-text="p.variantes.reduce((s,v) => s + v.stock_disponible, 0) + ' disp.'"></span>
                            </div>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         PANEL DERECHO — Carrito
    ══════════════════════════════════════════════════════════════ --}}
    <div class="w-96 flex-shrink-0 bg-white flex flex-col overflow-hidden">

        <div class="px-6 py-4 border-b border-neutral-100 flex items-center justify-between flex-shrink-0">
            <div>
                <h2 class="text-base font-bold text-neutral-900">Ticket de Venta</h2>
                <p class="text-xs text-neutral-400 mt-0.5" x-text="carrito.length + ' ítem(s)'"></p>
            </div>
            <button x-show="carrito.length > 0" @click="carrito=[]; descuento=0"
                    class="text-xs text-neutral-400 hover:text-red-500 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Limpiar
            </button>
        </div>

        {{-- Items --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
            <div x-show="carrito.length === 0" class="flex flex-col items-center justify-center h-48 text-neutral-300">
                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm text-neutral-400">Carrito vacío</p>
            </div>

            <template x-for="(item, i) in carrito" :key="item.varianteId">
                <div class="flex items-center gap-3 bg-neutral-50 rounded-xl p-3">
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button @click="dec(i)" class="w-6 h-6 rounded-lg bg-white border border-neutral-200 flex items-center justify-center text-neutral-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-all text-xs font-bold">−</button>
                        <span class="w-7 text-center text-sm font-bold" x-text="item.qty"></span>
                        <button @click="inc(i)" :disabled="item.qty >= item.stockDisponible" class="w-6 h-6 rounded-lg bg-white border border-neutral-200 flex items-center justify-center text-neutral-500 hover:bg-green-50 hover:border-green-200 hover:text-green-600 transition-all text-xs font-bold disabled:opacity-30">+</button>
                    </div>
                    
                    <img :src="item.imagen || 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%23d4d4d4\' stroke-width=\'1.5\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z\'/%3E%3C/svg%3E'"
                         class="w-10 h-10 object-cover rounded-lg bg-white border border-neutral-200 flex-shrink-0" alt="Item">

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-neutral-900 truncate" x-text="item.nombre"></p>
                        <p class="text-xs text-neutral-400 font-mono" x-text="item.sku"></p>
                        <p class="text-xs text-neutral-400" x-text="'L.' + Number(item.precio).toFixed(2) + ' c/u'"></p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-bold text-neutral-900" x-text="'L.' + (item.precio * item.qty).toFixed(2)"></p>
                        <button @click="carrito.splice(i,1)" class="text-xs text-neutral-300 hover:text-red-500 transition-colors">quitar</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Totales --}}
        <div class="px-5 py-4 border-t border-neutral-100 space-y-3 bg-[#FAFAFA] flex-shrink-0">
            <!-- Selector de Cliente (POS) -->
            <div class="border-b border-neutral-200 pb-3 mb-1">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-400">Cliente</label>
                    <button type="button" @click="openQuickClient = true" class="text-xs text-blue-600 hover:text-blue-800 hover:underline font-bold transition-all">
                        + Nuevo Cliente
                    </button>
                </div>
                
                <!-- Si es una liquidación de pedido, mostrar en texto plano deshabilitado -->
                <div x-show="pedidoEncontrado" class="bg-neutral-100 border border-neutral-200 rounded-xl px-3 py-2 text-sm text-neutral-700 font-bold" style="display: none;">
                    Pedido: <span x-text="pedidoEncontrado ? pedidoEncontrado.cliente : ''"></span>
                </div>
                
                <!-- Venta rápida normal -->
                <div x-show="!pedidoEncontrado">
                    <select x-model="clienteId" class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer">
                        <option value="">CONSUMIDOR FINAL</option>
                        <template x-for="c in clientes" :key="c.id">
                            <option :value="c.id" x-text="c.nombre"></option>
                        </template>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center justify-between gap-3">
                <label class="text-sm text-neutral-500 font-medium flex-shrink-0">Descuento ($)</label>
                <input type="number" x-model.number="descuento" min="0" step="0.01"
                       class="w-28 text-right rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-neutral-500">Subtotal</span>
                <span class="font-medium" x-text="'L.' + subtotal.toFixed(2)"></span>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-neutral-200">
                <span class="text-base font-bold">Total</span>
                <span class="text-2xl font-bold" x-text="'L.' + total.toFixed(2)"></span>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="px-5 pb-5 pt-3 flex flex-col gap-2 flex-shrink-0">
            <button @click="openCobro = true" :disabled="carrito.length === 0"
                    class="w-full py-3.5 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 active:scale-[0.98] transition-all shadow-sm disabled:opacity-30 text-sm flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Cobrar — <span x-text="'L.' + total.toFixed(2)"></span>
            </button>
            <div class="grid grid-cols-2 gap-2">
                <button @click="whatsapp()" :disabled="carrito.length === 0"
                        class="py-2.5 border border-neutral-200 text-neutral-600 text-xs font-medium rounded-xl hover:bg-neutral-50 transition-colors disabled:opacity-30 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </button>
                <button @click="carrito=[]; descuento=0" :disabled="carrito.length === 0"
                        class="py-2.5 border border-neutral-200 text-neutral-600 text-xs font-medium rounded-xl hover:bg-neutral-50 transition-colors disabled:opacity-30 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         MODAL: COBRO
    ══════════════════════════════════════════════════════════════ --}}
    <div x-show="openCobro" x-cloak
         class="absolute inset-0 z-40 bg-neutral-900/30 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="openCobro = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg border border-neutral-100"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between bg-[#FAFAFA] rounded-t-3xl">
                <h3 class="text-lg font-bold">Procesar Cobro</h3>
                <button @click="openCobro = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-7 space-y-5">
                <div class="bg-neutral-900 rounded-2xl px-6 py-5 text-center">
                    <p class="text-sm text-neutral-400 font-medium">Total a Cobrar</p>
                    <p class="text-4xl font-bold text-white mt-1" x-text="'L.' + total.toFixed(2)"></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-3">Método de Pago</label>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="m in metodos" :key="m.v">
                            <button @click="metodoPago = m.v"
                                    :class="metodoPago === m.v ? 'border-neutral-900 bg-neutral-900 text-white' : 'border-neutral-200 bg-white text-neutral-600 hover:border-neutral-300'"
                                    class="flex items-center gap-2.5 px-4 py-3 border-2 rounded-xl text-sm font-medium transition-all">
                                <span x-text="m.icon" class="text-lg"></span>
                                <span x-text="m.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="metodoPago === 'efectivo'" x-transition class="bg-neutral-50 rounded-2xl p-5 border border-neutral-100 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">Monto Entregado ($)</label>
                        <input type="number" x-model.number="montoEntregado" min="0" step="0.01" @focus="$event.target.select()"
                               class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 text-center text-xl font-bold" placeholder="0.00"/>
                    </div>
                    <div class="pt-3 border-t border-neutral-200 flex items-center justify-between">
                        <span class="text-sm font-semibold text-neutral-700">Cambio a devolver</span>
                        <span class="text-2xl font-bold" :class="cambio < 0 ? 'text-red-600' : 'text-green-600'"
                              x-text="'L.' + Math.max(0, cambio).toFixed(2)"></span>
                    </div>
                    <p x-show="montoEntregado > 0 && cambio < 0" class="text-xs text-red-500 font-medium">⚠️ El monto es menor al total.</p>
                </div>

                <div x-show="errorCobro" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorCobro"></div>

                <button @click="cobrar()"
                        :disabled="cargandoCobro || (metodoPago === 'efectivo' && montoEntregado > 0 && cambio < 0)"
                        class="w-full py-4 bg-neutral-900 text-white font-bold rounded-2xl hover:bg-neutral-800 active:scale-[0.98] transition-all disabled:opacity-40 flex items-center justify-center gap-2">
                    <svg x-show="cargandoCobro" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="cargandoCobro ? 'Procesando...' : '✓ Confirmar Venta'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         MODAL: COBRO DE PEDIDO (INTEGRACIÓN)
    ══════════════════════════════════════════════════════════════ --}}
    <div x-show="modalCobroPedido" x-cloak
         class="absolute inset-0 z-50 bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl border border-neutral-100 flex flex-col max-h-full"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-5 border-b border-neutral-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-neutral-900">Liquidar Pedido</h3>
                    <p class="text-sm text-neutral-500">Busca el pedido y registra su pago final.</p>
                </div>
                <button @click="modalCobroPedido = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-6">
                <!-- Buscador rápido por número -->
                <div class="flex gap-3">
                    <input type="text" x-model="busquedaPedidoTerm" @keydown.enter="buscarPedidoAction(busquedaPedidoTerm)" placeholder="Teclea ORD-00000X" class="flex-1 rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 font-mono uppercase">
                    <button @click="buscarPedidoAction(busquedaPedidoTerm)" :disabled="cargandoBusquedaPedido || !busquedaPedidoTerm" class="px-6 py-3 bg-neutral-900 text-white font-bold rounded-xl hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!cargandoBusquedaPedido">Buscar</span>
                        <span x-show="cargandoBusquedaPedido">...</span>
                    </button>
                </div>
                <p x-show="errorBusquedaPedido" class="text-sm text-red-500 font-medium mt-1" x-text="errorBusquedaPedido"></p>

                <!-- Lista de Pedidos Pendientes -->
                <div x-show="!pedidoEncontrado" class="space-y-3">
                    <h4 class="text-sm font-bold text-neutral-900 mb-2">Órdenes Pendientes Recientes</h4>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-2">
                        @foreach($pedidosPendientes as $pend)
                            <div class="flex items-center justify-between p-4 rounded-xl border border-neutral-100 bg-[#FAFAFA] hover:border-neutral-200 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-neutral-900 bg-white px-2 py-1 rounded-md shadow-sm border border-neutral-100">{{ $pend['numero_orden'] }}</span>
                                        <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded-md border border-red-100">Saldo: L. {{ number_format($pend['saldo_pendiente'], 2) }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-neutral-700">{{ $pend['cliente'] }}</p>
                                </div>
                                <button @click="buscarPedidoAction('{{ $pend['numero_orden'] }}')" class="px-4 py-2 bg-white border border-neutral-200 text-neutral-900 text-xs font-bold rounded-lg hover:bg-neutral-50 active:scale-95 transition-all shadow-sm">
                                    Cobrar / Abonar
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Resultado del Pedido (Para liquidar o abonar) -->
                <div x-show="pedidoEncontrado" class="space-y-6">
                    <div class="bg-[#FAFAFA] rounded-2xl p-5 border border-neutral-200">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-bold text-neutral-900 bg-neutral-200 px-2 py-1 rounded-md" x-text="pedidoEncontrado?.numero_orden"></span>
                                <h4 class="text-lg font-bold text-neutral-900 mt-2" x-text="pedidoEncontrado?.cliente?.nombre"></h4>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-neutral-500">Total Pedido</p>
                                <p class="text-xl font-bold text-neutral-900" x-text="'L.' + Number(pedidoEncontrado?.total_pedido || 0).toFixed(2)"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-200">
                            <div>
                                <p class="text-xs text-neutral-500">Total Abonado</p>
                                <p class="text-sm font-bold text-green-600" x-text="'L.' + Number(pedidoEncontrado?.total_abonado || 0).toFixed(2)"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-neutral-500 uppercase tracking-wider font-bold">Saldo Pendiente</p>
                                <p class="text-2xl font-black text-red-500" x-text="'L.' + Number(pedidoEncontrado?.saldo_pendiente || 0).toFixed(2)"></p>
                            </div>
                        </div>
                    </div>

                    <div x-show="pedidoEncontrado?.saldo_pendiente > 0">
                        <label class="block text-sm font-semibold text-neutral-700 mb-3">Método de Pago</label>
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <template x-for="m in metodos" :key="m.v">
                                <button @click="metodoPago = m.v"
                                        :class="metodoPago === m.v ? 'border-neutral-900 bg-neutral-900 text-white' : 'border-neutral-200 bg-white text-neutral-600 hover:border-neutral-300'"
                                        class="flex items-center gap-2.5 px-4 py-3 border-2 rounded-xl text-sm font-medium transition-all">
                                    <span x-text="m.icon" class="text-lg"></span>
                                    <span x-text="m.label"></span>
                                </button>
                            </template>
                        </div>
                        
                        <div x-show="metodoPago === 'efectivo'" class="bg-neutral-50 rounded-2xl p-4 border border-neutral-100">
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Monto Entregado / Abono ($)</label>
                            <input type="number" x-model.number="montoEntregado" min="0" step="0.01" 
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 text-center text-xl font-bold"/>
                            
                            <div class="mt-3 flex justify-between items-center text-sm font-bold">
                                <span>Restante tras el abono:</span>
                                <span :class="(pedidoEncontrado.saldo_pendiente - montoEntregado) > 0 ? 'text-orange-500' : 'text-green-600'"
                                      x-text="'L.' + Math.max(0, pedidoEncontrado.saldo_pendiente - montoEntregado).toFixed(2)"></span>
                            </div>
                            <div class="mt-1 flex justify-between items-center text-xs font-bold text-neutral-500" x-show="(montoEntregado - pedidoEncontrado.saldo_pendiente) > 0">
                                <span>Cambio a devolver:</span>
                                <span class="text-green-600" x-text="'L.' + (montoEntregado - pedidoEncontrado.saldo_pendiente).toFixed(2)"></span>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button @click="pedidoEncontrado = null; busquedaPedidoTerm = ''" class="px-6 py-4 bg-white border border-neutral-200 text-neutral-700 font-bold rounded-2xl hover:bg-neutral-50 transition-colors">Volver a Lista</button>
                            <button @click="pagarPedidoAction()" :disabled="cargandoPagoPedido || (metodoPago === 'efectivo' && montoEntregado <= 0)"
                                    class="flex-1 py-4 bg-neutral-900 text-white font-bold rounded-2xl hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-text="cargandoPagoPedido ? 'Procesando...' : (montoEntregado >= pedidoEncontrado?.saldo_pendiente ? 'Liquidar y Entregar' : 'Registrar Abono')"></span>
                            </button>
                        </div>
                    </div>

                    <div x-show="pedidoEncontrado?.saldo_pendiente <= 0" class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-200 text-center font-bold">
                        Este pedido ya no tiene saldo pendiente.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nuevo Cliente Rápido -->
    <div x-show="openQuickClient" class="relative z-50" x-cloak>
        <div x-show="openQuickClient" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="openQuickClient"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="openQuickClient = false"
                     class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100">
                    
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-neutral-900">Registrar Nuevo Cliente</h3>
                        <button @click="openQuickClient = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Nombre Completo *</label>
                            <input type="text" x-model="newClientName" placeholder="Nombre del cliente..."
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Teléfono</label>
                            <input type="text" x-model="newClientPhone" placeholder="Ej. 9999-9999"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Email</label>
                            <input type="email" x-model="newClientEmail" placeholder="correo@ejemplo.com"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div x-show="quickClientError" class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-2.5" x-text="quickClientError"></div>

                        <button @click="guardarQuickCliente()" :disabled="guardandoQuickClient"
                                class="w-full py-3 bg-neutral-900 hover:bg-neutral-800 text-white font-semibold rounded-2xl disabled:opacity-50 transition-all flex items-center justify-center gap-2 mt-2">
                            <svg x-show="guardandoQuickClient" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="guardandoQuickClient ? 'Guardando...' : 'Crear y Seleccionar Cliente'"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Corte de Caja (Cierre / Corte) -->
    <div x-show="modalCorte" class="relative z-50" x-cloak>
        <div x-show="modalCorte" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalCorte"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="modalCorte = false"
                     class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                        <h3 class="text-lg font-bold text-neutral-900">Cierre / Corte de Caja</h3>
                        <button @click="modalCorte = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-neutral-50 rounded-2xl p-4 border border-neutral-200">
                            <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">Dinero Esperado en Caja</span>
                            <span class="text-2xl font-black text-neutral-900 mt-1" x-text="'L. ' + Number(dineroEsperado).toFixed(2)"></span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Efectivo Real en Caja (Arqueo) *</label>
                            <input type="number" step="0.01" x-model.number="efectivoReal" min="0" placeholder="0.00"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Monto a Retirar hacia Tesorería *</label>
                            <input type="number" step="0.01" x-model.number="montoARetirar" min="0" placeholder="0.00"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"/>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Notas del Corte</label>
                            <textarea x-model="notasCorte" rows="2" placeholder="Observaciones adicionales..."
                                      class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"></textarea>
                        </div>

                        <div x-show="efectivoReal > 0" class="text-sm font-bold p-3 rounded-xl flex justify-between items-center bg-neutral-100 border border-neutral-200 text-neutral-800">
                            <span>Fondo que queda en caja:</span>
                            <span class="text-lg font-black" x-text="'L. ' + Number(Math.max(0, efectivoReal - montoARetirar)).toFixed(2)"></span>
                        </div>

                        <div x-show="errorCorte" class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-2.5" x-text="errorCorte"></div>

                        <button @click="procesarCorteCaja()" :disabled="cargandoCorte || efectivoReal <= 0"
                                class="w-full py-3.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-2xl disabled:opacity-50 transition-all flex items-center justify-center gap-2 mt-2">
                            <svg x-show="cargandoCorte" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>Procesar Corte y Cierre</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Selección de Extras -->
    <div x-show="modalExtras" x-cloak
         class="fixed inset-0 z-[60] bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="modalExtras = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm border border-neutral-100"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-neutral-900">Adicionales / Extras</h3>
                    <p class="text-xs text-neutral-400 mt-0.5" x-text="varianteParaExtras?.sku"></p>
                </div>
                <button @click="modalExtras = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-7 space-y-4">
                <p class="text-xs text-neutral-500">Selecciona los extras que deseas agregar a este producto:</p>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <template x-for="extra in (productoParaExtras?.extras || [])" :key="extra.id">
                        <label class="flex items-center justify-between p-3 bg-neutral-50 hover:bg-neutral-100/75 rounded-xl cursor-pointer transition-colors border border-neutral-100">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" :value="extra" x-model="extrasSeleccionados"
                                       class="rounded text-neutral-900 focus:ring-neutral-900 border-neutral-300 w-4 h-4"/>
                                <span class="text-xs font-semibold text-neutral-700" x-text="extra.nombre"></span>
                            </div>
                            <span class="text-xs font-bold text-neutral-900" x-text="'+L. ' + Number(extra.precio).toFixed(2)"></span>
                        </label>
                    </template>
                </div>

                <button @click="confirmarExtrasYAgregar()"
                        class="w-full py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 transition-all text-xs">
                    Confirmar y Agregar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('pos_scripts')
<script>
// ─── Store Alpine para sincronizar header ────────────────────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.store('pos', { sesionActiva: {{ $sesion ? 'true' : 'false' }} });
});

// ─── Componente principal del POS ────────────────────────────────────────────
function posApp() {
    return {
        // Sesión
        sesionActiva:    {{ $sesion ? 'true' : 'false' }},
        sesionId:        {{ $sesion ? $sesion->id : 'null' }},
        montoInicial:    0,
        cargandoApertura: false,
        errorApertura:   '',
        clientes:        @json($clientes),
        clienteId:       '',
        openQuickClient: false,
        newClientName:   '',
        newClientPhone:  '',
        newClientEmail:  '',
        quickClientError: '',
        guardandoQuickClient: false,

        // Corte de Caja
        modalCorte: false,
        dineroEsperado: @js($dineroEsperado),
        efectivoReal: 0,
        montoARetirar: 0,
        notasCorte: '',
        cargandoCorte: false,
        errorCorte: '',

        // Catálogo (productos con variantes)
        todos:    @json($productos),
        filtrados: [],
        busqueda: '',

        // Modal selector de variante
        modalVariante:       false,
        productoSeleccionado: null,

        // Modal selector de extras
        modalExtras:         false,
        varianteParaExtras:  null,
        productoParaExtras:  null,
        extrasSeleccionados: [],

        // Carrito — items: { varianteId, productoId, nombre, sku, precio, stockDisponible, qty }
        carrito:  [],
        descuento: 0,

        // Cobro
        openCobro:      false,
        metodoPago:     'efectivo',
        montoEntregado: 0,
        metodos: [
            { v: 'efectivo',      label: 'Efectivo',      icon: '💵' },
            { v: 'tarjeta',       label: 'Tarjeta',       icon: '💳' },
            { v: 'transferencia', label: 'Transferencia', icon: '🏦' },
            { v: 'mixto',         label: 'Mixto',         icon: '🔀' },
        ],
        errorCobro:   '',
        cargandoCobro: false,

        // ── Cobro de Pedidos (Integración) ──
        modalCobroPedido: false,
        busquedaPedidoTerm: '',
        pedidoEncontrado: null,
        errorBusquedaPedido: '',
        cargandoBusquedaPedido: false,
        cargandoPagoPedido: false,

        // Éxito
        ventaExitosa: false,
        cambioFinal:  0,

        // Getters
        get subtotal() { return this.carrito.reduce((s, i) => s + i.precio * i.qty, 0); },
        get total()    { return Math.max(0, this.subtotal - this.descuento); },
        get cambio()   { return this.montoEntregado - this.total; },

        // ── Init ─────────────────────────────────────────────────────────────
        init() {
            this.filtrados = this.todos;
            window.addEventListener('keydown', e => {
                if (e.key === 'F2')     { e.preventDefault(); document.querySelector('[x-model="busqueda"]')?.focus(); }
                if (e.key === 'Escape') { this.openCobro = false; this.modalVariante = false; this.modalCobroPedido = false; }
            });
            window.addEventListener('abrir-cobro-pedido', () => {
                this.modalCobroPedido = true;
                this.pedidoEncontrado = null;
                this.busquedaPedidoTerm = '';
                this.errorBusquedaPedido = '';
                this.metodoPago = 'efectivo';
                this.montoEntregado = 0;
                setTimeout(() => document.getElementById('inp-buscar-pedido')?.focus(), 100);
            });
            window.addEventListener('abrir-corte-caja', () => {
                this.modalCorte = true;
                this.efectivoReal = 0;
                this.montoARetirar = 0;
                this.notasCorte = '';
                this.errorCorte = '';
            });
        },

        // ── Productos / Filtrar ──────────────────────────────────────────────
        filtrar() {
            const q = this.busqueda.toLowerCase().trim();
            this.filtrados = q
                ? this.todos.filter(p =>
                    p.nombre.toLowerCase().includes(q) ||
                    p.variantes.some(v => v.sku.toLowerCase().includes(q))
                  )
                : this.todos;
        },

        /**
         * Click en una tarjeta de producto:
         *   - Si tiene 1 sola variante → agregar directo al carrito
         *   - Si tiene múltiples       → abrir el selector de variante
         */
        clickProducto(producto) {
            if (producto.variantes.length === 0) return;
            if (producto.variantes.length === 1) {
                this.agregarVariante(producto.variantes[0], producto.imagen);
            } else {
                this.productoSeleccionado = producto;
                this.modalVariante = true;
            }
        },

        // ── Carrito ──────────────────────────────────────────────────────────
        agregarVariante(variante, productoImagen = null) {
            // Buscar el producto padre para ver si tiene extras
            const producto = this.todos.find(p => p.id === (variante.producto_id || this.productoSeleccionado?.id));
            if (producto && producto.extras && producto.extras.length > 0) {
                this.varianteParaExtras = variante;
                this.productoParaExtras = producto;
                this.extrasSeleccionados = [];
                this.modalExtras = true;
            } else {
                const cartLineKey = variante.id.toString();
                const ex = this.carrito.find(i => i.cartLineKey === cartLineKey);
                if (ex) {
                    if (ex.qty < ex.stockDisponible) ex.qty++;
                } else {
                    this.carrito.push({
                        cartLineKey:    cartLineKey,
                        varianteId:     variante.id,
                        nombre:         variante.nombre_completo,
                        sku:            variante.sku,
                        precio:         parseFloat(variante.precio),
                        stockDisponible: variante.stock_disponible,
                        imagen:         variante.imagen || productoImagen || this.productoSeleccionado?.imagen,
                        qty:            1,
                        extras:         [],
                    });
                }
                this.modalVariante = false;
            }
        },

        confirmarExtrasYAgregar() {
            const basePrecio = parseFloat(this.varianteParaExtras.precio);
            const extrasPrecioTotal = this.extrasSeleccionados.reduce((s, e) => s + parseFloat(e.precio), 0);
            const precioFinal = basePrecio + extrasPrecioTotal;
            
            const extrasIds = this.extrasSeleccionados.map(e => e.id).sort().join('-');
            const cartLineKey = this.varianteParaExtras.id + (extrasIds ? '-' + extrasIds : '');

            const ex = this.carrito.find(i => i.cartLineKey === cartLineKey);
            if (ex) {
                if (ex.qty < ex.stockDisponible) ex.qty++;
            } else {
                this.carrito.push({
                    cartLineKey:    cartLineKey,
                    varianteId:     this.varianteParaExtras.id,
                    nombre:         this.varianteParaExtras.nombre_completo + (this.extrasSeleccionados.length > 0 ? ' (' + this.extrasSeleccionados.map(e => e.nombre).join(', ') + ')' : ''),
                    sku:            this.varianteParaExtras.sku,
                    precio:         precioFinal,
                    stockDisponible: this.varianteParaExtras.stock_disponible,
                    imagen:         this.varianteParaExtras.imagen || this.productoParaExtras?.imagen,
                    qty:            1,
                    extras:         JSON.parse(JSON.stringify(this.extrasSeleccionados)),
                });
            }

            this.modalExtras = false;
            this.modalVariante = false;
        },

        inc(i) { if (this.carrito[i].qty < this.carrito[i].stockDisponible) this.carrito[i].qty++; },
        dec(i) { this.carrito[i].qty <= 1 ? this.carrito.splice(i, 1) : this.carrito[i].qty--; },

        // ── Cobro ─────────────────────────────────────────────────────────────
        async cobrar() {
            if (this.cargandoCobro) return;
            this.errorCobro   = '';
            this.cargandoCobro = true;

            try {
                const res = await fetch('{{ route('pos.procesarVenta') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        caja_sesion_id:  this.sesionId,
                        carrito:         this.carrito.map(i => ({ id: i.varianteId, qty: i.qty, extras: i.extras })),
                        descuento:       this.descuento || 0,
                        metodo_pago:     this.metodoPago,
                        monto_entregado: this.metodoPago === 'efectivo' ? this.montoEntregado : null,
                        cliente_id:      this.clienteId || null,
                    })
                });
                const data = await res.json();

                if (data.success) {
                    this.openCobro  = false;
                    this.cambioFinal = data.cambio ?? 0;
                    this.ventaExitosa = true;
                    if (data.ticket_url) {
                        window.open(data.ticket_url, '_blank');
                    }

                    // Actualizar stock local en el catálogo
                    this.carrito.forEach(item => {
                        for (const prod of this.todos) {
                            const v = prod.variantes.find(v => v.id === item.varianteId);
                            if (v) { v.stock_disponible = Math.max(0, v.stock_disponible - item.qty); break; }
                        }
                    });
                    this.filtrar();
                } else {
                    this.errorCobro = data.message || 'Error al procesar la venta.';
                }
            } catch (e) {
                this.errorCobro = 'Error de conexión. Verifica el servidor.';
            } finally {
                this.cargandoCobro = false;
            }
        },

        nuevaVenta() {
            this.ventaExitosa  = false;
            this.carrito       = [];
            this.descuento     = 0;
            this.montoEntregado = 0;
            this.cambioFinal   = 0;
            this.clienteId     = '';
        },

        // ── Cobro de Pedidos (Integración) ────────────────────────────────────
        async buscarPedidoAction(term = null) {
            const searchQuery = term || this.busquedaPedidoTerm;
            if (!searchQuery) return;
            
            this.cargandoBusquedaPedido = true;
            this.errorBusquedaPedido = '';
            this.pedidoEncontrado = null;

            try {
                const res = await fetch(`{{ route('pos.buscarPedido') }}?numero_orden=${searchQuery.toUpperCase()}`);
                const data = await res.json();
                
                if (res.ok && data.success) {
                    this.pedidoEncontrado = data.pedido;
                    this.busquedaPedidoTerm = searchQuery; // update input
                } else {
                    this.errorBusquedaPedido = data.message || 'Pedido no encontrado o ya pagado.';
                }
            } catch (e) {
                this.errorBusquedaPedido = 'Error de conexión. Verifica el servidor.';
            } finally {
                this.cargandoBusquedaPedido = false;
            }
        },

        async pagarPedidoAction() {
            if (!this.pedidoEncontrado || this.cargandoPagoPedido) return;
            this.cargandoPagoPedido = true;

            try {
                const res = await fetch('{{ route('pos.pagarPedido') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        caja_sesion_id: this.sesionId,
                        pedido_id: this.pedidoEncontrado.id,
                        monto_entregado: this.metodoPago === 'efectivo' ? this.montoEntregado : null,
                        metodo_pago: this.metodoPago
                    })
                });
                
                const data = await res.json();
                if (res.ok && data.success) {
                    if (data.ticket_url) {
                        window.open(data.ticket_url, '_blank');
                    }
                    alert('Pago de pedido registrado con éxito. Cambio: L.' + (data.cambio || 0).toFixed(2));
                    this.modalCobroPedido = false;
                    this.ventaExitosa = true; // Mostrar confetti o recargar
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al procesar el pago del pedido.');
                }
            } catch (e) {
                alert('Error de conexión al procesar el pago.');
            } finally {
                this.cargandoPagoPedido = false;
            }
        },

        // ── Apertura ──────────────────────────────────────────────────────────
        async abrirCaja() {
            if (this.cargandoApertura) return;
            this.errorApertura   = '';
            this.cargandoApertura = true;

            try {
                const res = await fetch('{{ route('pos.abrirSesion') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ monto_inicial: this.montoInicial })
                });
                const data = await res.json();

                if (data.success) {
                    this.sesionActiva = true;
                    this.sesionId     = data.sesion.id;
                    Alpine.store('pos').sesionActiva = true;
                } else {
                    this.errorApertura = data.message || 'Error al abrir la caja.';
                }
            } catch (e) {
                this.errorApertura = 'Error de conexión. Verifica el servidor.';
            } finally {
                this.cargandoApertura = false;
            }
        },

        // ── WhatsApp ──────────────────────────────────────────────────────────
        whatsapp() {
            const items = this.carrito.map(i => `- ${i.nombre} x${i.qty}: $${(i.precio * i.qty).toFixed(2)}`).join('\n');
            const texto = `*Detalle de Venta - Safa Digital*\n\n${items}\n\n*Total: $${this.total.toFixed(2)}*`;
            window.open('https://wa.me/?text=' + encodeURIComponent(texto), '_blank');
        },

        // ── Nuevo Cliente Rápido ──────────────────────────────────────────────
        async guardarQuickCliente() {
            if (!this.newClientName.trim()) {
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nombre: this.newClientName,
                        telefono: this.newClientPhone,
                        email: this.newClientEmail
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.clientes.push(data.cliente);
                    this.clienteId = data.cliente.id;
                    this.newClientName = '';
                    this.newClientPhone = '';
                    this.newClientEmail = '';
                    this.openQuickClient = false;
                } else {
                    this.quickClientError = data.message || 'Error al guardar el cliente.';
                }
            } catch (e) {
                this.quickClientError = 'Error de conexión con el servidor.';
            } finally {
                this.guardandoQuickClient = false;
            }
        },

        async procesarCorteCaja() {
            if (this.cargandoCorte) return;
            this.errorCorte = '';
            this.cargandoCorte = true;

            try {
                const res = await fetch('{{ route('pos.cerrarCaja') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        caja_sesion_id: this.sesionId,
                        monto_contado_fisico: this.efectivoReal,
                        monto_a_retirar: this.montoARetirar,
                        notas: this.notasCorte
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.modalCorte = false;
                    alert(data.message);
                    window.location.reload();
                } else {
                    this.errorCorte = data.message || 'Error al procesar el corte.';
                }
            } catch (e) {
                this.errorCorte = 'Error de conexión. Verifica el servidor.';
            } finally {
                this.cargandoCorte = false;
            }
        },
    };
}
</script>
@endpush
