@extends('layouts.app')

@section('header_title', 'Directorio de Clientes')

@section('content')
<div x-data="{ openModal: false }" class="space-y-8">
    
    <!-- Encabezado y Acción -->
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Cartera de Clientes</h2>
            <p class="text-neutral-500 text-sm mt-1">Gestión de contactos e historial de pedidos.</p>
        </div>
        <button @click="openModal = true" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nuevo Cliente
        </button>
    </div>

    <!-- Tarjetas de KPIs (Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm">
            <h3 class="text-sm font-medium text-neutral-500">Total de Clientes Registrados</h3>
            <div class="mt-4">
                <span class="text-4xl font-bold text-neutral-900">{{ $totalClientes ?? 0 }}</span>
            </div>
        </div>

        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm md:col-span-2">
            <h3 class="text-sm font-medium text-neutral-500">Mejor Cliente (Por volumen de compra)</h3>
            <div class="mt-4 flex flex-col sm:flex-row sm:items-baseline gap-2">
                <span class="text-4xl font-bold text-neutral-900">{{ $mejorCliente->nombre ?? 'N/A' }}</span>
                <span class="text-neutral-500 font-medium">con un gasto total de <span class="text-green-600 font-bold">${{ number_format($mejorCliente->total_gastado ?? 0, 2) }}</span></span>
            </div>
        </div>
    </div>

    <!-- Tabla Limpia de Clientes -->
    <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden mt-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-neutral-500 bg-white">
                    <tr>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100">Nombre del Cliente</th>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100">Teléfono</th>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100">Email</th>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100 text-right">Total Gastado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 bg-white">
                    @forelse($clientes ?? [] as $cliente)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="px-6 py-4 text-neutral-900 font-medium">{{ $cliente->nombre }}</td>
                        <td class="px-6 py-4 text-neutral-500">{{ $cliente->telefono ?? '-' }}</td>
                        <td class="px-6 py-4 text-neutral-500">{{ $cliente->email ?? '-' }}</td>
                        <td class="px-6 py-4 text-right font-bold text-neutral-900">
                            ${{ number_format($cliente->total_gastado, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-neutral-500">No hay clientes registrados en el sistema.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Alpine.js para Nuevo Cliente (Sin Lógica de Crédito) -->
    <div x-show="openModal" class="relative z-50" style="display: none;">
        <div x-show="openModal" x-transition.opacity class="fixed inset-0 bg-neutral-900/20 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="openModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-neutral-100">
                    
                    <div class="px-6 py-5 border-b border-neutral-100 flex justify-between items-center bg-[#FAFAFA]">
                        <h3 class="text-lg font-semibold text-neutral-900">Registrar Cliente</h3>
                        <button type="button" @click="openModal = false" class="text-neutral-400 hover:text-neutral-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form class="p-6 space-y-6" action="#" method="POST">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-neutral-900">Nombre Completo o Empresa</label>
                            <input type="text" name="nombre" required class="mt-2 w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-900">Teléfono</label>
                                <input type="text" name="telefono" class="mt-2 w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-900">Email</label>
                                <input type="email" name="email" class="mt-2 w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors">
                            </div>
                        </div>

                        <!-- Nota aclaratoria según reglas de negocio -->
                        <div class="bg-[#FAFAFA] p-4 rounded-xl border border-neutral-100">
                            <p class="text-xs text-neutral-500">Nota: Safa Digital no administra créditos directos. Los pagos parciales o totales se gestionarán independientemente en cada pedido creado.</p>
                        </div>

                        <div class="pt-2 flex justify-end gap-3">
                            <button type="button" @click="openModal = false" class="px-5 py-2.5 text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm">
                                Crear Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
