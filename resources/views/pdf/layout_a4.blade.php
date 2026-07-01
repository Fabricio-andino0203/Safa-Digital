<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-title {
            font-size: 20px;
            font-weight: bold;
            color: #111;
            margin: 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9px;
            color: #666;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-title {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
            text-transform: uppercase;
        }
        .report-meta {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin: 3px 0 0 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #222;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #e5e5e5;
            padding: 8px 10px;
        }
        .data-table td {
            padding: 8px 10px;
            border: 1px solid #e5e5e5;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
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
        .summary-card {
            background-color: #fafafa;
            border: 1px solid #e5e5e5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-card table {
            width: 100%;
        }
        .summary-label {
            font-weight: bold;
            color: #555;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <h1 class="logo-title">SAFA DIGITAL</h1>
                    <p class="subtitle">Módulo Central de Reportes y Auditoría</p>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <h2 class="report-title">@yield('report_name')</h2>
                    <p class="report-meta">
                        Generado: {{ now()->format('d/m/Y H:i') }} <br>
                        Período: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    @yield('content')

    <div class="footer">
        SAFA DIGITAL — Reporte Interno Confidencial de Operaciones
    </div>
</body>
</html>
