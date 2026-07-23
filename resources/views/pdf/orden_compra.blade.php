<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra - {{ $compra->numero_orden }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            margin-bottom: 30px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-section {
            margin-bottom: 25px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 15px;
        }
        .info-title {
            font-size: 11px;
            font-weight: bold;
            color: #777777;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .details-table th {
            background-color: #fafafa;
            border-bottom: 2px solid #333333;
            color: #111111;
            font-weight: bold;
            text-align: left;
            padding: 10px 8px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .details-table td {
            border-bottom: 1px solid #eeeeee;
            padding: 12px 8px;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #333333;
            border-bottom: none;
            padding-top: 15px;
            font-size: 14px;
        }
        .notes-box {
            margin-top: 40px;
            background-color: #fafafa;
            border: 1px solid #e5e5e5;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <!-- Header Logo and Order Number -->
    <table width="100%" class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="50%" valign="top">
                @php
                    $logo_ruta = get_setting('logo_ruta');
                    $clean_ruta = str_replace('storage/', '', $logo_ruta);
                    $rutaAbsoluta = storage_path('app/public/' . $clean_ruta);
                    $logoHtml = '';
                    if ($logo_ruta && file_exists($rutaAbsoluta)) {
                        $ext = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
                        $data = @file_get_contents($rutaAbsoluta);
                        if ($data) {
                            $base64 = base64_encode($data);
                            $mime = ($ext === 'svg') ? 'image/svg+xml' : 'image/' . $ext;
                            $logoHtml = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="height: 60px; object-fit: contain;">';
                        }
                    }
                @endphp
                @if($logoHtml)
                    {!! $logoHtml !!}
                @else
                    <span style="font-size: 22px; font-weight: bold; color: #111111;">{{ get_setting('nombre_comercial', 'Safa Digital') }}</span>
                @endif
            </td>
            <td width="50%" class="text-right" valign="top">
                <div class="title">ORDEN DE COMPRA</div>
                <div style="font-size: 16px; font-weight: bold; color: #777777; margin-top: 5px;">N° {{ $compra->numero_orden }}</div>
                <div style="margin-top: 10px; color: #555555;">Fecha: {{ $compra->fecha->format('d/m/Y') }}</div>
                <div>Estado: <span style="font-weight: bold; color: {{ $compra->estado === 'Recibida' ? '#10b981' : '#f59e0b' }}">{{ $compra->estado }}</span></div>
            </td>
        </tr>
    </table>

    <!-- Info Sections -->
    <table width="100%" class="info-section" cellpadding="0" cellspacing="0">
        <tr>
            <td width="50%" valign="top" style="padding-right: 20px;">
                <div class="info-title">Proveedor</div>
                @if($compra->proveedor)
                    <div class="font-bold" style="font-size: 13px; color: #111111;">
                        {{ $compra->proveedor->empresa ?? $compra->proveedor->nombre }}
                    </div>
                    @if($compra->proveedor->empresa && $compra->proveedor->nombre && $compra->proveedor->empresa !== $compra->proveedor->nombre)
                        <div style="color: #555555; margin-top: 2px;">Contacto: {{ $compra->proveedor->nombre }}</div>
                    @endif
                    @if($compra->proveedor->telefono)
                        <div style="color: #555555;">Teléfono: {{ $compra->proveedor->telefono }}</div>
                    @endif
                @else
                    <div class="font-bold" style="font-size: 13px; color: #111111;">Proveedor No Asignado</div>
                @endif
            </td>
            <td width="50%" valign="top">
                <div class="info-title">Comprador</div>
                <div class="font-bold" style="font-size: 13px; color: #111111;">{{ get_setting('nombre_comercial', 'Safa Digital') }}</div>
                <div style="color: #555555; margin-top: 2px;">{{ get_setting('direccion', 'Honduras') }}</div>
                <div style="color: #555555;">Teléfono: {{ get_setting('telefono', '') }}</div>
            </td>
        </tr>
    </table>

    <!-- Detalles Table -->
    <table class="details-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="10%" class="text-center">Cant</th>
                <th width="50%">Descripción</th>
                <th width="20%" class="text-right">Costo Unit.</th>
                <th width="20%" class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compra->detalles as $det)
                <tr>
                    <td class="text-center font-bold" style="color: #111111;">{{ $det->cantidad }}</td>
                    <td>
                        @if($det->variante && $det->variante->producto)
                            <span class="font-bold" style="color: #111111;">{{ $det->variante->producto->nombre }}</span>
                            @if(!empty($det->variante->atributos))
                                <div style="font-size: 10px; color: #666666; margin-top: 2px;">
                                    SKU: {{ $det->variante->sku }} |
                                    {{ implode(' / ', array_values($det->variante->atributos)) }}
                                </div>
                            @elseif($det->variante->sku)
                                <div style="font-size: 10px; color: #666666; margin-top: 2px;">
                                    SKU: {{ $det->variante->sku }}
                                </div>
                            @endif
                        @else
                            <span class="font-bold" style="color: #111111;">{{ $det->nombre_snapshot ?? $det->descripcion ?? 'Trabajo a Medida' }}</span>
                        @endif
                    </td>
                    <td class="text-right">L. {{ number_format($det->costo_unitario, 2) }}</td>
                    <td class="text-right font-bold" style="color: #111111;">L. {{ number_format($det->subtotal ?? ($det->cantidad * $det->costo_unitario), 2) }}</td>
                </tr>
            @endforeach

            <!-- Desglose de Costos Extras (Fletes, Ojitos, Refuerzos, etc.) -->
            @if(!empty($compra->extras) && (is_array($compra->extras) || is_object($compra->extras)))
                @foreach($compra->extras as $extra)
                    @php
                        $nombreExtra = is_array($extra) ? ($extra['concepto'] ?? $extra['descripcion'] ?? $extra['nombre'] ?? 'Extra') : ($extra->concepto ?? $extra->descripcion ?? $extra->nombre ?? 'Extra');
                        $montoExtra = is_array($extra) ? floatval($extra['costo'] ?? $extra['monto'] ?? 0) : floatval($extra->costo ?? $extra->monto ?? 0);
                    @endphp
                    @if($montoExtra > 0 || !empty($nombreExtra))
                        <tr>
                            <td class="text-center font-bold" style="color: #666666;">1</td>
                            <td>
                                <span class="font-bold" style="color: #444444;">Extra: {{ $nombreExtra }}</span>
                            </td>
                            <td class="text-right" style="color: #666666;">L. {{ number_format($montoExtra, 2) }}</td>
                            <td class="text-right font-bold" style="color: #111111;">L. {{ number_format($montoExtra, 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif

            <!-- Resumen Total Global -->
            <tr class="total-row">
                <td colspan="2"></td>
                <td class="text-right font-bold" style="font-size: 12px; text-transform: uppercase;">Total</td>
                <td class="text-right font-bold" style="color: #111111; font-size: 14px;">L. {{ number_format($compra->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Notas Section -->
    @if($compra->notas)
        <div class="notes-box">
            <div class="info-title">Notas / Instrucciones de Entrega</div>
            <div style="color: #555555; margin-top: 5px; white-space: pre-line;">{{ $compra->notas }}</div>
        </div>
    @endif

</body>
</html>
