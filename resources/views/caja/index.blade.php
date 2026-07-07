@extends('layouts.app')

@section('header_title', 'Tesorería')

@section('content')
<div x-data="tesoreriaBoard()" class="space-y-8">
    
    <!-- Encabezado y Acción -->
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Resumen Financiero</h2>
            <p class="text-neutral-500 text-sm mt-1">Control de caja y cuentas bancarias.</p>
        </div>
        <button @click="openModal = true" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Registrar Movimiento
        </button>
    </div>

    <!-- ═══ FILTROS DE FECHA ═══ -->
    <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm p-5">
        <form method="GET" action="{{ route('caja.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                       class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 text-sm focus:border-neutral-900 focus:outline-none shadow-sm transition-colors">
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-1.5">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                       class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 text-sm focus:border-neutral-900 focus:outline-none shadow-sm transition-colors">
            </div>
            <button type="submit" class="px-6 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Filtrar
            </button>
            <a href="{{ route('caja.index') }}" class="px-5 py-2.5 text-sm font-medium text-neutral-600 hover:text-neutral-900 border border-neutral-200 rounded-xl hover:bg-neutral-50 transition-colors">
                Limpiar
            </a>
        </form>
    </div>

    <!-- Tarjetas de Métricas (Grid Estricto CSS) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Tarjeta: Depósitos -->
        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm flex flex-col justify-between">
            <h3 class="text-sm font-medium text-neutral-500">Total Depósitos (Periodo)</h3>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-bold text-neutral-900"> L.{{ number_format($totalIngresos ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Tarjeta: Efectivo en Mano -->
        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute right-0 top-0 w-2 h-full bg-green-500"></div>
            <h3 class="text-sm font-medium text-neutral-500">Efectivo en Mano</h3>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-bold text-neutral-900"> L.{{ number_format($balanceEfectivo ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Tarjeta: Saldo en Bancos -->
        <div class="bg-white p-6 border border-neutral-100 rounded-2xl shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute right-0 top-0 w-2 h-full bg-blue-500"></div>
            <h3 class="text-sm font-medium text-neutral-500">Saldo en Bancos</h3>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-4xl font-bold text-neutral-900"> L.{{ number_format($balanceBancos ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden mt-8">
        <div class="px-6 py-5 border-b border-neutral-100 bg-[#FAFAFA] flex items-center justify-between">
            <h3 class="text-base font-semibold text-neutral-900">Movimientos del Periodo</h3>
            <span class="text-xs text-neutral-500 font-medium">{{ $movimientosHoy->count() }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-neutral-500 bg-white">
                    <tr>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100">Fecha</th>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100">Concepto</th>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100">Método</th>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100 text-right">Monto</th>
                        <th class="px-6 py-4 font-medium border-b border-neutral-100 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 bg-white">
                    @forelse($movimientosHoy ?? [] as $movimiento)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="px-6 py-4 text-neutral-500">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-neutral-900 font-medium">{{ $movimiento->concepto }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-600">
                                {{ $movimiento->metodo ?? 'Bancos' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold {{ $movimiento->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movimiento->tipo == 'ingreso' ? '+' : '-' }} L. {{ number_format($movimiento->monto, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                            <a href="{{ route('caja.ticket', $movimiento->id) }}" target="_blank" class="text-neutral-500 hover:text-neutral-900 inline-flex items-center gap-1 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Ticket
                            </a>

                            {{-- Botón Eliminar Venta POS (solo admin) --}}
                            @if(auth()->id() === 1 || auth()->user()->rol === 'admin')
                                @if(str_contains($movimiento->concepto, 'Venta POS #'))
                                    @php
                                        preg_match('/Venta POS #(\d+)/', $movimiento->concepto, $matches);
                                        $ventaId = $matches[1] ?? null;
                                    @endphp
                                    @if($ventaId)
                                        <button type="button" 
                                                @click="confirmarEliminarVenta({{ $ventaId }}, '{{ str_pad($ventaId, 5, '0', STR_PAD_LEFT) }}')"
                                                class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-lg border border-red-100 transition-colors shadow-sm">
                                            Eliminar Venta
                                        </button>
                                    @endif
                                @endif

                                {{-- Botón Eliminar Movimiento Genérico (solo admin) --}}
                                <button type="button" 
                                        @click="confirmarEliminarMovimiento({{ $movimiento->id }}, '{{ addslashes($movimiento->concepto) }}')"
                                        class="px-2.5 py-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white text-xs font-bold rounded-lg border border-red-100 hover:border-red-600 transition-all shadow-sm inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Eliminar
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-neutral-500">No hay movimientos registrados en este periodo.</td>
                    </tr>
                    @endforelse
                </tbody>
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

                    <form class="p-6 space-y-6" @submit.prevent="submitMovimiento">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-900">Tipo</label>
                                <select x-model="form.tipo" @change="checkReglas()" class="mt-2 w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors cursor-pointer">
                                    <option value="ingreso">Depósito</option>
                                    <option value="egreso">Retiro (Gasto)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-900">Cuenta / Método</label>
                                <select x-model="form.metodo" :disabled="form.tipo === 'egreso'" class="mt-2 w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors cursor-pointer disabled:bg-neutral-50 disabled:text-neutral-500">
                                    <option value="Efectivo">Efectivo en Mano</option>
                                    <option value="Bancos">Saldo en Bancos</option>
                                </select>
                                <p x-show="form.tipo === 'egreso'" class="text-xs text-neutral-500 mt-1">Los gastos operativos van a Bancos fijos.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-900">Monto (L.)</label>
                            <input type="number" step="0.01" x-model="form.monto" required class="mt-2 w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-900">Concepto</label>
                            <input type="text" x-model="form.concepto" required placeholder="Ej. Pago de servicios de luz" class="mt-2 w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-neutral-900 focus:border-neutral-900 focus:outline-none sm:text-sm shadow-sm transition-colors">
                        </div>

                        <div class="pt-2 flex justify-end gap-3">
                            <button type="button" @click="openModal = false" class="px-5 py-2.5 text-sm font-medium text-neutral-600 hover:text-neutral-900 transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm">
                                Guardar Movimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación de Venta POS -->
    <div x-show="modalEliminar" class="relative z-50" x-cloak>
        <div x-show="modalEliminar" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4">
            <div x-show="modalEliminar"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="modalEliminar = false"
                 class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-6">
                
                <div class="text-center space-y-3">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto text-red-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900">¿Estás seguro?</h3>
                    <p class="text-sm text-neutral-500">
                        Esta acción borrará la venta <span class="font-mono font-bold text-neutral-900">#VTA-<span x-text="ventaAEliminarNumero"></span></span> para siempre. 
                        Se revertirá el stock de los productos vendidos y se eliminará el movimiento de la sesión de caja.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="modalEliminar = false" class="flex-1 py-3 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors border border-neutral-200">
                        No, mantener
                    </button>
                    <button type="button" @click="ejecutarEliminacion()" :disabled="cargandoEliminacion"
                            class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm disabled:opacity-50">
                        <span x-show="!cargandoEliminacion">Eliminar Venta (Definitivo)</span>
                        <span x-show="cargandoEliminacion">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación de Movimiento Genérico -->
    <div x-show="modalEliminarMov" class="relative z-50" x-cloak>
        <div x-show="modalEliminarMov" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4">
            <div x-show="modalEliminarMov"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="modalEliminarMov = false"
                 class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-6">
                
                <div class="text-center space-y-3">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto text-red-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900">¿Eliminar Movimiento?</h3>
                    <p class="text-sm text-neutral-500">
                        Estás a punto de borrar permanentemente el movimiento: 
                        <span class="font-bold text-neutral-900" x-text="movAEliminarConcepto"></span>.
                        <br>Esta acción no se puede deshacer y afectará los balances de caja.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="modalEliminarMov = false" class="flex-1 py-3 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors border border-neutral-200">
                        No, mantener
                    </button>
                    <button type="button" @click="ejecutarEliminacionMov()" :disabled="cargandoEliminacionMov"
                            class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm disabled:opacity-50">
                        <span x-show="!cargandoEliminacionMov">Eliminar Definitivamente</span>
                        <span x-show="cargandoEliminacionMov">Eliminando...</span>
                    </button>
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
            form: {
                tipo: 'ingreso',
                metodo: 'Efectivo',
                monto: '',
                concepto: ''
            },
            // Eliminar Venta POS
            modalEliminar: false,
            ventaAEliminarId: null,
            ventaAEliminarNumero: '',
            cargandoEliminacion: false,
            // Eliminar Movimiento Genérico
            modalEliminarMov: false,
            movAEliminarId: null,
            movAEliminarConcepto: '',
            cargandoEliminacionMov: false,

            checkReglas() {
                // Regla Innegociable: Los Retiros se descuentan de Bancos
                if (this.form.tipo === 'egreso') {
                    this.form.metodo = 'Bancos';
                }
            },

            // ─── Eliminar Venta POS ───
            confirmarEliminarVenta(id, numero) {
                this.ventaAEliminarId = id;
                this.ventaAEliminarNumero = numero;
                this.modalEliminar = true;
            },
            async ejecutarEliminacion() {
                if (this.cargandoEliminacion || !this.ventaAEliminarId) return;
                this.cargandoEliminacion = true;
                try {
                    const res = await fetch(`/pos/venta/${this.ventaAEliminarId}/eliminar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar la venta.');
                    }
                } catch (e) {
                    alert('Error de conexión al servidor.');
                } finally {
                    this.cargandoEliminacion = false;
                    this.modalEliminar = false;
                }
            },

            // ─── Eliminar Movimiento Genérico ───
            confirmarEliminarMovimiento(id, concepto) {
                this.movAEliminarId = id;
                this.movAEliminarConcepto = concepto;
                this.modalEliminarMov = true;
            },
            async ejecutarEliminacionMov() {
                if (this.cargandoEliminacionMov || !this.movAEliminarId) return;
                this.cargandoEliminacionMov = true;
                try {
                    const res = await fetch(`/caja/${this.movAEliminarId}/eliminar`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar el movimiento.');
                    }
                } catch (e) {
                    alert('Error de conexión al servidor.');
                } finally {
                    this.cargandoEliminacionMov = false;
                    this.modalEliminarMov = false;
                }
            },

            async submitMovimiento() {
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
                }
            }
        }
    }
</script>
@endpush
