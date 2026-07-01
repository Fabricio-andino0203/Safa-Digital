@extends('layouts.app')

@section('header_title', 'Panel de Reportes y Auditoría')

@section('content')
<div x-data="reportesManager()" class="max-w-6xl mx-auto space-y-8" x-cloak>
    
    <div class="border-b border-neutral-100 pb-4">
        <p class="text-sm text-neutral-500">Accede a las auditorías, arqueos, estadísticas y estados financieros del negocio.</p>
    </div>

    <!-- CATEGORÍA 1: VENTAS Y RENDIMIENTO -->
    <div class="space-y-4">
        <h2 class="text-sm font-bold text-neutral-400 uppercase tracking-wider flex items-center gap-2">
            <span>📊</span> Ventas y Rendimiento
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Ventas Generales -->
            <button @click="abrirModal('{{ route('reportes.ventas.pdf') }}', 'Ventas Generales')" class="text-left block p-6 bg-white border border-neutral-200 hover:border-neutral-900 rounded-3xl transition-all shadow-sm group w-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-neutral-100 group-hover:bg-neutral-950 group-hover:text-white rounded-2xl transition-colors">
                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-neutral-900 group-hover:text-neutral-950">Ventas Generales</h3>
                        <p class="text-xs text-neutral-500">Historial completo y facturación de pedidos entregados.</p>
                    </div>
                </div>
            </button>

            <!-- Productos Más Vendidos -->
            <button @click="abrirModal('{{ route('reportes.top-productos.pdf') }}', 'Productos Más Vendidos')" class="text-left block p-6 bg-white border border-neutral-200 hover:border-neutral-900 rounded-3xl transition-all shadow-sm group w-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-neutral-100 group-hover:bg-neutral-950 group-hover:text-white rounded-2xl transition-colors">
                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-neutral-900 group-hover:text-neutral-950">Más Vendidos</h3>
                        <p class="text-xs text-neutral-500">Ranking detallado de los productos y variantes con mayor demanda.</p>
                    </div>
                </div>
            </button>

            <!-- Reporte de Rentabilidad -->
            <button @click="abrirModal('{{ route('reportes.rentabilidad.pdf') }}', 'Rentabilidad del Negocio')" class="text-left block p-6 bg-white border border-neutral-200 hover:border-neutral-900 rounded-3xl transition-all shadow-sm group w-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-neutral-100 group-hover:bg-neutral-950 group-hover:text-white rounded-2xl transition-colors">
                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-neutral-900 group-hover:text-neutral-950">Rentabilidad</h3>
                        <p class="text-xs text-neutral-500">Cálculo de ganancias netas comparando costo vs precio de venta.</p>
                    </div>
                </div>
            </button>
        </div>
    </div>

    <!-- CATEGORÍA 2: CAJA Y FINANZAS -->
    <div class="space-y-4">
        <h2 class="text-sm font-bold text-neutral-400 uppercase tracking-wider flex items-center gap-2">
            <span>💵</span> Caja y Finanzas
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Historial de Cortes de Caja -->
            <a href="{{ route('reportes.cortes') }}" class="block p-6 bg-white border border-neutral-200 hover:border-neutral-900 rounded-3xl transition-all shadow-sm group">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-neutral-100 group-hover:bg-neutral-950 group-hover:text-white rounded-2xl transition-colors">
                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-neutral-900 group-hover:text-neutral-950">Cortes de Caja</h3>
                        <p class="text-xs text-neutral-500">Historial, arqueos, retiros y auditorías de cierre de cajeros.</p>
                    </div>
                </div>
            </a>

            <!-- Flujo de Tesorería -->
            <button @click="abrirModal('{{ route('reportes.flujo-tesoreria.pdf') }}', 'Flujo de Tesorería')" class="text-left block p-6 bg-white border border-neutral-200 hover:border-neutral-900 rounded-3xl transition-all shadow-sm group w-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-neutral-100 group-hover:bg-neutral-950 group-hover:text-white rounded-2xl transition-colors">
                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-neutral-900 group-hover:text-neutral-950">Flujo de Tesorería</h3>
                        <p class="text-xs text-neutral-500">Historial contable de movimientos (Depósitos y Retiros).</p>
                    </div>
                </div>
            </button>
        </div>
    </div>

    <!-- CATEGORÍA 3: INVENTARIO Y GASTOS -->
    <div class="space-y-4">
        <h2 class="text-sm font-bold text-neutral-400 uppercase tracking-wider flex items-center gap-2">
            <span>📦</span> Inventario y Gastos
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Compras a Proveedores -->
            <button @click="abrirModal('{{ route('reportes.compras.pdf') }}', 'Compras a Proveedores')" class="text-left block p-6 bg-white border border-neutral-200 hover:border-neutral-900 rounded-3xl transition-all shadow-sm group w-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-neutral-100 group-hover:bg-neutral-950 group-hover:text-white rounded-2xl transition-colors">
                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-neutral-900 group-hover:text-neutral-950">Compras a Proveedores</h3>
                        <p class="text-xs text-neutral-500">Historial e inversión en compras valorizadas y pagadas.</p>
                    </div>
                </div>
            </button>

            <!-- Mermas y Ajustes de Stock -->
            <button @click="abrirModal('{{ route('reportes.ajustes-stock.pdf') }}', 'Mermas y Ajustes de Stock')" class="text-left block p-6 bg-white border border-neutral-200 hover:border-neutral-900 rounded-3xl transition-all shadow-sm group w-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-neutral-100 group-hover:bg-neutral-950 group-hover:text-white rounded-2xl transition-colors">
                        <svg class="w-6 h-6 text-neutral-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-neutral-900 group-hover:text-neutral-950">Mermas y Ajustes</h3>
                        <p class="text-xs text-neutral-500">Auditoría de ajustes de stock y salidas físicas justificadas.</p>
                    </div>
                </div>
            </button>
        </div>
    </div>

    <!-- Modal de Filtro por Rango de Fechas -->
    <div x-show="modalFiltro" class="relative z-[60]" x-cloak>
        <div x-show="modalFiltro" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4">
            <div x-show="modalFiltro" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-3xl border border-neutral-200 p-7 max-w-md w-full shadow-2xl space-y-5"
                 @click.away="modalFiltro = false">
                
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900">Rango del Reporte</h3>
                        <p class="text-xs text-neutral-400" x-text="reporteTitulo"></p>
                    </div>
                    <button @click="modalFiltro = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form :action="reporteUrl" method="GET" target="_blank" @submit="modalFiltro = false" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" x-model="fechaInicio" required
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">Fecha de Fin</label>
                            <input type="date" name="fecha_fin" x-model="fechaFin" required
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                        <button type="button" @click="modalFiltro = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                            Generar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function reportesManager() {
        return {
            modalFiltro: false,
            reporteUrl: '',
            reporteTitulo: '',
            fechaInicio: '',
            fechaFin: '',

            init() {
                const hoy = new Date();
                const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                
                // Hack para zona horaria local de Tegucigalpa
                const offset = hoy.getTimezoneOffset() * 60000;
                const localHoy = new Date(hoy.getTime() - offset);
                const localPrimerDia = new Date(primerDia.getTime() - offset);

                this.fechaInicio = localPrimerDia.toISOString().split('T')[0];
                this.fechaFin = localHoy.toISOString().split('T')[0];
            },

            abrirModal(url, titulo) {
                this.reporteUrl = url;
                this.reporteTitulo = titulo;
                this.modalFiltro = true;
            }
        }
    }
</script>
@endpush
