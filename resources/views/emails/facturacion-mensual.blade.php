<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación Mensual</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(90deg, #7C3AED, #EC4899); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Facturación Mensual {{ $organizacion->nombre }} - {{ $periodoVisual }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <p>Hola <strong>{{ $adminNombres ?: $organizacion->nombre }}</strong>,</p>

        <p>Te informamos que ya está disponible tu facturación correspondiente al mes <strong>{{ $periodoVisual }}</strong>.</p>

        <div style="margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tbody>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">Período</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>{{ $periodoVisual }}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">Cantidad de asociados</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>{{ $cantidadAsociados }}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">Monto total a pagar</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>USD ${{ number_format($monto, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="background-color: #DBEAFE; border-left: 4px solid #3B82F6; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #1E3A8A;"><strong>Datos para transferencia</strong></p>
            <p style="margin: 10px 0 0; color: #1E3A8A;">
                <strong>ALIAS:</strong> FINDI.APP<br>
                <strong>CBU:</strong> 0170275240000003423998
            </p>
        </div>

        <p>Si tenés alguna consulta, escribinos a <a href="mailto:hola@findiapp.com" style="color: #3B82F6; text-decoration: none;">hola@findiapp.com</a>.</p>

        <p>Saludos,<br>
            <strong>El equipo de Findi</strong>
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #777;">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
    </div>

</body>

</html>