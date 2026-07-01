<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago #REC-{{ str_pad($pago->id, 4, '0', STR_PAD_LEFT) }}</title>
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
                    $logoHtml = '<img src="data:image/svg+xml;base64,' . $base64 . '" style="width: 200px; max-height: none; margin: 0 auto; display: block;">';
                } else {
                    $logoHtml = '<img src="data:image/' . $ext . ';base64,' . $base64 . '" style="width: 200px; max-height: none; margin: 0 auto; display: block;">';
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
    
    <!-- TITULO DESTACADO CON FONDO NEGRO -->
    <div style="background-color: #000; color: #fff; text-align: center; font-weight: bold; font-size: 11px; padding: 6px 0; margin: 10px 0; text-transform: uppercase; letter-spacing: 0.5px;">
        Recibo de Pago: {{ $tipo }}
    </div>

    <!-- METADATOS -->
    <table width="100%" style="border-collapse: collapse; margin-bottom: 10px; font-size: 10px;">
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0; width: 35%;">Recibo N°:</td>
            <td style="text-align: right; padding: 2px 0; width: 65%;">REC-{{ str_pad($pago->id, 4, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Fecha/Hora:</td>
            <td style="text-align: right; padding: 2px 0;">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @if($pedido)
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Aplica a Orden:</td>
            <td style="text-align: right; padding: 2px 0; font-weight: bold;">{{ $pedido->numero_orden }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Cliente:</td>
            <td style="text-align: right; padding: 2px 0;">{{ $pedido->cliente->nombre ?? 'N/A' }}</td>
        </tr>
        @endif
        <tr>
            <td style="font-weight: bold; text-align: left; padding: 2px 0;">Método Pago:</td>
            <td style="text-align: right; padding: 2px 0; text-transform: uppercase;">{{ $pago->referencia ?? 'Efectivo' }}</td>
        </tr>
    </table>

    <div style="border-bottom: 1px solid #000; margin: 5px 0;"></div>

    <!-- TABLA DE MONTOS -->
    <table width="100%" style="border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; font-size: 11px;">
        <tr>
            <td style="text-align: left; padding: 4px 0; width: 60%; font-weight: bold;">Saldo Anterior:</td>
            <td style="text-align: right; padding: 4px 0; width: 40%;">L.{{ number_format($saldoAnterior, 2) }}</td>
        </tr>
        <tr style="font-size: 12px; font-weight: bold;">
            <td style="text-align: left; padding: 4px 0; color: #16a34a;">Monto Pagado:</td>
            <td style="text-align: right; padding: 4px 0; color: #16a34a;">L.{{ number_format($pago->monto, 2) }}</td>
        </tr>
        <tr style="border-top: 1px solid #000; border-bottom: 1px solid #000;">
            <td colspan="2" style="padding: 2px 0;"></td>
        </tr>
        <tr style="font-weight: bold; font-size: 13px;">
            <td style="text-align: left; padding: 6px 0; text-transform: uppercase;">Saldo Actual (Pendiente):</td>
            <td style="text-align: right; padding: 6px 0;">L.{{ number_format($saldoActual, 2) }}</td>
        </tr>
    </table>

    <!-- SECCIÓN QR -->
    @if(isset($qrCode) && $qrCode)
    <div style="text-align: center; margin: 15px 0;">
        <div style="text-align: center; margin-bottom: 5px;">
            <img src="data:image/svg+xml;base64,{!! $qrCode !!}" width="100" style="margin: 0 auto; display: block;">
        </div>
        <div style="font-size: 9px; font-weight: bold; text-transform: uppercase; line-height: 1.2;">
            Escanea para seguir<br>tu pedido en línea
        </div>
    </div>
    @endif

    <!-- PIE DE TICKET -->
    <div style="text-align: center; font-size: 9px; line-height: 1.3; margin-top: 15px; border-top: 1px solid #000; padding-top: 8px;">
        Cajero: {{ auth()->user()->name ?? 'Admin' }}<br>
        ¡Gracias por su preferencia!
    </div>
    
    <div style="text-align: center; font-size: 8px; font-style: italic; margin-top: 10px; color: #555;">
        Generado en Safa Digital
    </div>

</body>
</html>
