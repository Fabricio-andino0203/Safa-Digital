<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Caja #{{ $corte->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
        }
        .header table {
            width: 100%;
        }
        .logo-title {
            font-size: 24px;
            font-weight: 900;
            color: #111;
            margin: 0;
        }
        .subtitle {
            font-size: 11px;
            color: #666;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .document-title {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #444;
            margin: 0;
        }
        .document-number {
            text-align: right;
            font-size: 13px;
            color: #666;
            margin: 2px 0 0 0;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 6px 10px;
            border: 1px solid #e5e5e5;
        }
        .meta-label {
            font-weight: bold;
            color: #555;
            background-color: #fafafa;
            width: 25%;
        }
        .meta-value {
            width: 25%;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #222;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
            font-size: 11px;
            border: 1px solid #e5e5e5;
            padding: 8px 12px;
        }
        .details-table td {
            padding: 10px 12px;
            border: 1px solid #e5e5e5;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .highlight {
            font-weight: bold;
            background-color: #fafafa;
        }
        .footer {
            margin-top: 60px;
            width: 100%;
        }
        .signature-col {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #888;
            margin: 50px auto 5px auto;
        }
        .signature-text {
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <h1 class="logo-title">SAFA DIGITAL</h1>
                    <p class="subtitle">Sistema de Control Operativo e Inventario</p>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <h2 class="document-title">AUDITORÍA DE CORTE DE CAJA</h2>
                    <p class="document-number">Orden de Control: <strong>#{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</strong></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Información del Turno / Sesión</div>
    <table class="meta-table">
        <tr>
            <td class="meta-label">Cajero Operador:</td>
            <td class="meta-value font-bold">{{ $corte->usuario->name ?? 'Cajero' }}</td>
            <td class="meta-label">Código Corte:</td>
            <td class="meta-value">CC-{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Apertura de Turno:</td>
            <td class="meta-value">{{ $corte->fecha_apertura->format('d/m/Y H:i') }}</td>
            <td class="meta-label">Cierre de Turno:</td>
            <td class="meta-value">{{ $corte->fecha_cierre->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <div class="section-title">Resumen del Arqueo de Efectivo</div>
    <table class="details-table">
        <thead>
            <tr>
                <th>Concepto / Desglose</th>
                <th class="text-right">Monto Calculado (L.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>(+) Fondo Inicial de Apertura</td>
                <td class="text-right">L. {{ number_format($corte->fondo_inicial, 2) }}</td>
            </tr>
            <tr>
                <td>(+) Ventas en Efectivo Registradas (POS)</td>
                <td class="text-right">L. {{ number_format($corte->ventas_efectivo, 2) }}</td>
            </tr>
            <tr class="highlight">
                <td>(=) Saldo de Caja Esperado (Teórico)</td>
                <td class="text-right">L. {{ number_format($corte->total_esperado, 2) }}</td>
            </tr>
            <tr class="highlight">
                <td>(=) Efectivo Físico Contado (Arqueo Real)</td>
                <td class="text-right font-bold">L. {{ number_format($corte->efectivo_real, 2) }}</td>
            </tr>
            <tr class="highlight" style="color: {{ $corte->diferencia >= 0 ? '#10b981' : '#ef4444' }}">
                <td>Diferencia de Arqueo (Faltante / Sobrante)</td>
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
        </tbody>
    </table>

    <div class="section-title">Transferencia de Fondos hacia Tesorería</div>
    <table class="details-table">
        <thead>
            <tr>
                <th>Destino / Concepto</th>
                <th class="text-right">Monto Transferido (L.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Retiro para Tesorería (Caja Fuerte / Banco)</td>
                <td class="text-right font-bold">L. {{ number_format($corte->retiro_tesoreria, 2) }}</td>
            </tr>
            <tr class="highlight">
                <td>Fondo Base Remanente en Caja Física (Turno Siguiente)</td>
                <td class="text-right">L. {{ number_format(max(0, $corte->efectivo_real - $corte->retiro_tesoreria), 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($corte->notas)
        <div class="section-title">Notas / Observaciones del Cajero</div>
        <div style="background-color: #fafafa; border: 1px solid #e5e5e5; padding: 12px 15px; border-radius: 8px; white-space: pre-wrap; font-size: 12px; color: #555;">
            {{ $corte->notas }}
        </div>
    @endif

    <table class="footer">
        <tr>
            <td class="signature-col">
                <div class="signature-line"></div>
                <div class="signature-text font-bold">{{ $corte->usuario->name ?? 'Cajero' }}</div>
                <div class="signature-text">Cajero Operador</div>
            </td>
            <td class="signature-col">
                <div class="signature-line"></div>
                <div class="signature-text font-bold">Administrador / Tesorería</div>
                <div class="signature-text">Auditor de Caja</div>
            </td>
        </tr>
    </table>
</body>
</html>
