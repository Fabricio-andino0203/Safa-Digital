<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $pedido->numero_orden }}</title>
    <style>
        @page {
            margin: 10px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
            width: 100%;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <div class="text-center">
        @php
            $rutaRelativa = get_setting('logo_ruta');
            $realPath = str_replace('storage/', '', $rutaRelativa);
            $rutaAbsoluta = storage_path('app/public/' . $realPath);
            $logoHtml = '';
            $razon_social = get_setting('razon_social');
            
            if ($rutaRelativa && file_exists($rutaAbsoluta)) {
                $ext = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
                $data = file_get_contents($rutaAbsoluta);
                $base64 = base64_encode($data);
                
                if ($ext === 'svg') {
                    $logoHtml = '<img src="data:image/svg+xml;base64,' . $base64 . '" style="width: 220px; max-height: none; margin: 0 auto; display: block;">';
                } else {
                    $logoHtml = '<img src="data:image/' . $ext . ';base64,' . $base64 . '" style="width: 220px; max-height: none; margin: 0 auto; display: block;">';
                }
            }
        @endphp

        <div style="text-align: center; width: 100%; margin-bottom: 8px;">
            @if($logoHtml)
                {!! $logoHtml !!}
            @else
                <h2>{{ get_setting('nombre_comercial') }}</h2>
            @endif
        </div>

        @if($razon_social && trim($razon_social) !== '.')
            <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">
                {{ $razon_social }}
            </div>
        @endif
        
        <div style="font-size: 9px; margin-top: 3px;">
            {!! nl2br(e(get_setting('direccion', "Barrio Santa Clara, El Paraíso, El Paraíso, HND"))) !!}<br>
            Tel: {{ get_setting('telefono', '+504 9999-9999') }}
        </div>
    </div>
    
    <!-- BARRA TICKET NEGRA -->
    <div style="background-color: #000; color: #fff; text-align: center; font-weight: bold; font-size: 13px; padding: 6px 0; margin: 10px 0; text-transform: uppercase;">
        N° Ticket: {{ $pedido->numero_orden }}
    </div>
    
    <!-- METADATOS -->
    <table width="100%" style="border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0; width: 35%;">Fecha:</td>
            <td style="text-align: right; padding: 2px 0; width: 65%;">{{ $pedido->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Hora:</td>
            <td style="text-align: right; padding: 2px 0;">{{ $pedido->created_at->format('H:i') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Cajero:</td>
            <td style="text-align: right; padding: 2px 0;">{{ auth()->user()->name ?? 'Admin' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Cliente:</td>
            <td style="text-align: right; padding: 2px 0;">{{ $pedido->cliente->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Entrega Est:</td>
            <td style="text-align: right; padding: 2px 0;">
                {{ $pedido->fecha_estimada_entrega ? $pedido->fecha_estimada_entrega->format('d/m/Y') : 'Pendiente' }}
                {{ $pedido->hora_estimada_entrega ? ' '.$pedido->hora_estimada_entrega : '' }}
            </td>
        </tr>
    </table>

    <!-- PRODUCTOS -->
    <table width="100%" style="border-collapse: collapse; margin-top: 10px; margin-bottom: 10px;">
        <thead>
            <tr style="border-top: 1px solid #000; border-bottom: 1px solid #000;">
                <th style="text-align: left; padding: 5px 0; font-size: 9px; font-weight: bold; width: 50%;">Descripción</th>
                <th style="text-align: center; padding: 5px 0; font-size: 9px; font-weight: bold; width: 18%;">P. Unit</th>
                <th style="text-align: center; padding: 5px 0; font-size: 9px; font-weight: bold; width: 14%;">Cant</th>
                <th style="text-align: right; padding: 5px 0; font-size: 9px; font-weight: bold; width: 18%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->detalles as $detalle)
            <tr style="border-bottom: 1px solid #000;">
                <td style="padding: 5px 0; font-size: 9px; vertical-align: top;">
                    @if($detalle->tipo_producto === 'Inventario' && $detalle->variante)
                        <div style="font-weight: bold;">{{ $detalle->variante->producto->nombre }}</div>
                        <div style="font-size: 8px;">{{ $detalle->variante->talla }} / {{ $detalle->variante->color }}</div>
                    @else
                        <div style="font-weight: bold;">{{ $detalle->nombre_libre }}</div>
                        <div style="font-size: 8px;">{{ $detalle->descripcion_libre }}</div>
                    @endif
                </td>
                <td style="text-align: center; padding: 5px 0; font-size: 9px; vertical-align: top;">
                    {{ number_format($detalle->precio_venta, 2) }}
                </td>
                <td style="text-align: center; padding: 5px 0; font-size: 9px; vertical-align: top; font-weight: bold;">
                    {{ $detalle->cantidad }}
                </td>
                <td style="text-align: right; padding: 5px 0; font-size: 9px; vertical-align: top; font-weight: bold;">
                    {{ number_format($detalle->subtotal, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTALES -->
    <table width="100%" style="border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; font-size: 11px;">
        <tr>
            <td style="text-align: right; padding: 2px 0; width: 60%; font-weight: bold;">Subtotal:</td>
            <td style="text-align: right; padding: 2px 0; width: 40%;">L.{{ number_format($pedido->subtotal, 2) }}</td>
        </tr>
        @if($pedido->descuento > 0)
        <tr>
            <td style="text-align: right; padding: 2px 0; font-weight: bold;">Descuento:</td>
            <td style="text-align: right; padding: 2px 0;">-L.{{ number_format($pedido->descuento, 2) }}</td>
        </tr>
        @endif
        <tr style="font-weight: bold;">
            <td style="text-align: right; padding: 2px 0;">Total:</td>
            <td style="text-align: right; padding: 2px 0;">L.{{ number_format($pedido->total_pedido, 2) }}</td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td colspan="2" style="padding: 2px 0;"></td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 4px 0; font-weight: bold;">Abonado:</td>
            <td style="text-align: right; padding: 4px 0;">L.{{ number_format($pedido->total_abonado, 2) }}</td>
        </tr>
        <tr style="font-weight: bold; font-size: 13px;">
            <td style="text-align: right; padding: 4px 0; text-transform: uppercase;">Saldo Pendiente:</td>
            <td style="text-align: right; padding: 4px 0;">L.{{ number_format($pedido->saldo_pendiente, 2) }}</td>
        </tr>
    </table>

    <!-- TOTAL ARTICULOS -->
    <div style="font-size: 10px; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 5px;">
        No. Artículos: {{ $pedido->detalles->sum('cantidad') }}
    </div>

    <!-- SECCIÓN QR -->
    @if(isset($qrCode) && $qrCode)
    <div style="text-align: center; margin: 15px 0;">
        <div style="text-align: center; margin-bottom: 5px;">
            <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="120" style="margin: 0 auto; display: block;">
        </div>
        <div style="font-size: 9px; font-weight: bold; text-transform: uppercase; line-height: 1.2;">
            Escanea para seguir<br>tu pedido en línea
        </div>
    </div>
    @endif

    <!-- PIE DE TICKET (MENSAJE IMPORTANTE) -->
    <div style="text-align: center; font-size: 9px; line-height: 1.3; margin-top: 15px;">
        <strong>¡IMPORTANTE!</strong><br>
        {!! nl2br(e(get_setting('ticket_mensaje_pie', "Conserve este ticket. Es indispensable\npara reclamos o retiros de su pedido."))) !!}
    </div>
    
    <div style="text-align: center; font-size: 8px; font-style: italic; margin-top: 10px; color: #555;">
        Generado en Safa Digital
    </div>

</body>
</html>
