@extends('layouts.app')

@section('header_title', 'Gestión de Inventario')

@section('content')
<div x-data="{ tabActivo: 'productos' }" class="space-y-8">
    
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Almacén Central</h2>
            <p class="text-neutral-500 text-sm mt-1">Control visual de artículos y materias primas.</p>
        </div>
        <button class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Añadir Ítem
        </button>
    </div>

    <!-- Pestañas Alpine.js -->
    <div class="border-b border-neutral-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="tabActivo = 'productos'" 
                    :class="tabActivo === 'productos' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Productos Terminados
            </button>
            <button @click="tabActivo = 'materiales'" 
                    :class="tabActivo === 'materiales' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                Materiales e Insumos
            </button>
        </nav>
    </div>

    <!-- Tab: Productos -->
    <div x-show="tabActivo === 'productos'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-neutral-500 bg-white">
                        <tr>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100">SKU</th>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100">Nombre del Producto</th>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100 text-right">Precio</th>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100 text-center">Stock Actual</th>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 bg-white">
                        @forelse($productos ?? [] as $item)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4 text-neutral-400 font-mono text-xs">{{ $item->sku ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-neutral-900 font-medium">{{ $item->nombre }}</td>
                            <td class="px-6 py-4 text-right text-neutral-900">${{ number_format($item->precio, 2) }}</td>
                            <!-- Lógica visual de Stock Mínimo -->
                            <td class="px-6 py-4 text-center font-bold {{ $item->stock <= $item->stock_minimo ? 'text-red-600 bg-red-50' : 'text-neutral-900' }}">
                                {{ $item->stock }}
                            </td>
                            <td class="px-6 py-4">
                                @if($item->stock <= $item->stock_minimo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Bajo Stock</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Óptimo</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-neutral-500">No hay productos terminados registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Materiales -->
    <div x-show="tabActivo === 'materiales'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-neutral-500 bg-white">
                        <tr>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100">Referencia</th>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100">Nombre del Material (Insumo)</th>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100 text-center">Cantidad Disponible</th>
                            <th class="px-6 py-4 font-medium border-b border-neutral-100">Alerta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 bg-white">
                        @forelse($materiales ?? [] as $item)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4 text-neutral-400 font-mono text-xs">{{ $item->sku ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-neutral-900 font-medium">{{ $item->nombre }}</td>
                            <td class="px-6 py-4 text-center font-bold {{ $item->stock <= $item->stock_minimo ? 'text-red-600 bg-red-50' : 'text-neutral-900' }}">
                                {{ $item->stock }}
                            </td>
                            <td class="px-6 py-4">
                                @if($item->stock <= $item->stock_minimo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Reabastecer</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Suficiente</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-neutral-500">No hay materiales registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
