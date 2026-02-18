<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimiento Eliminado</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px;">

    <div style="max-width: 600px; margin: 0 auto; background-color: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">

        <!-- Header -->
        <div style="background-color: #dc3545; padding: 30px 40px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: white;">{{ $organizacionNombre }}</h1>
            <p style="margin: 8px 0 0 0; font-size: 14px; color: rgba(255,255,255,0.8);">Notificación de Movimiento Eliminado</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px;">
            <p style="margin: 0 0 16px 0; font-size: 16px; color: #333;">Hola <strong>{{ $movimiento['asociado_nombre'] }}</strong>,</p>

            <p style="margin: 0 0 24px 0; font-size: 15px; color: #666; line-height: 1.6;">
                Te informamos que el siguiente movimiento de <strong>{{ $movimiento['tipo'] }}</strong> ha sido <strong>eliminado</strong> de nuestros registros:
            </p>

            <!-- Detalle del movimiento eliminado -->
            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin: 0 0 24px 0; border-left: 4px solid #dc3545;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-size: 14px; color: #666;">Fecha:</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #333; text-align: right;"><strong>{{ $movimiento['fecha'] }}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-size: 14px; color: #666;">Hora:</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #333; text-align: right;"><strong>{{ $movimiento['hora'] }}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-size: 14px; color: #666;">Monto:</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #333; text-align: right;"><strong>${{ number_format($movimiento['monto'], 2, ',', '.') }}</strong></td>
                    </tr>
                    @if(!empty($movimiento['detalle']))
                    <tr>
                        <td style="padding: 8px 0; font-size: 14px; color: #666;">Detalle:</td>
                        <td style="padding: 8px 0; font-size: 14px; color: #333; text-align: right;">{{ $movimiento['detalle'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p style="margin: 0 0 32px 0; font-size: 15px; color: #666;">
                Si tienes alguna consulta sobre esta eliminación, por favor contacta a la organización.
            </p>

            <!-- Divider -->
            <div style="border-top: 1px solid #e5e5e5; margin: 32px 0;"></div>

            <p style="margin: 0 0 8px 0; font-size: 15px; color: #333;">Saludos,</p>
            <p style="margin: 0; font-size: 15px; color: #666;"><strong>{{ $organizacionNombre }}</strong></p>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #999;">Powered by Findi</p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f9f9f9; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e5e5;">
            <p style="margin: 0 0 12px 0; font-size: 13px; color: #666;">
                Este es un correo automático, por favor no respondas a este mensaje.
            </p>
            <a href="https://findiapp.com" target="_blank" style="font-size: 13px; color: #666; text-decoration: none;">
                www.findiapp.com
            </a>
        </div>
    </div>
</body>

</html>