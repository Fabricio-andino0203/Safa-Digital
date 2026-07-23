@extends('layouts.app')

@section('header_title', 'Calculadora de Precios — Stickers y Rotulados')

@section('content')
<div x-data="calculadoraStickers()" class="max-w-6xl mx-auto space-y-6 pb-16">

    <!-- Encabezado Principal y Acciones -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-neutral-900 text-white p-6 md:p-8 rounded-3xl shadow-xl relative overflow-hidden">
        <div class="space-y-1 z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold text-orange-300 border border-white/10">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M6 21h12a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Cotizador Rápido de Caja
            </div>
            <h2 class="text-2xl md:text-3xl font-black tracking-tight text-white">Calculadora Comercial de Stickers</h2>
            <p class="text-xs text-neutral-300">
                Calcula precios redondeados a múltiplos de 5 para facilitar el cobro en caja.
            </p>
        </div>

        <div class="flex items-center gap-3 z-10">
            <!-- Botón Toggle Zona de Costos (Modo Admin) -->
            <button @click="mostrarCostos = !mostrarCostos" 
                    :class="mostrarCostos ? 'bg-orange-600 text-white' : 'bg-white/10 hover:bg-white/20 text-neutral-200 border border-white/10'"
                    class="px-4 py-2.5 rounded-2xl font-bold text-xs transition-all flex items-center gap-2 backdrop-blur-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                <span x-text="mostrarCostos ? 'Ocultar Costos Admin' : '⚙️ Ajustes de Costeo (Admin)'"></span>
            </button>
        </div>

        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-orange-600/20 rounded-full blur-2xl pointer-events-none"></div>
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
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" @click="setProducto('vinil_corte')"
                            :class="tipoProducto === 'vinil_corte' ? 'bg-neutral-900 text-white border-neutral-900 shadow-md' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-3 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1.5">
                        <span class="text-base">🏷️</span>
                        <span>Sticker Vinil Corte</span>
                    </button>
                    <button type="button" @click="setProducto('impreso')"
                            :class="tipoProducto === 'impreso' ? 'bg-neutral-900 text-white border-neutral-900 shadow-md' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-3 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1.5">
                        <span class="text-base">🖨️</span>
                        <span>Sticker Impreso</span>
                    </button>
                    <button type="button" @click="setProducto('banner')"
                            :class="tipoProducto === 'banner' ? 'bg-neutral-900 text-white border-neutral-900 shadow-md' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-3 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1.5">
                        <span class="text-base">🚩</span>
                        <span>Banner / Lona</span>
                    </button>
                    <button type="button" @click="setProducto('pvc')"
                            :class="tipoProducto === 'pvc' ? 'bg-neutral-900 text-white border-neutral-900 shadow-md' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:bg-neutral-100'"
                            class="p-3 border rounded-2xl text-center text-xs font-bold transition-all flex flex-col items-center justify-center gap-1.5">
                        <span class="text-base">🧱</span>
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

        <!-- Panel de Precio Destacado a Cobrar (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Tarjeta Visual Destacada de Precio Total -->
            <div class="bg-gradient-to-br from-neutral-900 to-neutral-950 text-white rounded-3xl p-6 md:p-8 shadow-2xl space-y-6 border border-neutral-800 relative overflow-hidden">
                
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <span class="text-xs font-extrabold text-orange-400 uppercase tracking-widest">Cobro al Cliente</span>
                    <span class="px-2.5 py-1 bg-green-500/20 text-green-400 border border-green-500/30 text-[10px] font-bold rounded-full flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        Redondeado (Múltiplo de 5)
                    </span>
                </div>

                <!-- Precio Total Lote Destacado -->
                <div class="space-y-1 bg-white/5 p-6 rounded-2xl border border-white/10">
                    <span class="text-xs text-neutral-400 font-bold block uppercase tracking-wider">Precio Total a Cobrar (Lote)</span>
                    <div class="text-4xl md:text-5xl font-black text-orange-400 tracking-tight flex items-baseline gap-1">
                        <span>L.</span>
                        <span x-text="formatNumber(precioTotalRedondeado)"></span>
                    </div>
                    <p class="text-[11px] text-neutral-300 block pt-1">
                        Total por <strong class="text-white" x-text="cantidad"></strong> sticker(s) de <strong class="text-white" x-text="ancho + 'x' + alto + ' ' + unidadMedida"></strong>
                    </p>
                </div>

                <!-- Precio Unitario Sugerido -->
                <div class="bg-white/5 p-4 rounded-2xl border border-white/10 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-neutral-400 font-bold block uppercase">Precio Unitario Sugerido</span>
                        <span class="text-xs text-neutral-500">Unidad al cliente</span>
                    </div>
                    <div class="text-2xl font-black text-green-400">
                        L. <span x-text="formatNumber(precioUnitarioSugerido)"></span>
                    </div>
                </div>

                <!-- Botones de Acción Comercial -->
                <div class="pt-2 space-y-2">
                    <button type="button" @click="copiarResumen()" 
                            class="w-full py-3.5 bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs rounded-2xl transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
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
         class="bg-white border-2 border-orange-200 rounded-3xl p-6 md:p-8 shadow-md space-y-6">

        <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
            <div class="flex items-center gap-2">
                <span class="p-2 bg-orange-100 text-orange-600 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                </span>
                <div>
                    <h3 class="text-base font-black text-neutral-900">Configuración Interna de Costos y Márgenes (Administración)</h3>
                    <p class="text-xs text-neutral-500">Esta sección permite definir los costos de compra y % de ganancia sin mostrarse al cliente.</p>
                </div>
            </div>
            <button type="button" @click="mostrarCostos = false" class="text-xs font-bold text-neutral-400 hover:text-neutral-700">
                ✕ Cerrar
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Modo de Costeo Yarda vs Pulgada Cuadrada -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-700">Modo de Costeo del Material</label>
                <select x-model="modoCosteo" class="w-full px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl text-xs font-bold text-neutral-900">
                    <option value="yarda">Costo por Yarda (Vinil Corte)</option>
                    <option value="in2">Costo por Pulgada Cuadrada (Impresos/PVC)</option>
                </select>
            </div>

            <!-- Input Costo según Modo -->
            <template x-if="modoCosteo === 'yarda'">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-neutral-700">Costo de la Yarda (L.) *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                        <input type="number" step="0.01" x-model.number="costoYarda"
                               class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                    </div>
                </div>
            </template>

            <template x-if="modoCosteo === 'in2'">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-neutral-700">Costo por Pulgada Cuadrada (L. / in²) *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 font-bold text-xs">L.</span>
                        <input type="number" step="0.001" x-model.number="costoPorIn2"
                               class="w-full pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
                    </div>
                </div>
            </template>

            <!-- Margen de Ganancia (%) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-neutral-700">Margen de Ganancia (%)</label>
                    <span class="text-xs font-black text-orange-600" x-text="margenGanancia + '%'"></span>
                </div>
                <input type="number" x-model.number="margenGanancia" min="0" max="500" step="5"
                       class="w-full px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl text-sm font-bold text-neutral-900">
            </div>

        </div>

        <!-- Presets Rápidos de Margen Comercial -->
        <div class="pt-2 border-t border-neutral-100 flex items-center gap-3">
            <span class="text-xs font-bold text-neutral-500">Preset Margen:</span>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="margenGanancia = 30" :class="margenGanancia == 30 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-3 py-1 text-xs font-semibold rounded-lg transition-all">30% (Mayorista)</button>
                <button type="button" @click="margenGanancia = 50" :class="margenGanancia == 50 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-3 py-1 text-xs font-semibold rounded-lg transition-all">50% (Estándar)</button>
                <button type="button" @click="margenGanancia = 100" :class="margenGanancia == 100 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-3 py-1 text-xs font-semibold rounded-lg transition-all">100% (Detalle / Retail)</button>
                <button type="button" @click="margenGanancia = 150" :class="margenGanancia == 150 ? 'bg-orange-600 text-white' : 'bg-neutral-100 text-neutral-700'" class="px-3 py-1 text-xs font-semibold rounded-lg transition-all">150% (Urgente)</button>
            </div>
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
function calculadoraStickers() {
    return {
        // UI State
        mostrarCostos: false,
        copiado: false,
        
        // Tipo Producto & Modo Costeo
        tipoProducto: 'vinil_corte', // vinil_corte, impreso, banner, pvc
        modoCosteo: 'yarda',         // yarda, in2
        unidadMedida: 'cm',          // cm, in

        // Form inputs
        ancho: 5,
        alto: 5,
        cantidad: 100,
        colores: 1,

        // Cost Inputs Admin
        costoYarda: 250,
        anchoYardaCm: 100,
        largoYardaCm: 91.44,

        costoPorIn2: 0.08,
        margenGanancia: 50,

        // Cambio de producto
        setProducto(tipo) {
            this.tipoProducto = tipo;
            if (tipo === 'vinil_corte') {
                this.modoCosteo = 'yarda';
            } else if (tipo === 'impreso') {
                this.modoCosteo = 'in2';
                this.costoPorIn2 = 0.08;
            } else if (tipo === 'banner') {
                this.modoCosteo = 'in2';
                this.costoPorIn2 = 0.05;
            } else if (tipo === 'pvc') {
                this.modoCosteo = 'in2';
                this.costoPorIn2 = 0.12;
            }
        },

        // Cambio de unidad (cm <-> in)
        setUnidad(nuevaUnidad) {
            if (this.unidadMedida === nuevaUnidad) return;
            if (nuevaUnidad === 'in') {
                // De cm a in
                this.ancho = Number((this.ancho / 2.54).toFixed(2));
                this.alto = Number((this.alto / 2.54).toFixed(2));
            } else {
                // De in a cm
                this.ancho = Number((this.ancho * 2.54).toFixed(2));
                this.alto = Number((this.alto * 2.54).toFixed(2));
            }
            this.unidadMedida = nuevaUnidad;
        },

        setMedidasPreset(w, h) {
            this.ancho = w;
            this.alto = h;
        },

        // Conversiones de dimensiones del sticker
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

        // Costo Base de Producción Unitario (sin margen)
        get costoProduccionUnitario() {
            let costoBase = 0;

            if (this.modoCosteo === 'yarda') {
                const areaYardaCm2 = (Number(this.anchoYardaCm) || 100) * (Number(this.largoYardaCm) || 91.44);
                const costoPorCm2 = areaYardaCm2 > 0 ? (Number(this.costoYarda) || 0) / areaYardaCm2 : 0;
                costoBase = this.areaStickerCm2 * costoPorCm2;
            } else {
                // Modo in2
                costoBase = this.areaStickerIn2 * (Number(this.costoPorIn2) || 0);
            }

            const numColores = Math.max(1, Number(this.colores) || 1);
            return costoBase * numColores;
        },

        get costoProduccionTotal() {
            const cant = Math.max(1, Number(this.cantidad) || 1);
            return this.costoProduccionUnitario * cant;
        },

        // Precio Sin Redondear
        get precioTotalSinRedondear() {
            const margen = (Number(this.margenGanancia) || 0) / 100;
            return this.costoProduccionTotal * (1 + margen);
        },

        // ALGORITMO DE REDONDEO A MÚLTIPLOS DE 5 (0 o 5 al final)
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
            const texto = `*COTIZACIÓN DE STICKERS / ROTULADO*\n` +
                          `-----------------------------------\n` +
                          `• Producto: ${this.tipoProducto.toUpperCase().replace('_', ' ')}\n` +
                          `• Medidas: ${this.ancho} x ${this.alto} ${this.unidadMedida}\n` +
                          `• Cantidad: ${this.cantidad} unidades\n` +
                          `• Capas/Colores: ${this.colores}\n` +
                          `• Precio Unitario: L. ${this.formatNumber(this.precioUnitarioSugerido)}\n` +
                          `-----------------------------------\n` +
                          `*TOTAL A COBRAR: L. ${this.formatNumber(this.precioTotalRedondeado)}*`;

            navigator.clipboard.writeText(texto).then(() => {
                this.copiado = true;
                setTimeout(() => this.copiado = false, 3000);
            });
        }
    }
}
</script>
@endpush
@endsection
