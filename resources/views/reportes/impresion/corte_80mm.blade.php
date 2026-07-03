<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Caja - CC-{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            width: 80mm;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            margin: 0 auto;
            padding: 12px;
            box-sizing: border-box;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .divider {
            border-top: 1px solid #e5e5e5;
            margin: 8px 0;
        }
        .divider-dashed {
            border-top: 1px dashed #cccccc;
            margin: 10px 0;
        }
        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-table, .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .info-label {
            color: #666666;
            width: 40%;
        }
        .info-value {
            font-weight: bold;
            color: #1a1a1a;
        }
        .data-table td {
            padding: 4px 0;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #444444;
            letter-spacing: 0.5px;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .highlight-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .highlight-row td {
            padding: 5px 4px;
            border-top: 1px solid #e5e5e5;
            border-bottom: 1px solid #e5e5e5;
        }
        .signatures {
            margin-top: 30px;
        }
        .signature-block {
            margin-top: 25px;
            text-align: center;
        }
        .signature-line {
            width: 75%;
            margin: 0 auto;
            border-top: 1px solid #999999;
            padding-top: 3px;
            font-size: 9px;
            color: #666666;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header text-center">
        <h2>SAFA DIGITAL</h2>
        <p>Reporte de Arqueo y Cierre</p>
    </div>

    <div class="section-title">Información del Turno</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Corte No:</td>
            <td class="info-value">#{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="info-label">Cajero:</td>
            <td class="info-value">{{ $corte->usuario->name ?? 'Cajero' }}</td>
        </tr>
        <tr>
            <td class="info-label">Apertura:</td>
            <td class="info-value" style="font-weight: normal;">{{ $corte->fecha_apertura->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="info-label">Cierre:</td>
            <td class="info-value" style="font-weight: normal;">{{ $corte->fecha_cierre->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="section-title">Resumen de Efectivo</div>
    <table class="data-table">
        <tr>
            <td>(+) Fondo Inicial:</td>
            <td class="text-right">L. {{ number_format($corte->fondo_inicial, 2) }}</td>
        </tr>
        <tr>
            <td>(+) Ventas en Efectivo:</td>
            <td class="text-right">L. {{ number_format($corte->ventas_efectivo, 2) }}</td>
        </tr>
        <tr class="highlight-row">
            <td>(=) Efectivo Esperado:</td>
            <td class="text-right">L. {{ number_format($corte->total_esperado, 2) }}</td>
        </tr>
        <tr>
            <td class="font-bold">(=) Efectivo Real (Arqueo):</td>
            <td class="text-right font-bold">L. {{ number_format($corte->efectivo_real, 2) }}</td>
        </tr>
        <tr class="font-bold" style="color: {{ $corte->diferencia >= 0 ? '#10b981' : '#ef4444' }};">
            <td>Diferencia:</td>
            <td class="text-right">
                @if($corte->diferencia == 0)
                    L. 0.00 (Cuadrado)
                @elseif($corte->diferencia > 0)
                    + L. {{ number_format($corte->diferencia, 2) }} (Sobrante)
                @else
                    - L. {{ number_format(abs($corte->diferencia), 2) }} (Faltante)
                @endif
            </td>
        </tr>
    </table>

    <div class="divider-dashed"></div>

    <div class="section-title">Transferencia y Remanente</div>
    <table class="data-table">
        <tr class="font-bold">
            <td>(-) Retiro a Tesorería:</td>
            <td class="text-right">L. {{ number_format($corte->retiro_tesoreria, 2) }}</td>
        </tr>
        <tr class="highlight-row" style="background-color: #f0fdf4; color: #15803d;">
            <td>(=) Fondo Remanente en Caja:</td>
            <td class="text-right">L. {{ number_format(max(0, $corte->efectivo_real - $corte->retiro_tesoreria), 2) }}</td>
        </tr>
    </table>

    @if($corte->notas)
        <div class="divider"></div>
        <div class="section-title">Observaciones</div>
        <div style="font-size: 10px; color: #555555; background-color: #fafafa; padding: 6px; border: 1px solid #eeeeee; border-radius: 4px; white-space: pre-wrap;">{{ $corte->notes ?? $corte->notas }}</div>
    @endif

    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line">
                Firma del Cajero<br>
                <strong>{{ $corte->usuario->name ?? 'Cajero' }}</strong>
            </div>
        </div>
        <div class="signature-block">
            <div class="signature-line">
                Firma de Tesorería / Autorización
            </div>
        </div>
    </div>

    <div class="text-center" style="margin-top: 25px; font-size: 8px; color: #888888; text-transform: uppercase; letter-spacing: 0.5px;">
        Auditoría Interna - SAFA Digital
    </div>
</body>
</html>
