<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #{{ $pedido->numero_orden }} · {{ get_setting('nombre_comercial', 'Safa Digital') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #F9FAFB; }
    </style>
</head>
<body class="text-neutral-800 antialiased min-h-screen">

    <div class="max-w-lg mx-auto px-4 py-8 space-y-4">

        <!-- Logo / Encabezado -->
        <div class="flex justify-center mb-6">
            @php $logo = get_setting('logo_ruta'); @endphp
            @if($logo)
                <img src="{{ asset($logo) }}" alt="Logo" class="h-12 object-contain">
            @else
                <h1 class="text-xl font-bold tracking-tight">{{ get_setting('nombre_comercial', 'Safa Digital') }}</h1>
            @endif
        </div>

        <!-- Número de orden + Fecha + Badge de Estado de Pago -->
        @php
            $totalPedido    = (float) ($pedido->total_pedido ?? $pedido->total ?? 0);
            $totalAbonado   = (float) ($pedido->total_abonado ?? 0);
            $saldoPendiente = (float) ($pedido->saldo_pendiente ?? ($totalPedido - $totalAbonado));

            if ($saldoPendiente <= 0) {
                $badgeClass = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                $badgeIcon  = '✅';
                $badgeText  = 'Liquidado';
            } elseif ($totalAbonado > 0) {
                $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                $badgeIcon  = '⚡';
                $badgeText  = 'Pago Parcial';
            } else {
                $badgeClass = 'bg-red-50 text-red-700 border border-red-200';
                $badgeIcon  = '⏳';
                $badgeText  = 'Pago Pendiente';
            }
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-neutral-400 uppercase tracking-widest">Orden</p>
                    <div class="text-3xl font-extrabold text-neutral-900 mt-0.5">#{{ $pedido->numero_orden }}</div>
                    <p class="text-xs text-neutral-400 mt-1.5">
                        {{ $pedido->created_at->timezone('America/Tegucigalpa')->format('d/m/Y h:i A') }}
                    </p>
                    @if($pedido->cliente)
                    <p class="text-sm font-medium text-neutral-600 mt-1">{{ $pedido->cliente->nombre }}</p>
                    @endif
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold {{ $badgeClass }}">
                    {{ $badgeIcon }} {{ $badgeText }}
                </span>
            </div>
        </div>

        <!-- Estado actual del pedido -->
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6">
            <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-4">Estado Actual</h3>

            @php
                $est = strtoupper($pedido->estado);
                $estadoColor = 'bg-neutral-100 text-neutral-500';
                $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />';

                if (in_array($est, ['PENDIENTE', 'NUEVO'])) {
                    $estadoColor = 'bg-blue-50 text-blue-600';
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />';
                } elseif (in_array($est, ['DISEÑO', 'ESPERANDO APROBACIÓN'])) {
                    $estadoColor = 'bg-indigo-50 text-indigo-600';
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />';
                } elseif (in_array($est, ['PRODUCCIÓN', 'EN PRODUCCIÓN'])) {
                    $estadoColor = 'bg-amber-50 text-amber-600';
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />';
                } elseif (in_array($est, ['LISTO PARA ENTREGA', 'LISTO'])) {
                    $estadoColor = 'bg-emerald-50 text-emerald-600';
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />';
                } elseif ($est == 'ENTREGADO') {
                    $estadoColor = 'bg-neutral-100 text-neutral-600';
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />';
                } elseif (in_array($est, ['CANCELADO', 'ANULADO'])) {
                    $estadoColor = 'bg-red-50 text-red-600';
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />';
                }
            @endphp

            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full {{ $estadoColor }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        {!! $icon !!}
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-bold text-neutral-900">{{ $pedido->estado }}</div>
                    @if($pedido->fecha_estimada_entrega)
                    <div class="text-sm text-neutral-400 mt-0.5">
                        Entrega estimada: {{ $pedido->fecha_estimada_entrega->format('d/m/Y') }}
                        @if($pedido->hora_estimada_entrega)
                            {{ \Carbon\Carbon::parse($pedido->hora_estimada_entrega)->format('h:i A') }}
                        @endif
                    </div>
                    @else
                    <div class="text-sm text-neutral-400 mt-0.5">Fecha de entrega pendiente</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Productos del pedido -->
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6">
            <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-4">Productos</h3>
            <ul class="divide-y divide-neutral-50">
                @foreach($pedido->detalles as $detalle)
                @php
                    $nombreProducto = '';
                    $nombreVariante = '';

                    if ($detalle->tipo_producto === 'Inventario' && $detalle->variante) {
                        $nombreProducto = $detalle->variante->producto->nombre ?? $detalle->variante->nombre_completo ?? '—';
                        $nombreVariante = ($detalle->variante->nombre ?? '') !== 'Única' ? ($detalle->variante->nombre ?? '') : '';
                    } else {
                        $nombreProducto = $detalle->nombre_libre ?? 'Ítem personalizado';
                        $nombreVariante = $detalle->descripcion_libre ?? '';
                    }

                    $precioUnitario = (float) ($detalle->precio_venta ?? $detalle->precio_unitario ?? 0);
                    $subtotalLinea  = $precioUnitario * (int) $detalle->cantidad;

                    // Extras aplicados
                    $extras = is_array($detalle->extras) ? $detalle->extras : (json_decode($detalle->extras ?? '[]', true) ?: []);
                    $extrasPrecio = collect($extras)->sum('precio');
                @endphp
                <li class="py-3.5">
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-neutral-900 text-sm">{{ $nombreProducto }}</p>
                            @if($nombreVariante)
                                <p class="text-xs text-neutral-400 mt-0.5">{{ $nombreVariante }}</p>
                            @endif
                            @if(count($extras) > 0)
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($extras as $ex)
                                        <span class="text-[10px] bg-neutral-100 text-neutral-500 px-1.5 py-0.5 rounded font-medium">
                                            + {{ $ex['nombre'] ?? '' }} (L.{{ number_format($ex['precio'] ?? 0, 2) }})
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-neutral-400">{{ $detalle->cantidad }} × L.{{ number_format($precioUnitario, 2) }}</p>
                            <p class="text-sm font-bold text-neutral-900 mt-0.5">L.{{ number_format($subtotalLinea, 2) }}</p>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Resumen financiero -->
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6">
            <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-4">Resumen de Pago</h3>

            @php
                $subtotal  = (float) ($pedido->subtotal ?? $totalPedido);
                $descuento = (float) ($pedido->descuento ?? 0);
            @endphp

            <div class="space-y-2.5">
                <div class="flex justify-between text-sm text-neutral-500">
                    <span>Subtotal</span>
                    <span>L.{{ number_format($subtotal, 2) }}</span>
                </div>

                @if($descuento > 0)
                <div class="flex justify-between text-sm text-red-500">
                    <span>Descuento</span>
                    <span>− L.{{ number_format($descuento, 2) }}</span>
                </div>
                @endif

                <div class="flex justify-between text-base font-bold text-neutral-900 pt-2.5 border-t border-neutral-100">
                    <span>Total del Pedido</span>
                    <span>L.{{ number_format($totalPedido, 2) }}</span>
                </div>

                <div class="flex justify-between text-sm {{ $totalAbonado > 0 ? 'text-emerald-600 font-semibold' : 'text-neutral-400' }}">
                    <span>Total Abonado</span>
                    <span>L.{{ number_format($totalAbonado, 2) }}</span>
                </div>

                <div class="flex justify-between items-center text-lg font-black pt-3 border-t border-neutral-200 {{ $saldoPendiente <= 0 ? 'text-emerald-600' : 'text-neutral-900' }}">
                    <span>{{ $saldoPendiente <= 0 ? '✅ Liquidado' : 'Saldo Pendiente' }}</span>
                    <span>L.{{ number_format(max(0, $saldoPendiente), 2) }}</span>
                </div>
            </div>

            <!-- Badge resumen visual -->
            <div class="mt-4 flex justify-center">
                <span class="w-full text-center py-2.5 px-4 rounded-xl text-sm font-bold {{ $badgeClass }}">
                    {{ $badgeIcon }} {{ $badgeText }}
                    @if($saldoPendiente > 0 && $totalAbonado > 0)
                        — Abonado L.{{ number_format($totalAbonado, 2) }} de L.{{ number_format($totalPedido, 2) }}
                    @endif
                </span>
            </div>
        </div>

        <!-- Historial de Progreso -->
        @if($pedido->historiales && $pedido->historiales->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6">
            <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-5 text-center">Historial de Progreso</h3>
            <div class="relative border-l border-neutral-200 ml-4 space-y-5">
                @foreach($pedido->historiales->sortByDesc('created_at') as $historial)
                <div class="relative pl-6">
                    @php
                        $circleColor = $loop->first ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-neutral-300';
                    @endphp
                    <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full {{ $circleColor }} border-2 border-white"></div>
                    <span class="text-sm font-bold text-neutral-800">{{ $historial->estado_nuevo }}</span>
                    <span class="block text-xs text-neutral-400 mt-0.5">
                        {{ $historial->created_at->timezone('America/Tegucigalpa')->format('d/m/Y h:i A') }}
                    </span>
                    @if($historial->nota)
                        <span class="block text-xs text-neutral-500 mt-0.5 italic">{{ $historial->nota }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center py-4">
            <p class="text-xs text-neutral-400">{{ get_setting('nombre_comercial', 'Safa Digital') }}</p>
            <p class="text-xs text-neutral-300 mt-0.5">{{ get_setting('telefono', '') }} · {{ get_setting('direccion', '') }}</p>
        </div>

    </div>

</body>
</html>
