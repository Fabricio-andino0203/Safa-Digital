@extends('layouts.pos')

@section('pos_header_actions')
<div x-data x-show="$store.pos.sesionActiva" x-cloak class="flex items-center gap-3">
    <div class="flex items-center gap-2 text-sm">
        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
        <span class="font-medium text-neutral-700">Caja Abierta</span>
    </div>
    <a href="{{ route('pos.corteCaja') }}"
       class="px-3.5 py-1.5 text-xs font-semibold border border-neutral-200 rounded-xl text-neutral-600 hover:bg-neutral-50 transition-all">
        Corte de Caja
    </a>
</div>
@endsection

@section('pos_content')
{{-- ══════════════════════════════════════════════════════════════════
     RAÍZ ÚNICA de Alpine — todo el POS en un solo x-data
══════════════════════════════════════════════════════════════════ --}}
<div x-data="posApp()" x-init="init()" class="h-full flex relative">

    {{-- ══════════════════════════════════════════════════════════════
         MODAL: APERTURA DE CAJA (bloquea el POS si no hay sesión)
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
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 font-medium">$</span>
                        <input id="inp-monto-inicial" type="number" x-model.number="montoInicial" min="0" step="0.01"
                               placeholder="0.00" @keydown.enter="abrirCaja()"
                               class="w-full pl-8 pr-4 py-4 border border-neutral-200 rounded-2xl text-2xl font-bold text-center text-neutral-900 focus:outline-none focus:border-neutral-400 bg-neutral-50 transition-colors"/>
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
         PANEL IZQUIERDO — 70% Productos
    ══════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden border-r border-neutral-100 bg-[#FAFAFA]">

        {{-- Buscador --}}
        <div class="px-6 py-4 bg-white border-b border-neutral-100 flex-shrink-0">
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" x-model="busqueda" @input.debounce.200ms="filtrar()"
                       placeholder="Buscar por nombre o código SKU... (F2)"
                       class="w-full pl-12 pr-10 py-3.5 bg-[#FAFAFA] border border-neutral-200 rounded-2xl text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-400 focus:bg-white transition-all"/>
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
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <template x-for="p in filtrados" :key="p.id">
                    <button @click="agregar(p)" :disabled="p.stock <= 0"
                            class="group relative bg-white border border-neutral-100 rounded-2xl p-4 text-left hover:border-neutral-300 hover:shadow-md transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-neutral-900 focus:ring-offset-1 active:scale-95">
                        <div x-show="p.stock > 0 && p.stock <= 5" class="absolute top-2.5 right-2.5 w-2 h-2 bg-amber-400 rounded-full"></div>
                        <div class="w-10 h-10 rounded-xl bg-neutral-100 group-hover:bg-neutral-900 flex items-center justify-center mb-3 transition-all">
                            <svg class="w-5 h-5 text-neutral-500 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-xs font-mono text-neutral-400 truncate" x-text="p.sku ?? '—'"></p>
                        <p class="text-sm font-semibold text-neutral-900 mt-0.5 leading-tight line-clamp-2" x-text="p.nombre"></p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-base font-bold text-neutral-900" x-text="'$' + Number(p.precio).toFixed(2)"></span>
                            <span class="text-xs text-neutral-400" x-text="p.stock + ' uds.'"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         PANEL DERECHO — 30% Carrito
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

        {{-- Items del carrito --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2">
            <div x-show="carrito.length === 0" class="flex flex-col items-center justify-center h-48 text-neutral-300">
                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm text-neutral-400">Carrito vacío</p>
            </div>

            <template x-for="(item, i) in carrito" :key="item.id">
                <div class="flex items-center gap-3 bg-neutral-50 rounded-xl p-3">
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button @click="dec(i)" class="w-6 h-6 rounded-lg bg-white border border-neutral-200 flex items-center justify-center text-neutral-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-all text-xs font-bold">−</button>
                        <span class="w-7 text-center text-sm font-bold" x-text="item.qty"></span>
                        <button @click="inc(i)" :disabled="item.qty >= item.stock" class="w-6 h-6 rounded-lg bg-white border border-neutral-200 flex items-center justify-center text-neutral-500 hover:bg-green-50 hover:border-green-200 hover:text-green-600 transition-all text-xs font-bold disabled:opacity-30">+</button>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-neutral-900 truncate" x-text="item.nombre"></p>
                        <p class="text-xs text-neutral-400" x-text="'$' + Number(item.precio).toFixed(2) + ' c/u'"></p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-bold text-neutral-900" x-text="'$' + (item.precio * item.qty).toFixed(2)"></p>
                        <button @click="carrito.splice(i,1)" class="text-xs text-neutral-300 hover:text-red-500 transition-colors">quitar</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Totales --}}
        <div class="px-5 py-4 border-t border-neutral-100 space-y-3 bg-[#FAFAFA] flex-shrink-0">
            <div class="flex items-center justify-between gap-3">
                <label class="text-sm text-neutral-500 font-medium flex-shrink-0">Descuento ($)</label>
                <input type="number" x-model.number="descuento" min="0" step="0.01"
                       class="w-28 text-right border border-neutral-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:border-neutral-400 bg-white transition-colors"/>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-neutral-500">Subtotal</span>
                <span class="font-medium" x-text="'$' + subtotal.toFixed(2)"></span>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-neutral-200">
                <span class="text-base font-bold">Total</span>
                <span class="text-2xl font-bold" x-text="'$' + total.toFixed(2)"></span>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="px-5 pb-5 pt-3 flex flex-col gap-2 flex-shrink-0">
            <button @click="openCobro = true" :disabled="carrito.length === 0"
                    class="w-full py-3.5 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 active:scale-[0.98] transition-all shadow-sm disabled:opacity-30 text-sm flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Cobrar — <span x-text="'$' + total.toFixed(2)"></span>
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
             x-transition:enter-end="opacity-100 translate-y-0"
             @click.outside="openCobro = false">

            <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between bg-[#FAFAFA] rounded-t-3xl">
                <h3 class="text-lg font-bold">Procesar Cobro</h3>
                <button @click="openCobro = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-7 space-y-5">
                <div class="bg-neutral-900 rounded-2xl px-6 py-5 text-center">
                    <p class="text-sm text-neutral-400 font-medium">Total a Cobrar</p>
                    <p class="text-4xl font-bold text-white mt-1" x-text="'$' + total.toFixed(2)"></p>
                </div>

                {{-- Métodos de pago --}}
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

                {{-- Calculadora de cambio (solo efectivo) --}}
                <div x-show="metodoPago === 'efectivo'" x-transition class="bg-neutral-50 rounded-2xl p-5 border border-neutral-100 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">Monto Entregado ($)</label>
                        <input type="number" x-model.number="montoEntregado" min="0" step="0.01" @focus="$event.target.select()"
                               class="w-full border border-neutral-200 rounded-xl px-4 py-3 text-xl font-bold text-center focus:outline-none focus:border-neutral-400 bg-white transition-colors" placeholder="0.00"/>
                    </div>
                    <div class="pt-3 border-t border-neutral-200 flex items-center justify-between">
                        <span class="text-sm font-semibold text-neutral-700">Cambio a devolver</span>
                        <span class="text-2xl font-bold" :class="cambio < 0 ? 'text-red-600' : 'text-green-600'"
                              x-text="'$' + Math.max(0, cambio).toFixed(2)"></span>
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
         MODAL: VENTA EXITOSA
    ══════════════════════════════════════════════════════════════ --}}
    <div x-show="ventaExitosa" x-cloak
         class="absolute inset-0 z-50 bg-neutral-900/30 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm border border-neutral-100 text-center overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="bg-green-50 px-8 py-8">
                <div class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-green-200">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-neutral-900">¡Venta Completada!</h3>
                <p class="text-sm text-neutral-500 mt-2">Stock e inventario actualizados.</p>
            </div>
            <div class="px-8 py-6 space-y-4">
                <div x-show="cambioFinal > 0" class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
                    <p class="text-sm text-amber-700 font-medium">Cambio a devolver</p>
                    <p class="text-3xl font-bold text-amber-800 mt-1" x-text="'$' + Number(cambioFinal).toFixed(2)"></p>
                </div>
                <button @click="nuevaVenta()"
                        class="w-full py-3.5 bg-neutral-900 text-white font-bold rounded-2xl hover:bg-neutral-800 transition-all">
                    Nueva Venta
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
        sesionActiva: {{ $sesion ? 'true' : 'false' }},
        sesionId: {{ $sesion ? $sesion->id : 'null' }},
        montoInicial: 0,
        cargandoApertura: false,
        errorApertura: '',

        // Productos
        todos: @json($productos),
        filtrados: [],
        busqueda: '',

        // Carrito
        carrito: [],
        descuento: 0,

        // Cobro
        openCobro: false,
        metodoPago: 'efectivo',
        montoEntregado: 0,
        metodos: [
            { v: 'efectivo',      label: 'Efectivo',      icon: '💵' },
            { v: 'tarjeta',       label: 'Tarjeta',       icon: '💳' },
            { v: 'transferencia', label: 'Transferencia', icon: '🏦' },
            { v: 'mixto',         label: 'Mixto',         icon: '🔀' },
        ],
        errorCobro: '',
        cargandoCobro: false,

        // Éxito
        ventaExitosa: false,
        cambioFinal: 0,

        // Getters
        get subtotal() { return this.carrito.reduce((s, i) => s + i.precio * i.qty, 0); },
        get total()    { return Math.max(0, this.subtotal - this.descuento); },
        get cambio()   { return this.montoEntregado - this.total; },

        // ── Init ──────────────────────────────────────────────────────────────
        init() {
            this.filtrados = this.todos;
            window.addEventListener('keydown', e => {
                if (e.key === 'F2') { e.preventDefault(); document.querySelector('[x-model="busqueda"]')?.focus(); }
                if (e.key === 'Escape') { this.openCobro = false; }
            });
        },

        // ── Productos ─────────────────────────────────────────────────────────
        filtrar() {
            const q = this.busqueda.toLowerCase().trim();
            this.filtrados = q
                ? this.todos.filter(p => p.nombre.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q)))
                : this.todos;
        },

        // ── Carrito ───────────────────────────────────────────────────────────
        agregar(p) {
            const ex = this.carrito.find(i => i.id === p.id);
            if (ex) { if (ex.qty < p.stock) ex.qty++; }
            else    { this.carrito.push({ id: p.id, nombre: p.nombre, precio: parseFloat(p.precio), stock: p.stock, qty: 1 }); }
        },
        inc(i) { if (this.carrito[i].qty < this.carrito[i].stock) this.carrito[i].qty++; },
        dec(i) { this.carrito[i].qty <= 1 ? this.carrito.splice(i, 1) : this.carrito[i].qty--; },

        // ── Cobro ─────────────────────────────────────────────────────────────
        async cobrar() {
            if (this.cargandoCobro) return;
            this.errorCobro = '';
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
                        carrito:         this.carrito.map(i => ({ id: i.id, qty: i.qty })),
                        descuento:       this.descuento || 0,
                        metodo_pago:     this.metodoPago,
                        monto_entregado: this.metodoPago === 'efectivo' ? this.montoEntregado : null,
                    })
                });
                const data = await res.json();

                if (data.success) {
                    this.openCobro = false;
                    this.cambioFinal = data.cambio ?? 0;
                    this.ventaExitosa = true;
                    // Actualizar stock local
                    this.carrito.forEach(item => {
                        const p = this.todos.find(p => p.id === item.id);
                        if (p) p.stock -= item.qty;
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
            this.ventaExitosa = false;
            this.carrito = [];
            this.descuento = 0;
            this.montoEntregado = 0;
            this.cambioFinal = 0;
        },

        // ── Apertura ──────────────────────────────────────────────────────────
        async abrirCaja() {
            if (this.cargandoApertura) return;
            this.errorApertura = '';
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
                    this.sesionId = data.sesion.id;
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
    };
}
</script>
@endpush
