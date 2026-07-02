@extends('layouts.app')

@section('header_title', 'Tesorería')

@section('content')
<div x-data="tesoreriaBoard()" class="space-y-8">
    
    <!-- Encabezado y Acción -->
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Resumen Financiero</h2>
            <p class="text-neutral-500 text-sm mt-1">
                @if($sesion)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Sesión activa desde {{ $sesion->fecha_apertura->format('d/m/Y H:i') }}
                    </span>
                @else
                    Control de caja y cuentas bancarias.
                @endif
            </p>
        </div>
        <button @click="openModal = true" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Registrar Movimiento
        </button>
    </div>

    <!-- Tarjetas de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Depósitos -->
        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute right-0 top-0 w-1.5 h-full bg-green-400 rounded-r-2xl"></div>
            <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-widest">Total Depósitos {{ $sesion ? '(Sesión Actual)' : '(Hoy)' }}</h3>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-neutral-900">L.{{ number_format($totalIngresos ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Efectivo en Mano -->
        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute right-0 top-0 w-1.5 h-full bg-neutral-900 rounded-r-2xl"></div>
            <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-widest">Efectivo en Mano</h3>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-neutral-900">L.{{ number_format($balanceEfectivo ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Retiros -->
        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute right-0 top-0 w-1.5 h-full bg-red-400 rounded-r-2xl"></div>
            <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-widest">Total Retiros {{ $sesion ? '(Sesión Actual)' : '(Hoy)' }}</h3>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-neutral-900">L.{{ number_format($totalEgresos ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos de la Sesión -->
    <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-neutral-100 bg-[#FAFAFA] flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-neutral-900">Movimientos de Caja</h3>
                <p class="text-xs text-neutral-400 mt-0.5">
                    {{ $sesion ? 'Historial completo de la sesión abierta' : 'Movimientos del día de hoy' }}
                </p>
            </div>
            <span class="text-xs font-medium text-neutral-500 bg-neutral-100 px-3 py-1.5 rounded-full">
                {{ count($movimientosHoy ?? []) }} registro(s)
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-neutral-500 bg-[#FAFAFA]">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Fecha / Hora</th>
                        <th class="px-6 py-3.5 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Tipo</th>
                        <th class="px-6 py-3.5 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Concepto</th>
                        <th class="px-6 py-3.5 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100">Método</th>
                        <th class="px-6 py-3.5 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100 text-right">Monto</th>
                        <th class="px-6 py-3.5 font-semibold text-xs uppercase tracking-wider border-b border-neutral-100 text-center">Recibo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-50 bg-white">
                    @forelse($movimientosHoy ?? [] as $movimiento)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="px-6 py-4 text-neutral-500 text-xs font-mono">
                            {{ $movimiento->fecha->format('d/m/Y') }}
                            <span class="block text-neutral-400">{{ $movimiento->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($movimiento->tipo === 'ingreso')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    Depósito
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                    Retiro
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-neutral-900 font-medium max-w-xs truncate">{{ $movimiento->concepto }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-600">
                                {{ $movimiento->referencia ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-base {{ $movimiento->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movimiento->tipo == 'ingreso' ? '+' : '-' }} L.{{ number_format($movimiento->monto, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('caja.ticket', $movimiento->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-neutral-600 bg-neutral-100 hover:bg-neutral-900 hover:text-white rounded-lg transition-colors"
                               title="Reimprimir Recibo">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Reimprimir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-neutral-400">
                            <svg class="w-8 h-8 mx-auto mb-2 text-neutral-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm font-medium">No hay movimientos en esta sesión.</p>
                            <p class="text-xs text-neutral-300 mt-1">Las ventas y movimientos manuales aparecerán aquí.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($movimientosHoy ?? []) > 0)
                <tfoot class="bg-[#FAFAFA] border-t border-neutral-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-sm font-bold text-neutral-700 text-right">Balance Neto:</td>
                        <td class="px-6 py-4 text-right text-base font-bold {{ ($totalIngresos - $totalEgresos) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            L.{{ number_format($totalIngresos - $totalEgresos, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Modal Alpine.js para Registrar Movimiento -->
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
                        <h3 class="text-lg font-semibold text-neutral-900">Registrar Movimiento</h3>
                        <button type="button" @click="openModal = false" class="text-neutral-400 hover:text-neutral-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form class="p-6 space-y-5" @submit.prevent="submitMovimiento">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Tipo</label>
                                <select x-model="form.tipo" @change="checkReglas()" class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm transition-colors">
                                    <option value="ingreso">Depósito</option>
                                    <option value="egreso">Retiro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Método</label>
                                <select x-model="form.metodo" :disabled="form.tipo === 'egreso'" class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm transition-colors disabled:bg-neutral-50 disabled:text-neutral-400">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Monto (L.)</label>
                            <input type="number" step="0.01" x-model="form.monto" required min="0.01" class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Concepto</label>
                            <input type="text" x-model="form.concepto" required placeholder="Ej. Pago de servicios de luz" class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm transition-colors">
                        </div>

                        <div class="pt-2 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="openModal = false" class="px-5 py-2.5 text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="guardando" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-semibold rounded-xl hover:bg-neutral-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2">
                                <span x-show="guardando" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function tesoreriaBoard() {
        return {
            openModal: false,
            guardando: false,
            form: {
                tipo: 'ingreso',
                metodo: 'Efectivo',
                monto: '',
                concepto: ''
            },
            checkReglas() {
                if (this.form.tipo === 'egreso') {
                    this.form.metodo = 'Bancos';
                }
            },
            async submitMovimiento() {
                this.guardando = true;
                try {
                    const res = await fetch('{{ route('caja.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.openModal = false;
                        if (data.ticket_url) {
                            window.open(data.ticket_url, '_blank');
                        }
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al registrar el movimiento.');
                    }
                } catch(e) {
                    alert('Error de conexión al servidor.');
                } finally {
                    this.guardando = false;
                }
            }
        }
    }
</script>
@endpush
