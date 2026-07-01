<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta POS #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}</title>
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
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        .products-table th {
            border-bottom: 1px solid #000;
            font-size: 9px;
            padding: 4px 2px;
            text-align: left;
        }
        .products-table td {
            padding: 5px 2px;
            font-size: 10px;
            vertical-align: top;
        }
    </style>
</head>
<body>

    @php
        $logo_ruta = get_setting('logo_ruta');
        $razon_social = get_setting('razon_social');
        $clean_ruta = str_replace('storage/', '', $logo_ruta);
        $rutaAbsoluta = storage_path('app/public/' . $clean_ruta);
        $logoHtml = '';
        if ($logo_ruta && file_exists($rutaAbsoluta)) {
            $ext = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
            $data = file_get_contents($rutaAbsoluta);
            $base64 = base64_encode($data);
            $mime = ($ext === 'svg') ? 'image/svg+xml' : 'image/' . $ext;
            $logoHtml = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="width: 140px; display: block; margin: 0 auto 5px auto;">';
        } else {
            $logoHtml = '<h1 style="font-size: 16px; font-weight: bold; margin: 0 0 2px 0; text-transform: uppercase;">' . get_setting('nombre_comercial', 'SAFA DIGITAL') . '</h1>';
        }
    @endphp

    <!-- LOGO Y DATOS DE LA EMPRESA -->
    <div class="text-center">
        {!! $logoHtml !!}
        @if($razon_social && trim($razon_social) !== '.')
            <div style="font-size: 10px; font-weight: bold; margin-bottom: 3px;">{{ $razon_social }}</div>
        @endif
        <div style="font-size: 9px; line-height: 1.2;">
            {!! nl2br(e(get_setting('direccion', 'Tegucigalpa, Honduras'))) !!}<br>
            Tel: {{ get_setting('telefono', '+504 9999-9999') }}
        </div>
    </div>

    <!-- BLOQUE DESTACADO TICKET DE VENTA -->
    <table width="100%" cellspacing="0" cellpadding="0" style="background-color: #000; color: #fff; margin: 10px 0;">
        <tr>
            <td style="padding: 6px; text-align: center; font-weight: bold; font-size: 10px;">
                TICKET DE VENTA N°: #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}
            </td>
        </tr>
    </table>

    <!-- METADATOS DE TRANSACCIÓN -->
    <table width="100%" cellspacing="0" cellpadding="0" style="line-height: 1.4;">
        <tr>
            <td width="35%" class="font-bold">FECHA:</td>
            <td width="65%">{{ $venta->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="font-bold">HORA:</td>
            <td>{{ $venta->created_at->format('H:i') }}</td>
        </tr>
        <tr>
            <td class="font-bold">CAJERO:</td>
            <td>{{ auth()->user()->nombre ?? auth()->user()->name ?? 'Cajero General' }}</td>
        </tr>
        <tr>
            <td class="font-bold">CLIENTE:</td>
            <td>{{ $venta->cliente->nombre ?? $pedido->cliente->nombre ?? 'CONSUMIDOR FINAL' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- DESGLOSE DE PRODUCTOS -->
    <table class="products-table">
        <thead>
            <tr>
                <th width="12%">Cant</th>
                <th width="50%">Descripción</th>
                <th width="18%" class="text-right">P. Unit</th>
                <th width="20%" class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td class="text-center">{{ $detalle->cantidad }}</td>
                <td>
                    {{ $detalle->nombre_snapshot }}
                    <span style="font-size: 8px; color: #555; display: block;">SKU: {{ $detalle->sku_snapshot }}</span>
                </td>
                <td class="text-right">L.{{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">L.{{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- TOTALES -->
    <table width="100%" cellspacing="0" cellpadding="0" style="line-height: 1.5; font-size: 10px;">
        <tr>
            <td width="60%" class="text-right">Subtotal:</td>
            <td width="40%" class="text-right font-bold">L.{{ number_format($venta->subtotal, 2) }}</td>
        </tr>
        @if($venta->descuento > 0)
        <tr>
            <td class="text-right">Descuento:</td>
            <td class="text-right font-bold text-red-600">-L.{{ number_format($venta->descuento, 2) }}</td>
        </tr>
        @endif
        <tr style="font-size: 12px; font-weight: bold;">
            <td class="text-right" style="padding-top: 5px;">TOTAL COBRADO:</td>
            <td class="text-right" style="padding-top: 5px;">L.{{ number_format($venta->total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- MÉTODOS DE PAGO Y CAMBIO -->
    <table width="100%" cellspacing="0" cellpadding="0" style="line-height: 1.4; font-size: 9px; color: #333;">
        <tr>
            <td width="60%">MÉTODO PAGO:</td>
            <td width="40%" class="text-right" style="text-transform: uppercase; font-weight: bold;">{{ $venta->metodo_pago }}</td>
        </tr>
        @if($venta->metodo_pago === 'efectivo')
        <tr>
            <td>EFECTIVO RECIBIDO:</td>
            <td class="text-right">L.{{ number_format($venta->monto_entregado, 2) }}</td>
        </tr>
        <tr>
            <td class="font-bold">CAMBIO:</td>
            <td class="text-right font-bold">L.{{ number_format($venta->cambio, 2) }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 25px; font-size: 9px; color: #555; line-height: 1.3;">
        ¡Gracias por su compra!<br>
        Safa Digital - Inversiones Solucels<br>
        Conserve este comprobante.
    </div>

</body>
</html>
