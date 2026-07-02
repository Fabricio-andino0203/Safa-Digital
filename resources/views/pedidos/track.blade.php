<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreo de Pedido - {{ get_setting('nombre_comercial', 'Safa Digital') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F9FAFB] text-neutral-800 antialiased min-h-screen">
    
    <div class="max-w-lg mx-auto px-4 py-8">
        
        <!-- Encabezado / Logo -->
        <div class="flex justify-center mb-8">
            @php $logo = get_setting('logo_ruta'); @endphp
            @if($logo)
                <img src="{{ asset($logo) }}" alt="Logo" class="h-12 object-contain">
            @else
                <h1 class="text-xl font-bold tracking-tight">{{ get_setting('nombre_comercial', 'Safa Digital') }}</h1>
            @endif
        </div>

        @php
            $totalPedido = (float) $pedido->total_pedido;
            $totalAbonado = (float) $pedido->movimientosCaja()->where('tipo', 'ingreso')->sum('monto');
            $saldoPendiente = max(0.00, $totalPedido - $totalAbonado);
        @endphp

        <!-- Título -->
        <div class="text-center mb-6">
            <h2 class="text-sm font-medium text-neutral-500 uppercase tracking-wider">Orden</h2>
            <div class="text-3xl font-extrabold text-neutral-900">#{{ $pedido->numero_orden }}</div>
            <div class="text-xs text-neutral-400 mt-1">Registrado: {{ \Carbon\Carbon::parse($pedido->created_at)->timezone('America/Tegucigalpa')->format('d/m/Y h:i A') }}</div>
            
            <!-- Badge de Estado de Pago -->
            <div class="mt-3">
                @if($saldoPendiente <= 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Liquidado
                    </span>
                @elseif($totalAbonado > 0 && $saldoPendiente > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Abono / Pago Parcial
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                        Pago Pendiente
                    </span>
                @endif
            </div>
        </div>

        <!-- Timeline / Estado -->
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6 mb-6">
            <h3 class="text-sm font-semibold text-neutral-800 mb-4 uppercase tracking-wider">Estado Actual</h3>
            
            <div class="flex items-center space-x-4">
                @php
                    $estadoColor = 'bg-neutral-100 text-neutral-500';
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />';
                    
                    $est = strtoupper($pedido->estado);
                    if ($est == 'PENDIENTE' || $est == 'NUEVO') {
                        $estadoColor = 'bg-blue-50 text-blue-600';
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />';
                    } elseif ($est == 'DISEÑO' || $est == 'ESPERANDO APROBACIÓN') {
                        $estadoColor = 'bg-indigo-50 text-indigo-600';
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />';
                    } elseif ($est == 'PRODUCCIÓN' || $est == 'EN PRODUCCIÓN') {
                        $estadoColor = 'bg-amber-50 text-amber-600';
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />';
                    } elseif ($est == 'LISTO PARA ENTREGA' || $est == 'LISTO') {
                        $estadoColor = 'bg-emerald-50 text-emerald-600';
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />';
                    } elseif ($est == 'ENTREGADO') {
                        $estadoColor = 'bg-neutral-100 text-neutral-600';
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />';
                    } elseif ($est == 'CANCELADO' || $est == 'ANULADO') {
                        $estadoColor = 'bg-red-50 text-red-600';
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />';
                    }
                @endphp

                <div class="flex-shrink-0 w-12 h-12 rounded-full {{ $estadoColor }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        {!! $icon !!}
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-bold text-neutral-900">{{ $pedido->estado }}</div>
                    @if($pedido->fecha_estimada_entrega)
                    <div class="text-sm text-neutral-500 mt-0.5">Entrega: {{ $pedido->fecha_estimada_entrega->format('d/m/Y') }}</div>
                    @else
                    <div class="text-sm text-neutral-500 mt-0.5">Fecha de entrega pendiente</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Línea de Tiempo de Progreso -->
        @if($pedido->historiales && $pedido->historiales->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6 mb-6">
            <h3 class="text-sm font-semibold text-neutral-800 mb-6 uppercase tracking-wider text-center">Historial de Progreso</h3>
            <div class="relative border-l border-neutral-200 ml-4 space-y-6">
                @foreach($pedido->historiales->sortByDesc('created_at') as $historial)
                    <div class="relative pl-6">
                        <!-- Círculo indicador -->
                        @php
                            $circleColor = 'bg-emerald-500';
                            if ($loop->first) {
                                $circleColor = 'bg-emerald-500 ring-4 ring-emerald-100';
                            } else {
                                $circleColor = 'bg-neutral-300';
                            }
                        @endphp
                        <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full {{ $circleColor }} border border-white"></div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-neutral-800">{{ $historial->estado_nuevo }}</span>
                            <span class="text-xs text-neutral-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($historial->pivot->created_at ?? $historial->created_at, 'UTC')->setTimezone('America/Tegucigalpa')->format('d/m/Y h:i A') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Resumen de Productos -->
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6 mb-6">
            <h3 class="text-sm font-semibold text-neutral-800 mb-4 uppercase tracking-wider">Productos</h3>
            <ul class="divide-y divide-neutral-100">
                @foreach($pedido->detalles as $detalle)
                <li class="py-3 flex justify-between items-start">
                    <div class="flex flex-col flex-1 min-w-0 pr-4">
                        <span class="font-medium text-neutral-900 truncate">
                            {{ $detalle->tipo_producto === 'Libre' ? $detalle->nombre_libre : ($detalle->variante->producto->nombre ?? 'Producto') }}
                        </span>
                        @if($detalle->tipo_producto !== 'Libre' && $detalle->variante && $detalle->variante->nombre != 'Única')
                        <span class="text-xs text-neutral-500">{{ $detalle->variante->nombre }}</span>
                        @endif
                    </div>
                    <div class="flex items-center space-x-3 text-right flex-shrink-0">
                        <span class="text-xs text-neutral-500">L.{{ number_format($detalle->precio_venta, 2) }} x{{ $detalle->cantidad }}</span>
                        <span class="font-semibold text-neutral-900">L.{{ number_format($detalle->subtotal, 2) }}</span>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Finanzas -->
        <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 p-6">
            <h3 class="text-sm font-semibold text-neutral-800 mb-4 uppercase tracking-wider">Resumen de Pago</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between text-neutral-600">
                    <span>Subtotal</span>
                    <span>L.{{ number_format($pedido->subtotal, 2) }}</span>
                </div>
                
                @if($pedido->descuento > 0)
                <div class="flex justify-between text-red-500">
                    <span>Descuento</span>
                    <span>- L.{{ number_format($pedido->descuento, 2) }}</span>
                </div>
                @endif
                
                <div class="flex justify-between text-lg font-bold text-neutral-900 pt-2 border-t border-neutral-100">
                    <span>Total del Pedido</span>
                    <span>L.{{ number_format($totalPedido, 2) }}</span>
                </div>
                
                <div class="flex justify-between text-emerald-600 pt-2">
                    <span>Total Abonado</span>
                    <span>L.{{ number_format($totalAbonado, 2) }}</span>
                </div>
                
                <div class="flex justify-between text-xl font-black text-neutral-900 pt-3 border-t border-neutral-100">
                    <span>Saldo Pendiente</span>
                    <span>L.{{ number_format($saldoPendiente, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
