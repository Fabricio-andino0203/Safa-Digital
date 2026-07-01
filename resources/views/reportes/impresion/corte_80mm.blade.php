<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Caja - {{ $corte->id }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            width: 80mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
            color: #000;
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
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .header {
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
        }
        .header p {
            margin: 3px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }
        .signatures {
            margin-top: 40px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header text-center">
        <h2>SAFA DIGITAL</h2>
        <p>ARQUEO / CORTE DE CAJA</p>
        <p>-------------------------</p>
    </div>

    <div class="row">
        <span>Corte No:</span>
        <span class="font-bold">#{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="row">
        <span>Cajero:</span>
        <span class="font-bold">{{ $corte->usuario->name ?? 'Cajero' }}</span>
    </div>
    <div class="row">
        <span>Fecha Apertura:</span>
        <span>{{ $corte->fecha_apertura->format('d/m/Y H:i') }}</span>
    </div>
    <div class="row">
        <span>Fecha Cierre:</span>
        <span>{{ $corte->fecha_cierre->format('d/m/Y H:i') }}</span>
    </div>

    <div class="divider"></div>

    <div class="row font-bold">
        <span>Fondo Inicial:</span>
        <span>L. {{ number_format($corte->fondo_inicial, 2) }}</span>
    </div>
    <div class="row">
        <span>(+) Ventas Efectivo:</span>
        <span>L. {{ number_format($corte->ventas_efectivo, 2) }}</span>
    </div>
    <div class="row font-bold">
        <span>(=) Total Esperado:</span>
        <span>L. {{ number_format($corte->total_esperado, 2) }}</span>
    </div>

    <div class="divider"></div>

    <div class="row font-bold">
        <span>Efectivo Real:</span>
        <span>L. {{ number_format($corte->efectivo_real, 2) }}</span>
    </div>
    <div class="row font-bold">
        <span>Diferencia:</span>
        <span>
            @if($corte->diferencia == 0)
                L. 0.00
            @elseif($corte->diferencia > 0)
                +L. {{ number_format($corte->diferencia, 2) }} (Sobrante)
            @else
                -L. {{ number_format(abs($corte->diferencia), 2) }} (Faltante)
            @endif
        </span>
    </div>

    <div class="divider"></div>

    <div class="row font-bold">
        <span>Retiro a Tesorería:</span>
        <span>L. {{ number_format($corte->retiro_tesoreria, 2) }}</span>
    </div>
    <div class="row">
        <span>Remanente en Caja:</span>
        <span>L. {{ number_format(max(0, $corte->efectivo_real - $corte->retiro_tesoreria), 2) }}</span>
    </div>

    @if($corte->notas)
        <div class="divider"></div>
        <p class="font-bold" style="margin: 0 0 4px 0;">Notas:</p>
        <p style="margin: 0; font-size: 11px; white-space: pre-wrap;">{{ $corte->notes ?? $corte->notas }}</p>
    @endif

    <div class="signatures">
        <div class="signature-line">
            Firma del Cajero<br>
            {{ $corte->usuario->name ?? 'Cajero' }}
        </div>
        <div class="signature-line">
            Firma de Tesorería<br>
            Autorizado por
        </div>
    </div>

    <div class="text-center" style="margin-top: 30px; font-size: 9px; color: #555;">
        Documento de Auditoría Interna
    </div>
</body>
</html>
