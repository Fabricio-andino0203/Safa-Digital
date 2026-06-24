@extends('layouts.pos')

{{-- ══════════════════════════════════════════════════════════════════════════
     SAFA DIGITAL — CORTE DE CAJA
     Vista resumen del turno antes de cerrar definitivamente
══════════════════════════════════════════════════════════════════════════ --}}

@section('pos_header_actions')
    <a href="{{ route('pos.index') }}"
       class="px-3.5 py-1.5 text-xs font-semibold border border-neutral-200 rounded-xl text-neutral-600 hover:bg-neutral-50 transition-all">
        ← Volver al POS
    </a>
@endsection

@section('pos_content')
<div
    x-data="corteApp()"
    class="h-full overflow-y-auto"
>
    <div class="max-w-3xl mx-auto px-6 py-10 space-y-8">

        {{-- Encabezado --}}
        <div class="text-center">
            <div class="w-14 h-14 bg-neutral-900 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-neutral-900">Corte de Caja</h1>
            <p class="text-sm text-neutral-500 mt-1">
                Turno abierto el {{ $sesion->fecha_apertura->format('d/m/Y') }}
                a las {{ $sesion->fecha_apertura->format('H:i') }}
            </p>
        </div>

        {{-- Resumen de Ventas por Método de Pago --}}
        <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 bg-[#FAFAFA]">
                <h2 class="text-sm font-semibold text-neutral-900">Ventas por Método de Pago</h2>
            </div>

            <div class="divide-y divide-neutral-50">
                @php
                    $labelMetodos = [
                        'efectivo'      => ['label' => 'Efectivo',      'icon' => '💵'],
                        'tarjeta'       => ['label' => 'Tarjeta',       'icon' => '💳'],
                        'transferencia' => ['label' => 'Transferencia', 'icon' => '🏦'],
                        'mixto'         => ['label' => 'Mixto',         'icon' => '🔀'],
                    ];
                @endphp

                @forelse($ventasPorMetodo as $metodo => $monto)
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="text-lg">{{ $labelMetodos[$metodo]['icon'] ?? '💰' }}</span>
                        <span class="text-sm font-medium text-neutral-700">{{ $labelMetodos[$metodo]['label'] ?? ucfirst($metodo) }}</span>
                    </div>
                    <span class="text-base font-bold text-neutral-900">${{ number_format($monto, 2) }}</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-neutral-400 text-sm">
                    No hay ventas registradas en este turno.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Cuadro de Totales --}}
        <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 bg-[#FAFAFA]">
                <h2 class="text-sm font-semibold text-neutral-900">Resumen del Turno</h2>
            </div>
            <div class="divide-y divide-neutral-50">
                <div class="flex justify-between px-6 py-4">
                    <span class="text-sm text-neutral-500">Monto Inicial (Apertura)</span>
                    <span class="text-sm font-medium text-neutral-900">${{ number_format($sesion->monto_inicial, 2) }}</span>
                </div>
                <div class="flex justify-between px-6 py-4">
                    <span class="text-sm text-neutral-500">Total Ventas del Turno</span>
                    <span class="text-sm font-medium text-green-600">+${{ number_format($totalVentas, 2) }}</span>
                </div>
                <div class="flex justify-between px-6 py-4">
                    <span class="text-sm text-neutral-500">Retiros / Egresos del Turno</span>
                    <span class="text-sm font-medium text-red-500">−${{ number_format($retiros, 2) }}</span>
                </div>
                <div class="flex justify-between px-6 py-5 bg-neutral-50">
                    <span class="text-base font-bold text-neutral-900">Total Esperado en Caja</span>
                    <span class="text-xl font-bold text-neutral-900">${{ number_format($totalEsperado, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Conteo Físico y Diferencia --}}
        <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 bg-[#FAFAFA]">
                <h2 class="text-sm font-semibold text-neutral-900">Conteo Físico del Efectivo</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Cuenta el dinero físico y regístralo aquí para calcular la diferencia.</p>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">Monto Contado Físicamente ($)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 font-medium">$</span>
                        <input
                            type="number"
                            x-model.number="montoContado"
                            min="0"
                            step="0.01"
                            @focus="$event.target.select()"
                            class="w-full pl-8 pr-4 py-4 border border-neutral-200 rounded-2xl text-2xl font-bold text-center text-neutral-900 focus:outline-none focus:border-neutral-400 bg-neutral-50 transition-colors"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                {{-- Diferencia calculada en tiempo real --}}
                <div
                    x-show="montoContado > 0"
                    x-transition
                    class="rounded-2xl p-5 border flex items-center justify-between"
                    :class="diferencia >= 0
                        ? 'bg-green-50 border-green-100'
                        : 'bg-red-50 border-red-100'"
                >
                    <div>
                        <p class="text-sm font-semibold"
                           :class="diferencia >= 0 ? 'text-green-700' : 'text-red-700'"
                           x-text="diferencia >= 0 ? '✓ Sobrante' : '⚠ Faltante'">
                        </p>
                        <p class="text-xs mt-0.5"
                           :class="diferencia >= 0 ? 'text-green-600' : 'text-red-600'"
                           x-text="diferencia >= 0
                               ? 'Hay más efectivo del esperado'
                               : 'Hay menos efectivo del esperado'">
                        </p>
                    </div>
                    <span class="text-3xl font-bold"
                          :class="diferencia >= 0 ? 'text-green-700' : 'text-red-700'"
                          x-text="(diferencia >= 0 ? '+' : '') + '$' + Math.abs(diferencia).toFixed(2)">
                    </span>
                </div>

                {{-- Notas --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Notas del Turno (Opcional)</label>
                    <textarea
                        x-model="notas"
                        rows="3"
                        placeholder="Ej. Billete de $500 falso detectado, cliente devolvió producto..."
                        class="w-full border border-neutral-200 rounded-xl px-4 py-3 text-sm text-neutral-900 focus:outline-none focus:border-neutral-400 bg-neutral-50 transition-colors resize-none"
                    ></textarea>
                </div>

                {{-- Error --}}
                <div x-show="error" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="error"></div>

                {{-- Botón cerrar caja --}}
                <button
                    @click="cerrarCaja()"
                    :disabled="cargando || montoContado <= 0"
                    class="w-full py-4 bg-neutral-900 text-white font-bold rounded-2xl hover:bg-neutral-800 active:scale-[0.98] transition-all shadow-sm disabled:opacity-40 flex items-center justify-center gap-2"
                >
                    <svg x-show="cargando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="cargando ? 'Cerrando turno...' : '🔒 Cerrar Caja y Terminar Turno'"></span>
                </button>
            </div>
        </div>

        {{-- Lista de ventas del turno (colapsable) --}}
        @if($ventas->count() > 0)
        <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-neutral-100 bg-[#FAFAFA] flex items-center justify-between">
                <h2 class="text-sm font-semibold text-neutral-900">
                    Ventas del Turno
                    <span class="ml-2 text-xs font-medium bg-neutral-200 text-neutral-600 px-2 py-0.5 rounded-full">{{ $ventas->count() }}</span>
                </h2>
            </div>
            <div class="divide-y divide-neutral-50 max-h-64 overflow-y-auto">
                @foreach($ventas as $venta)
                <div class="flex items-center justify-between px-6 py-3.5">
                    <div>
                        <span class="text-xs font-mono text-neutral-400">#{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="ml-3 text-xs font-medium bg-neutral-100 text-neutral-600 px-2 py-0.5 rounded-lg">
                            {{ ['efectivo'=>'💵','tarjeta'=>'💳','transferencia'=>'🏦','mixto'=>'🔀'][$venta->metodo_pago] ?? '' }}
                            {{ $venta->label_metodo_pago }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-neutral-900">${{ number_format($venta->total, 2) }}</span>
                        <span class="block text-xs text-neutral-400">{{ $venta->created_at->format('H:i') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('pos_scripts')
<script>
function corteApp() {
    return {
        sesionId: {{ $sesion->id }},
        totalEsperado: {{ $totalEsperado }},
        montoContado: 0,
        notas: '',
        cargando: false,
        error: '',

        get diferencia() {
            return this.montoContado - this.totalEsperado;
        },

        async cerrarCaja() {
            if (this.cargando || this.montoContado <= 0) return;
            this.error = '';
            this.cargando = true;

            try {
                const res = await fetch('{{ route('pos.cerrarSesion') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        caja_sesion_id:       this.sesionId,
                        monto_contado_fisico: this.montoContado,
                        notas:                this.notas,
                    })
                });

                const data = await res.json();

                if (data.success) {
                    window.location.href = data.redirect || '{{ route('pos.index') }}';
                } else {
                    this.error = data.message || 'Error al cerrar la caja.';
                }
            } catch (e) {
                this.error = 'Error de conexión. Verifica el servidor.';
            } finally {
                this.cargando = false;
            }
        }
    };
}
</script>
@endpush
