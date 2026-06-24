@extends('layouts.app')

@section('header_title', 'Reportes y Métricas')

@section('content')
<div x-data="{ tabActivo: 'diario' }" class="space-y-8">
    
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Rendimiento Financiero</h2>
            <p class="text-neutral-500 text-sm mt-1">Análisis de ventas, gastos y rentabilidad.</p>
        </div>
    </div>

    <!-- Navegación de Pestañas (Tabs) Alpine.js -->
    <div class="border-b border-neutral-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="tabActivo = 'diario'" 
                    :class="tabActivo === 'diario' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Reporte Diario
            </button>
            <button @click="tabActivo = 'mensual'" 
                    :class="tabActivo === 'mensual' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Reporte Mensual
            </button>
            <button @click="tabActivo = 'productos'" 
                    :class="tabActivo === 'productos' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Productos Top
            </button>
        </nav>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="mt-8">
        
        <!-- Tab: Diario -->
        <div x-show="tabActivo === 'diario'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <!-- Tarjetas Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-neutral-500">Ventas (Depósitos Hoy)</h3>
                    <div class="mt-4">
                        <span class="text-4xl font-bold text-neutral-900">${{ number_format($ventasHoy ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-neutral-500">Gastos (Retiros Hoy)</h3>
                    <div class="mt-4">
                        <span class="text-4xl font-bold text-neutral-900">${{ number_format($gastosHoy ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="bg-[#FAFAFA] p-6 border border-neutral-200 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-neutral-500">Ganancia Neta</h3>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="text-4xl font-bold text-neutral-900">${{ number_format(($ventasHoy ?? 0) - ($gastosHoy ?? 0), 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-8 border border-neutral-100 rounded-2xl shadow-sm h-72 flex items-center justify-center text-neutral-400">
                [ Gráfico de Flujo Diario - Espacio Reservado ]
            </div>
        </div>

        <!-- Tab: Mensual -->
        <div x-show="tabActivo === 'mensual'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-neutral-500">Ventas (Mes Actual)</h3>
                    <div class="mt-4">
                        <span class="text-4xl font-bold text-neutral-900">${{ number_format($ventasMes ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-neutral-500">Gastos (Mes Actual)</h3>
                    <div class="mt-4">
                        <span class="text-4xl font-bold text-neutral-900">${{ number_format($gastosMes ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="bg-[#FAFAFA] p-6 border border-neutral-200 rounded-2xl shadow-sm">
                    <h3 class="text-sm font-medium text-neutral-500">Ganancia Neta Mensual</h3>
                    <div class="mt-4">
                        <span class="text-4xl font-bold text-neutral-900">${{ number_format(($ventasMes ?? 0) - ($gastosMes ?? 0), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Productos Top -->
        <div x-show="tabActivo === 'productos'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-neutral-100 bg-[#FAFAFA]">
                    <h3 class="text-base font-semibold text-neutral-900">Productos Más Vendidos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-neutral-500 bg-white border-b border-neutral-100">
                            <tr>
                                <th class="px-6 py-4 font-medium">Posición</th>
                                <th class="px-6 py-4 font-medium">Producto</th>
                                <th class="px-6 py-4 font-medium text-right">Cantidades Vendidas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-6 py-4 text-neutral-500">#1</td>
                                <td class="px-6 py-4 text-neutral-900 font-medium">Camisas DTF Negras</td>
                                <td class="px-6 py-4 text-right font-bold text-neutral-900">1,240</td>
                            </tr>
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-6 py-4 text-neutral-500">#2</td>
                                <td class="px-6 py-4 text-neutral-900 font-medium">Cobertores Personalizados</td>
                                <td class="px-6 py-4 text-right font-bold text-neutral-900">890</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
