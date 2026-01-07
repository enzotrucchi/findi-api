<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Movimiento</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 40px;
            background-color: #ffffff;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .header {
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .org-name {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .powered-by {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .document-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .document-type {
            font-size: 22px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .comprobante-box {
            background-color: #fafafa;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
            border: 1px solid #e5e5e5;
        }

        .monto-section {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e5e5;
        }

        .monto-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .monto-value {
            font-size: 36px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .tipo-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .tipo-ingreso {
            background-color: #f0f0f0;
            color: #333;
        }

        .tipo-egreso {
            background-color: #f0f0f0;
            color: #333;
        }

        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #e5e5e5;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row table {
            width: 100%;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 13px;
        }

        .info-value {
            text-align: right;
            color: #1a1a1a;
            font-size: 14px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
        }

        .footer-text {
            color: #999;
            font-size: 11px;
            line-height: 1.6;
        }

        .signature {
            margin-top: 40px;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="org-name">{{ $organizacionNombre }}</div>
                <div class="powered-by">Powered by Findi</div>
            </div>
            <div class="document-title">Comprobante</div>
            <div class="document-type">{{ ucfirst($movimiento->tipo) }}</div>
        </div>

        <!-- Info del Asociado -->
        @if($movimiento->asociado)
        <div style="margin-bottom: 30px;">
            <div style="font-size: 12px; color: #999; margin-bottom: 4px;">ASOCIADO</div>
            <div style="font-size: 16px; font-weight: 600; color: #1a1a1a;">{{ $movimiento->asociado->nombre }}</div>
            @if($movimiento->asociado->email)
            <div style="font-size: 13px; color: #666; margin-top: 2px;">{{ $movimiento->asociado->email }}</div>
            @endif
        </div>
        @endif

        <!-- Monto Principal -->
        <div class="comprobante-box">
            <div class="monto-section">
                <div class="monto-label">Monto {{ $movimiento->tipo === 'ingreso' ? 'Recibido' : 'Pagado' }}</div>
                <div class="monto-value">${{ number_format($movimiento->monto, 2, ',', '.') }}</div>
            </div>

            <div class="info-row">
                <table>
                    <tr>
                        <td class="info-label">Fecha:</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            <div class="info-row">
                <table>
                    <tr>
                        <td class="info-label">Hora:</td>
                        <td class="info-value">{{ $movimiento->hora }}</td>
                    </tr>
                </table>
            </div>

            @if($movimiento->detalle)
            <div class="info-row">
                <table>
                    <tr>
                        <td class="info-label">Detalle:</td>
                        <td class="info-value">{{ $movimiento->detalle }}</td>
                    </tr>
                </table>
            </div>
            @endif

            @if($movimiento->modoPago)
            <div class="info-row">
                <table>
                    <tr>
                        <td class="info-label">Modo de Pago:</td>
                        <td class="info-value">{{ $movimiento->modoPago->nombre }}</td>
                    </tr>
                </table>
            </div>
            @endif

            @if($movimiento->proyecto)
            <div class="info-row">
                <table>
                    <tr>
                        <td class="info-label">Proyecto:</td>
                        <td class="info-value">{{ $movimiento->proyecto->nombre }}</td>
                    </tr>
                </table>
            </div>
            @endif

            @if($movimiento->proveedor)
            <div class="info-row">
                <table>
                    <tr>
                        <td class="info-label">Proveedor:</td>
                        <td class="info-value">{{ $movimiento->proveedor->nombre }}</td>
                    </tr>
                </table>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="signature">
                <div style="font-weight: 600; color: #1a1a1a;">{{ $organizacionNombre }}</div>
                <div style="font-size: 11px; color: #999; margin-top: 4px;">
                    Powered by Findi - <a href="https://findiapp.com" style="color: #666; text-decoration: none;">findiapp.com</a>
                </div>
            </div>
            <p class="footer-text" style="margin-top: 20px;">
                Comprobante generado automáticamente el {{ \Carbon\Carbon::now()->format('d/m/Y') }} a las {{ \Carbon\Carbon::now()->format('H:i') }}hs.
            </p>
        </div>

    </div>

</body>

</html>