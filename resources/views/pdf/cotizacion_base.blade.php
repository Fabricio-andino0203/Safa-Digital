<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $cotizacion->numero_cotizacion }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-items th {
            background-color: #111827;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px;
            border: 1px solid #111827;
        }
        .table-items td {
            padding: 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .totals-table td {
            padding: 4px 0;
        }
    </style>
</head>
<body>

@php
    $rutaRelativa = get_setting('logo_ruta');
    $logoHtml = '';
    if ($rutaRelativa) {
        $realPath = ltrim(str_replace('storage/', '', $rutaRelativa), '/');
        $candidatos = [
            public_path($rutaRelativa),
            public_path($realPath),
            storage_path('app/public/' . $realPath),
        ];
        
        $rutaAbsoluta = null;
        foreach ($candidatos as $cand) {
            if (file_exists($cand) && is_file($cand)) {
                $rutaAbsoluta = $cand;
                break;
            }
        }
        
        if ($rutaAbsoluta) {
            $ext = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
            $data = file_get_contents($rutaAbsoluta);
            $base64 = base64_encode($data);
            $mime = ($ext === 'svg') ? 'image/svg+xml' : 'image/' . $ext;
            $logoHtml = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="width: 200px; max-height: none; display: block;">';
        }
    }
@endphp

    <!-- ENCABEZADO -->
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 25px;">
        <tr>
            <td width="60%" style="vertical-align: top;">
                <div style="margin-bottom: 10px;">
                    @if($logoHtml)
                        {!! $logoHtml !!}
                    @else
                        <h2 style="margin: 0; font-size: 20px; color: #111827;">{{ get_setting('nombre_comercial', 'SAFA DIGITAL') }}</h2>
                    @endif
                </div>
                <div style="font-size: 9px; color: #4b5563; line-height: 1.3; margin-top: 5px;">
                    <strong>Teléfono:</strong> {{ get_setting('telefono', '+504 9999-9999') }}<br>
                    <strong>Dirección:</strong> {!! nl2br(e(get_setting('direccion', "Barrio Santa Clara, El Paraíso, El Paraíso, HND"))) !!}
                </div>
            </td>
            <td width="40%" style="vertical-align: top; text-align: right;">
                <h1 style="margin: 0 0 5px 0; font-size: 26px; font-weight: 800; color: #333; letter-spacing: -0.5px;">COTIZACIÓN</h1>
                <div style="font-size: 13px; font-weight: bold; color: #111827; margin-bottom: 8px;">N° {{ $cotizacion->numero_cotizacion }}</div>
                <div style="font-size: 10px; color: #4b5563; line-height: 1.4;">
                    <strong>Fecha Emisión:</strong> {{ $cotizacion->fecha_emision->format('d/m/Y') }}<br>
                    <strong>Validez:</strong> {{ $cotizacion->validez_dias }} días
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS DEL CLIENTE -->
    <div style="border: 1px solid #e5e7eb; border-radius: 4px; padding: 12px; margin-bottom: 25px;">
        <table width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td width="15%" class="font-bold" style="color: #4b5563; padding: 3px 0;">Cliente:</td>
                <td style="color: #111827; padding: 3px 0;">{{ $cotizacion->cliente->nombre }}</td>
            </tr>
            @if($cotizacion->cliente->telefono)
            <tr>
                <td class="font-bold" style="color: #4b5563; padding: 3px 0;">Teléfono:</td>
                <td style="color: #111827; padding: 3px 0;">{{ $cotizacion->cliente->telefono }}</td>
            </tr>
            @endif
            @if($cotizacion->cliente->email)
            <tr>
                <td class="font-bold" style="color: #4b5563; padding: 3px 0;">Email:</td>
                <td style="color: #111827; padding: 3px 0;">{{ $cotizacion->cliente->email }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- TABLA DE PRODUCTOS -->
    <table class="table-items" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">Cantidad</th>
                <th style="width: 60%; text-align: left;">Descripción</th>
                <th style="width: 15%; text-align: right;">P. Unit</th>
                <th style="width: 15%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->detalles as $detalle)
            <tr>
                <td class="text-center font-bold" style="color: #111827;">{{ $detalle->cantidad }}</td>
                <td>
                    @if($detalle->tipo_producto === 'Inventario' && $detalle->variante)
                        <strong style="color: #111827;">{{ $detalle->variante->producto->nombre ?? 'Producto' }}</strong>
                        <span style="font-size: 9px; color: #6b7280; display: block; margin-top: 2px;">
                            SKU: {{ $detalle->variante->sku }} &bull; {{ $detalle->variante->nombre_completo }}
                            @if(!empty($detalle->extras))
                                &bull; Extras: 
                                @php
                                    $partes = [];
                                    foreach ($detalle->extras as $ex) {
                                        $qty = intval($ex['cantidad'] ?? 1);
                                        $partes[] = $qty > 1 ? "{$qty}x {$ex['nombre']}" : $ex['nombre'];
                                    }
                                @endphp
                                {{ implode(', ', $partes) }}
                            @endif
                        </span>
                    @else
                        <strong style="color: #111827;">{{ $detalle->nombre_libre ?? 'Ítem Libre' }}</strong>
                        @if($detalle->descripcion_libre)
                            <span style="font-size: 9px; color: #6b7280; display: block; margin-top: 2px;">{{ $detalle->descripcion_libre }}</span>
                        @endif
                    @endif
                </td>
                <td class="text-right" style="color: #374151;">L.{{ number_format($detalle->precio_venta, 2) }}</td>
                <td class="text-right font-bold" style="color: #111827;">L.{{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SECCIÓN INFERIOR (Notas y Totales) -->
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 25px;">
        <tr>
            <!-- Notas (70%) -->
            <td width="70%" style="vertical-align: top; padding-right: 30px;">
                @if($cotizacion->notas)
                    <div style="font-size: 11px;">
                        <strong style="color: #111827; display: block; margin-bottom: 5px;">Notas:</strong>
                        <div style="color: #4b5563; font-size: 10px; line-height: 1.4;">
                            {!! nl2br(e($cotizacion->notas)) !!}
                        </div>
                    </div>
                @endif
            </td>
            <!-- Totales (30%) -->
            <td width="30%" style="vertical-align: top;">
                <table class="totals-table" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                    <tr>
                        <td class="font-bold" style="color: #4b5563; text-align: right; padding-right: 10px;">Subtotal:</td>
                        <td class="text-right" style="color: #111827;">L.{{ number_format($cotizacion->subtotal, 2) }}</td>
                    </tr>
                    @if($cotizacion->descuento > 0)
                    <tr>
                        <td class="font-bold" style="color: #4b5563; text-align: right; padding-right: 10px;">Descuento:</td>
                        <td class="text-right" style="color: #ef4444;">-L.{{ number_format($cotizacion->descuento, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="font-size: 13px; font-weight: bold;">
                        <td style="color: #111827; text-align: right; padding-right: 10px; padding-top: 6px; border-top: 1px solid #e5e7eb;">TOTAL:</td>
                        <td class="text-right" style="color: #111827; padding-top: 6px; border-top: 1px solid #e5e7eb;">L.{{ number_format($cotizacion->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- TÉRMINOS Y CONDICIONES -->
    <div style="margin-top: 60px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #f3f4f6; padding-top: 10px;">
        {!! nl2br(e(get_setting('terminos_cotizacion', "Esta cotización es de carácter informativo y no constituye una reserva de inventario."))) !!}
    </div>

</body>
</html>
