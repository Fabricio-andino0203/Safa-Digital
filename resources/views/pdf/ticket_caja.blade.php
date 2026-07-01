<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Caja #{{ str_pad($movimiento->id, 5, '0', STR_PAD_LEFT) }}</title>
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
            margin: 10px 0;
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

    <!-- BLOQUE DESTACADO COMPROBANTE -->
    <table width="100%" cellspacing="0" cellpadding="0" style="background-color: #000; color: #fff; margin: 10px 0;">
        <tr>
            <td style="padding: 6px; text-align: center; font-weight: bold; font-size: 10px;">
                N° TICKET / COMPROBANTE: #{{ str_pad($movimiento->id, 5, '0', STR_PAD_LEFT) }}
            </td>
        </tr>
    </table>

    <!-- METADATOS DE TRANSACCIÓN -->
    <table width="100%" cellspacing="0" cellpadding="0" style="line-height: 1.4;">
        <tr>
            <td width="40%" class="font-bold">FECHA:</td>
            <td width="60%">{{ $movimiento->fecha->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="font-bold">HORA:</td>
            <td>{{ $movimiento->created_at->format('H:i') }}</td>
        </tr>
        <tr>
            <td class="font-bold">CAJERO:</td>
            <td>{{ auth()->user()->nombre ?? auth()->user()->name ?? 'Cajero General' }}</td>
        </tr>
        <tr>
            <td class="font-bold">TIPO MOV.:</td>
            <td class="font-bold">{{ $movimiento->tipo === 'ingreso' ? 'DEPÓSITO' : 'RETIRO' }}</td>
        </tr>
        <tr>
            <td class="font-bold">CONCEPTO:</td>
            <td>{{ $movimiento->concepto }}</td>
        </tr>
        @if($movimiento->referencia)
        <tr>
            <td class="font-bold">MÉTODO PAGO:</td>
            <td style="text-transform: uppercase;">{{ $movimiento->referencia }}</td>
        </tr>
        @endif
        @if($movimiento->pedido_id)
        <tr>
            <td class="font-bold">ORDEN ASOC.:</td>
            <td>#{{ $movimiento->pedido->numero_orden ?? $movimiento->pedido_id }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- TOTAL -->
    <table width="100%" cellspacing="0" cellpadding="0" style="margin: 8px 0; border: 1px solid #000; background-color: #fafafa;">
        <tr>
            <td style="padding: 8px; font-weight: bold; font-size: 13px; text-align: left;">TOTAL:</td>
            <td style="padding: 8px; font-weight: bold; font-size: 13px; text-align: right;">L. {{ number_format($movimiento->monto, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 30px; font-size: 9px;">
        <div style="border-top: 1px solid #000; width: 140px; margin: 0 auto; padding-top: 3px;">
            Firma Autorizada
        </div>
    </div>

    <div class="text-center" style="margin-top: 25px; font-size: 9px; color: #555;">
        Gracias por su preferencia<br>
        Safa Digital
    </div>

</body>
</html>
