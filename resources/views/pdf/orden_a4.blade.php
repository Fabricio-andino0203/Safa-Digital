<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden {{ $pedido->numero_orden }}</title>
    <style>
        @page {
            margin: 30px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #374151;
            margin: 0;
            padding: 0;
            width: 100%;
            line-height: 1.4;
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
                
                if ($ext === 'svg') {
                    $logoHtml = '<img src="data:image/svg+xml;base64,' . $base64 . '" style="width: 150px; max-height: none; margin: 0; display: block;">';
                } else {
                    $logoHtml = '<img src="data:image/' . $ext . ';base64,' . $base64 . '" style="width: 150px; max-height: none; margin: 0; display: block;">';
                }
            }
        }
    @endphp

    <!-- ENCABEZADO -->
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 15px;">
        <tr>
            <td style="width: 30%; vertical-align: middle; border: none; padding: 0;">
                @if($logoHtml)
                    {!! $logoHtml !!}
                @else
                    <div style="font-size: 20px; font-weight: bold; text-transform: uppercase;">
                        {{ get_setting('nombre_comercial', 'SAFA DIGITAL') }}
                    </div>
                @endif
            </td>
            <td style="width: 40%; vertical-align: middle; border: none; padding: 0 15px; font-size: 10px; color: #555; line-height: 1.4;">
                @php
                    $razon_social = get_setting('razon_social');
                @endphp
                @if($razon_social && trim($razon_social) !== '.')
                    <div style="font-weight: bold; color: #000; font-size: 11px; margin-bottom: 2px;">{{ mb_strtoupper($razon_social) }}</div>
                @endif
                <div>Tel: {{ get_setting('telefono', '+504 9999-9999') }}</div>
                <div>{!! nl2br(e(get_setting('direccion', ''))) !!}</div>
            </td>
            <td style="width: 30%; vertical-align: middle; border: none; padding: 0; text-align: right;">
                <div style="font-size: 20px; font-weight: bold; color: #4b5563; text-transform: uppercase; letter-spacing: 1px;">NOTA DE PEDIDO</div>
                <div style="font-size: 18px; font-weight: bold; color: #000; margin-top: 5px;">Nº {{ $pedido->numero_orden }}</div>
            </td>
        </tr>
    </table>

    <!-- CAJAS DE INFORMACION -->
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="width: 48%; vertical-align: top; border: none; padding-right: 2%;">
                <div style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; background-color: #fafafa; min-height: 90px;">
                    <div style="font-weight: bold; font-size: 11px; color: #000; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Datos del Cliente</div>
                    <div style="font-size: 10px; line-height: 1.5; color: #374151;">
                       <strong>Nombre:</strong> {{ $pedido->cliente ? $pedido->cliente->nombre : 'Venta de Mostrador' }}<br>
                       <strong>Teléfono:</strong> {{ $pedido->cliente ? $pedido->cliente->telefono : 'N/A' }}<br>
                       <strong>Email:</strong> {{ $pedido->cliente ? $pedido->cliente->email : 'N/A' }}
                    </div>
                </div>
            </td>
            <td style="width: 48%; vertical-align: top; border: none; padding-left: 2%;">
                <div style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; background-color: #fafafa; min-height: 90px;">
                    <div style="font-weight: bold; font-size: 11px; color: #000; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Detalles del Pedido</div>
                    <div style="font-size: 10px; line-height: 1.5; color: #374151;">
                       <strong>Fecha de Emisión:</strong> {{ $pedido->created_at->format('d/m/Y') }}<br>
                       <strong>Fecha Estimada Entrega:</strong> {{ $pedido->fecha_estimada_entrega ? $pedido->fecha_estimada_entrega->format('d/m/Y') : 'Por definir' }}<br>
                       <strong>Prioridad:</strong> {{ $pedido->prioridad }}<br>
                       <strong>Estado:</strong> {{ $pedido->estado }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- LISTA DE PRODUCTOS -->
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-bottom: 25px;">
        <thead>
            <tr style="background-color: #000; color: #fff;">
                <th style="width: 10%; text-align: center; padding: 8px 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; border: none;">Cant</th>
                <th style="width: 50%; text-align: left; padding: 8px 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; border: none;">Descripción</th>
                <th style="width: 20%; text-align: right; padding: 8px 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; border: none;">P. Unit</th>
                <th style="width: 20%; text-align: right; padding: 8px 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; border: none;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->detalles as $detalle)
            <tr style="border-bottom: 1px solid #f3f4f6;">
                <td style="text-align: center; padding: 8px 10px; font-size: 10px; vertical-align: top; font-weight: bold; color: #111827;">
                    {{ $detalle->cantidad }}
                </td>
                <td style="padding: 8px 10px; font-size: 10px; vertical-align: top;">
                    <div style="font-weight: bold; color: #111827;">
                        {{ $detalle->tipo_producto === 'Inventario' ? ($detalle->nombre_snapshot ?? ($detalle->variante->producto->nombre ?? 'Producto')) : $detalle->nombre_libre }}
                    </div>
                    @if($detalle->tipo_producto === 'Libre' && $detalle->descripcion_libre)
                        <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">{{ $detalle->descripcion_libre }}</div>
                    @endif
                </td>
                <td style="text-align: right; padding: 8px 10px; font-size: 10px; vertical-align: top; color: #374151;">
                    L.{{ number_format($detalle->precio_venta, 2) }}
                </td>
                <td style="text-align: right; padding: 8px 10px; font-size: 10px; vertical-align: top; font-weight: bold; color: #111827;">
                    L.{{ number_format($detalle->subtotal, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- INFERIOR: QR, TERMINOS Y TOTALES -->
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-top: 15px;">
        <tr>
            <!-- Columna QR e Info -->
            <td style="width: 48%; vertical-align: top; border: none; padding-right: 2%;">
                @if(isset($qrCode) && $qrCode)
                <table cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-bottom: 15px; border: none;">
                    <tr>
                        <td style="width: 90px; vertical-align: top; padding: 0; border: none;">
                            <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="80" style="display: block;">
                        </td>
                        <td style="vertical-align: middle; padding-left: 10px; border: none; font-size: 9px; font-weight: bold; color: #4b5563; line-height: 1.3; text-transform: uppercase;">
                            Escanea para ver el<br>estado de tu pedido<br>en línea
                        </td>
                    </tr>
                </table>
                @endif

                <!-- Términos y Condiciones -->
                <div style="font-size: 8px; color: #6b7280; line-height: 1.4; text-align: justify; margin-top: 10px;">
                    <strong>TÉRMINOS Y CONDICIONES:</strong><br>
                    {!! nl2br(e(get_setting('terminos_cotizacion', "Este documento es un comprobante de orden. El saldo pendiente debe ser liquidado antes o al momento de la entrega.\nEn caso de cancelación, los anticipos pueden no ser reembolsables dependiendo del avance del trabajo."))) !!}
                </div>
            </td>
            
            <!-- Columna Totales -->
            <td style="width: 48%; vertical-align: top; border: none; padding-left: 2%;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; font-size: 10px;">
                    <tr>
                        <td style="text-align: right; padding: 4px 10px; font-weight: bold; color: #4b5563; width: 60%;">Subtotal:</td>
                        <td style="text-align: right; padding: 4px 10px; color: #111827; width: 40%;">L.{{ number_format($pedido->subtotal, 2) }}</td>
                    </tr>
                    @if($pedido->descuento > 0)
                    <tr>
                        <td style="text-align: right; padding: 4px 10px; font-weight: bold; color: #4b5563;">Descuento:</td>
                        <td style="text-align: right; padding: 4px 10px; color: #111827;">-L.{{ number_format($pedido->descuento, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="font-weight: bold; font-size: 11px; background-color: #fafafa; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                        <td style="text-align: right; padding: 6px 10px; color: #111827;">Total del Pedido:</td>
                        <td style="text-align: right; padding: 6px 10px; color: #111827;">L.{{ number_format($pedido->total_pedido, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding: 6px 10px; font-weight: bold; color: #4b5563;">Anticipo / Abonos:</td>
                        <td style="text-align: right; padding: 6px 10px; color: #16a34a; font-weight: bold;">L.{{ number_format($pedido->total_abonado, 2) }}</td>
                    </tr>
                    <tr style="font-weight: bold; font-size: 13px; background-color: #fef2f2; border: 1px solid #fee2e2;">
                        <td style="text-align: right; padding: 8px 10px; color: #991b1b; text-transform: uppercase;">Saldo Pendiente:</td>
                        <td style="text-align: right; padding: 8px 10px; color: #b91c1c;">L.{{ number_format($pedido->saldo_pendiente, 2) }}</td>
                    </tr>
                </table>
                
                @if($pedido->notas)
                <div style="margin-top: 15px; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px; background-color: #fafafa; font-size: 9px; text-align: left;">
                    <strong>Notas / Observaciones:</strong><br>
                    <span style="color: #4b5563;">{{ $pedido->notas }}</span>
                </div>
                @endif
            </td>
        </tr>
    </table>

    <div style="margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; font-size: 9px; color: #9ca3af; font-weight: bold; text-transform: uppercase;">
        {{ get_setting('nombre_comercial', 'Safa Digital') }} - ¡Gracias por su confianza!
    </div>

</body>
</html>
