@extends('layouts.app')

@section('header_title', 'Calculadora de Precios — Stickers y Rotulados')

@section('content')
<div x-data="calculadoraStickers(@js($settings))" class="max-w-6xl mx-auto space-y-6 pb-16">

    <!-- Mensaje de Notificación de Guardado -->
    <div x-show="guardadoExito" x-transition.opacity class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl shadow-sm text-sm font-bold flex items-center justify-between" x-cloak>
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Valores de costeo vinculados y guardados en la configuración global de la base de datos.</span>
        </div>
        <button @click="guardadoExito = false" class="text-green-500 hover:text-green-800">✕</button>
    </div>

    <!-- Encabezado Principal y Acciones (Clean White Theme) -->
    <div class="bg-white border border-neutral-200 shadow-sm p-6 md:p-8 rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="space-y-1.5 z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-orange-50 border border-orange-200 rounded-full text-xs font-bold text-orange-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M15 11h.01M12 11h.01M9 11h.01M6 21h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Cotizador Rápido de Caja
            </div>
            <h2 class="text-2xl md:text-3xl font-black tracking-tight text-neutral-900">Calculadora Comercial de Stickers</h2>
            <p class="text-xs md:text-sm text-neutral-500">
                Estandariza cotizaciones con redondeo automático a múltiplos de 5 para un cobro ágil en caja.
            </p>
        </div>

        <div class="flex items-center gap-3 z-10">
            <!-- Botón Toggle Zona de Costos (Modo Admin) -->
            <button @click="mostrarCostos = !mostrarCostos" 
                    :class="mostrarCostos ? 'bg-orange-600 text-white shadow-sm' : 'bg-neutral-100 hover:bg-neutral-200 text-neutral-700 border border-neutral-200'"
                    class="px-4 py-2.5 rounded-2xl font-bold text-xs transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                <span x-text="mostrarCostos ? 'Ocultar Costos Admin' : '⚙️ Ajustes de Costeo (Admin)'"></span>
            </button>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- 1. ZONA DE VENTA (VISIBLE POR DEFECTO PARA EMPLEADOS Y VENDEDORES)        -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Panel Formulario de Venta (7 Cols) -->
        <div class="lg:col-span-7 bg-white border border-neutral-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">

            <!-- Fila 1: Selección de Tipo de Producto / Material -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider">Tipo de Producto / Material</label>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                    <button type="button" @click="setProducto('vinil_corte')"
                            :class="tipoProducto === 'vinil_corte' ? 'bg-neutral-900 text-white border-neutral-900 shadow-sm' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-2.5 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1">
                        <span class="text-sm">🏷️</span>
                        <span>Vinil Corte</span>
                    </button>
                    <button type="button" @click="setProducto('impreso')"
                            :class="tipoProducto === 'impreso' ? 'bg-neutral-900 text-white border-neutral-900 shadow-sm' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-2.5 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1">
                        <span class="text-sm">🖨️</span>
                        <span>Impreso</span>
                    </button>
                    <button type="button" @click="setProducto('troquelado')"
                            :class="tipoProducto === 'troquelado' ? 'bg-neutral-900 text-white border-neutral-900 shadow-sm' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-2.5 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1">
                        <span class="text-sm">✂️</span>
                        <span>Troquelado</span>
                    </button>
                    <button type="button" @click="setProducto('banner')"
                            :class="tipoProducto === 'banner' ? 'bg-neutral-900 text-white border-neutral-900 shadow-sm' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-2.5 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1">
                        <span class="text-sm">🚩</span>
                        <span>Banner / Lona</span>
                    </button>
                    <button type="button" @click="setProducto('pvc')"
                            :class="tipoProducto === 'pvc' ? 'bg-neutral-900 text-white border-neutral-900 shadow-sm' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-2.5 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1">
                        <span class="text-sm">🧱</span>
                        <span>Rotulado PVC</span>
                    </button>
                </div>
            </div>

            <!-- Fila 2: Medidas y Selector de Unidad (cm vs in) -->
            <div class="space-y-3 pt-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider">Dimensiones del Diseño</label>
                    <!-- Selector Toggle Rápido de Unidad (cm / in) -->
                    <div class="inline-flex bg-neutral-100 p-1 rounded-xl border border-neutral-200">
                        <button type="button" @click="setUnidad('cm')"
                                :class="unidadMedida === 'cm' ? 'bg-white text-neutral-900 shadow-sm font-black' : 'text-neutral-500 font-bold hover:text-neutral-900'"
                                class="px-3 py-1 text-xs rounded-lg transition-all">
                            Centímetros (cm)
                        </button>
                        <button type="button" @click="setUnidad('in')"
                                :class="unidadMedida === 'in' ? 'bg-white text-neutral-900 shadow-sm font-black' : 'text-neutral-500 font-bold hover:text-neutral-900'"
                                class="px-3 py-1 text-xs rounded-lg transition-all">
                            Pulgadas (in)
                        </button>
                    </div>
                </div>

                <!-- Presets Rápidos de Medida -->
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="setMedidasPreset(3, 3)" class="px-2.5 py-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-semibold rounded-lg transition-colors">
                        3 x 3 <span x-text="unidadMedida"></span>
                    </button>
                    <button type="button" @click="setMedidasPreset(5, 5)" class="px-2.5 py-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-semibold rounded-lg transition-colors">
                        5 x 5 <span x-text="unidadMedida"></span>
                    </button>
                    <button type="button" @click="setMedidasPreset(7.5, 7.5)" class="px-2.5 py-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-semibold rounded-lg transition-colors">
                        7.5 x 7.5 <span x-text="unidadMedida"></span>
                    </button>
                    <button type="button" @click="setMedidasPreset(10, 10)" class="px-2.5 py-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-semibold rounded-lg transition-colors">
                        10 x 10 <span x-text="unidadMedida"></span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1">
                            Ancho (<span x-text="unidadMedida"></span>) *
                        </label>
                        <input type="number" step="0.1" x-model.number="ancho" min="0.1"
                               class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-base font-extrabold text-neutral-900 focus:outline-none focus:bg-white focus:border-neutral-900 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1">
                            Alto (<span x-text="unidadMedida"></span>) *
                        </label>
                        <input type="number" step="0.1" x-model.number="alto" min="0.1"
                               class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-base font-extrabold text-neutral-900 focus:outline-none focus:bg-white focus:border-neutral-900 transition-all">
                    </div>
                </div>
            </div>

            <!-- Fila 3: Cantidad y Capas/Colores -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-neutral-700 mb-1">Cantidad a Producir (Unidades) *</label>
                    <input type="number" x-model.number="cantidad" min="1" step="1"
                           class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-base font-extrabold text-neutral-900 focus:outline-none focus:bg-white focus:border-neutral-900 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-neutral-700 mb-1">N° Colores / Capas Vinil *</label>
                    <input type="number" x-model.number="colores" min="1" step="1"
                           class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-2xl text-base font-extrabold text-neutral-900 focus:outline-none focus:bg-white focus:border-neutral-900 transition-all">
                    <span class="text-[10px] text-neutral-400 mt-1 block">Capas superpuestas de vinil</span>
                </div>
            </div>

        </div>

        <!-- Panel de Precio Destacado a Cobrar (5 Cols) (Clean White Theme) -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Tarjeta Visual Destacada de Precio Total -->
            <div class="bg-white border border-neutral-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
                
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                    <span class="text-xs font-extrabold text-orange-600 uppercase tracking-widest">Cobro al Cliente</span>
                    <span class="px-2.5 py-1 bg-orange-50 text-orange-700 border border-orange-200 text-[10px] font-bold rounded-full flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                        Redondeado (Múltiplo de 5)
                    </span>
                </div>

                <!-- Precio Total Lote Destacado (Acento suave Naranja Institucional) -->
                <div class="space-y-1 bg-orange-50/70 border border-orange-200/80 p-6 rounded-2xl">
                    <span class="text-xs text-orange-900 font-bold block uppercase tracking-wider">Precio Total a Cobrar (Lote)</span>
                    <div class="text-4xl md:text-5xl font-black text-neutral-900 tracking-tight flex items-baseline gap-1">
                        <span>L.</span>
                        <span x-text="formatNumber(precioTotalRedondeado)"></span>
                    </div>
                    <p class="text-xs text-neutral-600 block pt-1">
                        Total por <strong class="text-neutral-900" x-text="cantidad"></strong> sticker(s) de <strong class="text-neutral-900" x-text="ancho + 'x' + alto + ' ' + unidadMedida"></strong>
                        <template x-if="tipoProducto === 'banner'">
                            <span class="text-emerald-700 font-bold block mt-0.5">+ Flete de transporte incluido (L. <span x-text="costoFijoTransporte"></span>)</span>
                        </template>
                    </p>
                </div>

                <!-- Precio Unitario Sugerido -->
                <div class="bg-neutral-50 border border-neutral-200 p-4 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="text-xs text-neutral-700 font-bold block uppercase">Precio Unitario Sugerido</span>
                        <span class="text-[11px] text-neutral-500">Unidad al cliente</span>
                    </div>
                    <div class="text-2xl font-black text-neutral-900">
                        L. <span x-text="formatNumber(precioUnitarioSugerido)"></span>
                    </div>
                </div>

                <!-- Botones de Acción Comercial -->
                <div class="pt-2 space-y-2.5">
                    <!-- Formulario POST para Session Handoff a la Creación de Pedidos -->
                    <form action="{{ route('calculadora.enviarAPedido') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipo_material" :value="tipoProducto">
                        <input type="hidden" name="ancho" :value="ancho">
                        <input type="hidden" name="alto" :value="alto">
                        <input type="hidden" name="unidad" :value="unidadMedida">
                        <input type="hidden" name="cantidad" :value="cantidad">
                        <input type="hidden" name="precio_unitario" :value="precioUnitarioSugerido">
                        <input type="hidden" name="precio_total" :value="precioTotalRedondeado">
                        <input type="hidden" name="costo_produccion_total" :value="costoProduccionTotal">

                        <button type="submit" 
                                class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-2xl transition-all shadow-md active:scale-95 flex items-center justify-center gap-2 uppercase tracking-wider">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>🛒 Generar Pedido con estas Medidas</span>
                        </button>
                    </form>

                    <button type="button" @click="copiarResumen()" 
                            class="w-full py-3.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs rounded-2xl transition-all shadow-sm active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span x-text="copiado ? '¡Cotización Copiada!' : 'Copiar Resumen para WhatsApp'"></span>
                    </button>
                </div>

            </div>

        </div>

    </div>


    <!-- ========================================================================= -->
    <!-- 2. ZONA DE COSTOS Y MÁRGENES (COLAPSADA POR DEFECTO PARA EMPLEADOS)       -->
    <!-- ========================================================================= -->
    <div x-show="mostrarCostos" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white border-2 border-orange-200 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">

        <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
            <div class="flex items-center gap-2">
                <span class="p-2 bg-orange-100 text-orange-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                </span>
                <div>
                    <h3 class="text-base font-black text-neutral-900">Configuración Interna de Costos y Márgenes (Administración)</h3>
                    <p class="text-xs text-neutral-500">Define los costos base persistentes por pulgada cuadrada para cada tipo de material.</p>
                </div>
            </div>
            <button type="button" @click="mostrarCostos = false" class="text-xs font-bold text-neutral-400 hover:text-neutral-700">
                ✕ Cerrar
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Modo de Costeo Yarda vs Pulgada Cuadrada -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Modo de Costeo del Material</label>
                <select x-model="modoCosteo" class="w-full px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl text-xs font-bold text-neutral-900">
                    <option value="yarda">Costo por Yarda (Vinil Corte)</option>
                    <option value="in2">Costo por Pulgada Cuadrada (in²)</option>
                </select>
            </div>

            <!-- Input Costo Yarda -->
            <div class="space-y-2" x-show="modoCosteo === 'yarda'">
                <label class="block text-xs font-bold text-neutral-700">Costo Yarda Vinil Corte (L.) *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                    <input type="number" step="0.01" x-model.number="costoYarda"
                           class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                </div>
            </div>

            <!-- Input Costo Impreso / in² -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Costo Impreso / in² *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                    <input type="number" step="0.001" x-model.number="costoImpresoIn2" @input="if(tipoProducto === 'impreso') costoPorIn2 = costoImpresoIn2"
                           class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                </div>
            </div>

            <!-- Input Costo Troquelado / in² -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Costo Troquelado / in² *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                    <input type="number" step="0.001" x-model.number="costoTroqueladoIn2" @input="if(tipoProducto === 'troquelado') costoPorIn2 = costoTroqueladoIn2"
                           class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                </div>
            </div>

            <!-- Input Costo Banner / in² -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Costo Banner / in² *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                    <input type="number" step="0.001" x-model.number="costoBannerIn2" @input="if(tipoProducto === 'banner') costoPorIn2 = costoBannerIn2"
                           class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                </div>
            </div>

            <!-- Input Costo Fijo de Transporte (Banner / Lona) -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Costo Fijo de Transporte / Flete (L.) *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                    <input type="number" step="0.01" x-model.number="costoFijoTransporte"
                           class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                </div>
            </div>

            <!-- Input Costo PVC 3mm -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Costo PVC 3mm / in²</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                    <input type="number" step="0.001" x-model.number="costoPvc3mm" @input="if(tipoProducto === 'pvc') costoPorIn2 = costoPvc3mm"
                           class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                </div>
            </div>

            <!-- Input Costo PVC 5mm -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Costo PVC 5mm / in²</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                    <input type="number" step="0.001" x-model.number="costoPvc5mm"
                           class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                </div>
            </div>

            <!-- Margen de Ganancia (%) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-neutral-700">Margen por Defecto (%)</label>
                    <span class="text-xs font-black text-orange-600" x-text="margenGanancia + '%'"></span>
                </div>
                <input type="number" x-model.number="margenGanancia" min="0" max="500" step="5"
                       class="w-full px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
            </div>

        </div>

        <!-- Presets Rápidos de Margen Comercial + Botón de Guardado Persistente en BD -->
        <div class="pt-4 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-neutral-500">Preset Margen:</span>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="margenGanancia = 30" :class="margenGanancia == 30 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all">30%</button>
                    <button type="button" @click="margenGanancia = 50" :class="margenGanancia == 50 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all">50%</button>
                    <button type="button" @click="margenGanancia = 100" :class="margenGanancia == 100 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all">100%</button>
                    <button type="button" @click="margenGanancia = 150" :class="margenGanancia == 150 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all">150%</button>
                </div>
            </div>

            <!-- Botón Guardar Configuraciones Globales en BD -->
            <button type="button" @click="guardarConfiguracionGlobal()" :disabled="guardando"
                    class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 disabled:bg-neutral-400 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span x-text="guardando ? 'Guardando en BD...' : '💾 Guardar Ajustes como Predeterminados'"></span>
            </button>
        </div>

        <!-- Auditoría de Márgenes y Costos de Producción Real -->
        <div class="bg-neutral-50 p-4 rounded-2xl border border-neutral-200 grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-neutral-500 block font-medium">Costo Producción Real Unitario:</span>
                <span class="font-extrabold text-neutral-900" x-text="'L. ' + formatNumber(costoProduccionUnitario)"></span>
            </div>
            <div>
                <span class="text-neutral-500 block font-medium">Costo Producción Total Lote:</span>
                <span class="font-extrabold text-neutral-900" x-text="'L. ' + formatNumber(costoProduccionTotal)"></span>
            </div>
            <div>
                <span class="text-neutral-500 block font-medium">Ganancia Neta Estimada Lote:</span>
                <span class="font-extrabold text-green-600" x-text="'L. ' + formatNumber(gananciaNetaTotal)"></span>
            </div>
            <div>
                <span class="text-neutral-500 block font-medium">Stickers Aprox. por Yarda:</span>
                <span class="font-extrabold text-neutral-900" x-text="stickersPorYarda + ' uds'"></span>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function calculadoraStickers(settings) {
    return {
        // UI State
        mostrarCostos: false,
        copiado: false,
        guardando: false,
        guardadoExito: false,
        
        // Tipo Producto & Modo Costeo
        tipoProducto: 'vinil_corte',
        modoCosteo: 'yarda',
        unidadMedida: 'cm',

        // Form inputs
        ancho: 5,
        alto: 5,
        cantidad: 100,
        colores: 1,

        // Settings iniciales inyectados desde la BD (GlobalConfig)
        costoYarda: Number(settings.calc_costo_yarda || 150),
        anchoYardaCm: 100,
        largoYardaCm: 91.44,

        costoPorIn2: Number(settings.calc_costo_pulgada_cuadrada || 0.08),
        costoImpresoIn2: Number(settings.calc_costo_impreso_in2 || settings.calc_costo_pulgada_cuadrada || 0.08),
        costoTroqueladoIn2: Number(settings.calc_costo_troquelado_in2 || 0.09),
        costoBannerIn2: Number(settings.calc_costo_banner_in2 || 0.05),
        costoPvc3mm: Number(settings.calc_costo_pvc_3mm || 0.12),
        costoPvc5mm: Number(settings.calc_costo_pvc_5mm || 0.18),
        costoFijoTransporte: Number(settings.calc_costo_fijo_transporte || 100),
        margenGanancia: Number(settings.calc_margen_ganancia_default || 50),

        // Cambio de producto y actualización de matemática de costo
        setProducto(tipo) {
            this.tipoProducto = tipo;
            if (tipo === 'vinil_corte') {
                this.modoCosteo = 'yarda';
            } else if (tipo === 'impreso') {
                this.modoCosteo = 'in2';
                this.costoPorIn2 = this.costoImpresoIn2;
            } else if (tipo === 'troquelado') {
                this.modoCosteo = 'in2';
                this.costoPorIn2 = this.costoTroqueladoIn2;
            } else if (tipo === 'banner') {
                this.modoCosteo = 'in2';
                this.costoPorIn2 = this.costoBannerIn2;
            } else if (tipo === 'pvc') {
                this.modoCosteo = 'in2';
                this.costoPorIn2 = this.costoPvc3mm;
            }
        },

        // Cambio de unidad (cm <-> in)
        setUnidad(nuevaUnidad) {
            if (this.unidadMedida === nuevaUnidad) return;
            if (nuevaUnidad === 'in') {
                this.ancho = Number((this.ancho / 2.54).toFixed(2));
                this.alto = Number((this.alto / 2.54).toFixed(2));
            } else {
                this.ancho = Number((this.ancho * 2.54).toFixed(2));
                this.alto = Number((this.alto * 2.54).toFixed(2));
            }
            this.unidadMedida = nuevaUnidad;
        },

        setMedidasPreset(w, h) {
            this.ancho = w;
            this.alto = h;
        },

        get anchoCm() {
            return this.unidadMedida === 'in' ? this.ancho * 2.54 : this.ancho;
        },

        get altoCm() {
            return this.unidadMedida === 'in' ? this.alto * 2.54 : this.alto;
        },

        get areaStickerCm2() {
            return (Number(this.anchoCm) || 0) * (Number(this.altoCm) || 0);
        },

        get areaStickerIn2() {
            return this.areaStickerCm2 / 6.4516;
        },

        // Costo de Transporte aplicable a Banners
        get fleteTransporte() {
            return (this.tipoProducto === 'banner') ? Number(this.costoFijoTransporte || 0) : 0;
        },

        get costoProduccionUnitario() {
            let costoBase = 0;

            if (this.modoCosteo === 'yarda') {
                const areaYardaCm2 = (Number(this.anchoYardaCm) || 100) * (Number(this.largoYardaCm) || 91.44);
                const costoPorCm2 = areaYardaCm2 > 0 ? (Number(this.costoYarda) || 0) / areaYardaCm2 : 0;
                costoBase = this.areaStickerCm2 * costoPorCm2;
            } else {
                costoBase = this.areaStickerIn2 * (Number(this.costoPorIn2) || 0);
            }

            const numColores = Math.max(1, Number(this.colores) || 1);
            return costoBase * numColores;
        },

        get costoProduccionTotal() {
            const cant = Math.max(1, Number(this.cantidad) || 1);
            return (this.costoProduccionUnitario * cant) + this.fleteTransporte;
        },

        get precioTotalSinRedondear() {
            const margen = (Number(this.margenGanancia) || 0) / 100;
            return this.costoProduccionTotal * (1 + margen);
        },

        get precioTotalRedondeado() {
            const original = this.precioTotalSinRedondear;
            if (original <= 0) return 0;
            const redondeado = Math.round(original / 5) * 5;
            return Math.max(redondeado, 5);
        },

        get precioUnitarioSugerido() {
            const cant = Math.max(1, Number(this.cantidad) || 1);
            if (this.precioTotalRedondeado <= 0) return 0;
            const unit = this.precioTotalRedondeado / cant;
            return Number(unit.toFixed(2));
        },

        get gananciaNetaTotal() {
            return this.precioTotalRedondeado - this.costoProduccionTotal;
        },

        get stickersPorYarda() {
            const areaYarda = (Number(this.anchoYardaCm) || 100) * (Number(this.largoYardaCm) || 91.44);
            if (this.areaStickerCm2 <= 0) return 0;
            return Math.floor(areaYarda / this.areaStickerCm2);
        },

        formatNumber(num) {
            if (isNaN(num) || !isFinite(num)) return '0.00';
            return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        copiarResumen() {
            let texto = `*COTIZACIÓN DE STICKERS / ROTULADO*\n` +
                          `-----------------------------------\n` +
                          `• Producto: ${this.tipoProducto.toUpperCase().replace('_', ' ')}\n` +
                          `• Medidas: ${this.ancho} x ${this.alto} ${this.unidadMedida}\n` +
                          `• Cantidad: ${this.cantidad} unidades\n` +
                          `• Capas/Colores: ${this.colores}\n`;

            if (this.tipoProducto === 'banner') {
                texto += `• Flete de Transporte Incluido: L. ${this.formatNumber(this.costoFijoTransporte)}\n`;
            }

            texto += `• Precio Unitario: L. ${this.formatNumber(this.precioUnitarioSugerido)}\n` +
                     `-----------------------------------\n` +
                     `*TOTAL A COBRAR: L. ${this.formatNumber(this.precioTotalRedondeado)}*`;

            navigator.clipboard.writeText(texto).then(() => {
                this.copiado = true;
                setTimeout(() => this.copiado = false, 3000);
            });
        },

        // Transferencia directa al POS / Carrito de Ventas
        enviarAPos() {
            const params = new URLSearchParams({
                from_calculadora: 1,
                tipo_material: this.tipoProducto,
                ancho: this.ancho,
                alto: this.alto,
                unidad: this.unidadMedida,
                cantidad: this.cantidad,
                colores: this.colores,
                precio_unitario: this.precioUnitarioSugerido,
                precio_total: this.precioTotalRedondeado,
                costo_produccion_total: this.costoProduccionTotal
            });
            window.location.href = '{{ route("pos.index") }}?' + params.toString();
        },

        // Guardar la configuración global de costos en la BD por AJAX
        guardarConfiguracionGlobal() {
            this.guardando = true;
            fetch('{{ route("calculadora.configuracion") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    calc_costo_yarda: this.costoYarda,
                    calc_costo_pulgada_cuadrada: this.costoImpresoIn2,
                    calc_costo_banner_in2: this.costoBannerIn2,
                    calc_costo_troquelado_in2: this.costoTroqueladoIn2,
                    calc_costo_impreso_in2: this.costoImpresoIn2,
                    calc_costo_pvc_3mm: this.costoPvc3mm,
                    calc_costo_pvc_5mm: this.costoPvc5mm,
                    calc_costo_fijo_transporte: this.costoFijoTransporte,
                    calc_margen_ganancia_default: this.margenGanancia
                })
            })
            .then(res => res.json())
            .then(data => {
                this.guardando = false;
                if (data.success) {
                    this.guardadoExito = true;
                    setTimeout(() => this.guardadoExito = false, 4000);
                }
            })
            .catch(err => {
                this.guardando = false;
                alert('Error al guardar la configuración: ' + err.message);
            });
        }
    }
}
</script>
@endpush
@endsection
