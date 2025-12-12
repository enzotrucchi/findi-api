<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Movimiento</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(90deg, #7C3AED, #EC4899); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Comprobante de {{ ucfirst($movimiento->tipo) }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <p>Hola <strong>{{ $movimiento->asociado->nombre }}</strong>,</p>
        <p>Se ha registrado un nuevo {{ $movimiento->tipo }} en <strong>{{ $organizacionNombre }}</strong>.</p>

        <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">

            <div style="padding: 15px 0; margin-bottom: 20px; border-bottom: 2px solid #eee;">
                <table width="100%">
                    <tr>
                        @if($movimiento->tipo === 'ingreso')
                        <td style="text-align: left;">
                            <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; background-color: #D1FAE5; color: #065F46;">
                                {{ strtoupper($movimiento->tipo) }}
                            </span>
                        </td>
                        <td style="text-align: right; font-size: 24px; font-weight: bold; color: #10B981;">
                            ${{ number_format($movimiento->monto, 2, ',', '.') }}
                        </td>
                        @else
                        <td style="text-align: left;">
                            <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; background-color: #FEE2E2; color: #991B1B;">
                                {{ strtoupper($movimiento->tipo) }}
                            </span>
                        </td>
                        <td style="text-align: right; font-size: 24px; font-weight: bold; color: #EF4444;">
                            ${{ number_format($movimiento->monto, 2, ',', '.') }}
                        </td>
                        @endif
                    </tr>
                </table>
            </div>

            <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <table width="100%">
                    <tr>
                        <td style="font-weight: bold; color: #666;">Fecha:</td>
                        <td style="text-align: right; color: #333;">{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>

            <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <table width="100%">
                    <tr>
                        <td style="font-weight: bold; color: #666;">Hora:</td>
                        <td style="text-align: right; color: #333;">{{ $movimiento->hora }}</td>
                    </tr>
                </table>
            </div>

            @if($movimiento->detalle)
            <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <table width="100%">
                    <tr>
                        <td style="font-weight: bold; color: #666;">Detalle:</td>
                        <td style="text-align: right; color: #333;">{{ $movimiento->detalle }}</td>
                    </tr>
                </table>
            </div>
            @endif

            @if($movimiento->modoPago)
            <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <table width="100%">
                    <tr>
                        <td style="font-weight: bold; color: #666;">Modo de Pago:</td>
                        <td style="text-align: right; color: #333;">{{ $movimiento->modoPago->nombre }}</td>
                    </tr>
                </table>
            </div>
            @endif

            @if($movimiento->proyecto)
            <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <table width="100%">
                    <tr>
                        <td style="font-weight: bold; color: #666;">Proyecto:</td>
                        <td style="text-align: right; color: #333;">{{ $movimiento->proyecto->nombre }}</td>
                    </tr>
                </table>
            </div>
            @endif

            @if($movimiento->proveedor)
            <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                <table width="100%">
                    <tr>
                        <td style="font-weight: bold; color: #666;">Proveedor:</td>
                        <td style="text-align: right; color: #333;">{{ $movimiento->proveedor->nombre }}</td>
                    </tr>
                </table>
            </div>
            @endif

            <!-- <div style="padding: 10px 0;">
                <table width="100%">
                    <tr>
                        <td style="font-weight: bold; color: #666;">Estado:</td>
            <td style="text-align: right;">
                            @if($movimiento->status === 'completado')
                            <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; background-color: #D1FAE5; color: #065F46;">
                                {{ strtoupper($movimiento->status) }}
                            </span>
                            @elseif($movimiento->status === 'pendiente')
                            <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; background-color: #FEF3C7; color: #92400E;">
                                {{ strtoupper($movimiento->status) }}
                            </span>
                            @else
                            <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; background-color: #E5E7EB; color: #1F2937;">
                                {{ strtoupper($movimiento->status) }}
                            </span>
                            @endif
                        </td>
            </tr>
            </table>
        </div> -->
        </div>

        <p style="color: #666; font-size: 14px;">
            Este comprobante ha sido generado automáticamente. Puedes consultarlo en cualquier momento desde la plataforma de Findi.
        </p>

        <p>Saludos,<br><strong>El equipo de Findi</strong></p>

        <p style="text-align: center; margin-top: 30px;">
            <a href="https://findiapp.com" target="_blank" style="display: inline-block; padding: 10px 20px; background-color: #7C3AED; color: white; text-decoration: none; border-radius: 5px;">
                Ir a Findi
            </a>
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #777;">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
    </div>
</body>

</html>