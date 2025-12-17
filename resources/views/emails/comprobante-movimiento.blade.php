<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Movimiento</title>
</head>

<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px;">

    <div style="max-width: 600px; margin: 0 auto; background-color: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">

        <!-- Header -->
        <div style="background-color: #1a1a1a; padding: 30px 40px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: white;">{{ $organizacionNombre }}</h1>
            <p style="margin: 8px 0 0 0; font-size: 14px; color: #999;">Comprobante de {{ ucfirst($movimiento->tipo) }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px;">
            <p style="margin: 0 0 16px 0; font-size: 16px; color: #333;">Hola <strong>{{ $movimiento->asociado->nombre }}</strong>,</p>

            <p style="margin: 0 0 24px 0; font-size: 15px; color: #666; line-height: 1.6;">
                Se ha registrado un nuevo <strong>{{ $movimiento->tipo }}</strong> por un monto de
                <strong style="color: #1a1a1a;">${{ number_format($movimiento->monto, 2, ',', '.') }}</strong>
                el día {{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }} a las {{ $movimiento->hora }}.
            </p>

            <p style="margin: 0 0 32px 0; font-size: 15px; color: #666;">
                Encontrarás el comprobante detallado en el archivo PDF adjunto a este correo.
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