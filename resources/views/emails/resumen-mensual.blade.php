<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Mensual</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(90deg, #7C3AED, #EC4899); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Resumen Mensual - {{ $organizacion->nombre }}</h1>
        <p style="margin: 10px 0 0; font-size: 18px;">{{ $totalizadores['periodo_visual'] }}</p>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <p>Hola <strong>{{ $nombreAsociado }}</strong>,</p>

        <p>Te compartimos el resumen de <strong>{{ $organizacion->nombre }}</strong> correspondiente al mes <strong>{{ $totalizadores['periodo_visual'] }}</strong>.</p>

        <div style="margin: 25px 0;">
            <h2 style="color: #7C3AED; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #7C3AED; padding-bottom: 5px;">📊 Resumen General</h2>

            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tbody>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; background-color: #f5f5f5;">
                            <strong>Asociados activos</strong>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            <strong>{{ $totalizadores['asociados_activos'] }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; background-color: #f5f5f5;">
                            <strong>Proyectos totales</strong>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            <strong>{{ $totalizadores['total_proyectos'] }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; background-color: #f5f5f5;">
                            <strong>Proyectos activos</strong>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            <strong>{{ $totalizadores['proyectos_activos'] }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin: 25px 0;">
            <h2 style="color: #7C3AED; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #7C3AED; padding-bottom: 5px;">💰 Movimientos del Mes</h2>

            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tbody>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; background-color: #f5f5f5;">
                            <strong>Total de movimientos</strong>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            <strong>{{ $totalizadores['cantidad_movimientos'] }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; background-color: #E8F5E9;">
                            <strong>Ingresos</strong>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right; color: #2E7D32;">
                            <strong>+ ${{ number_format($totalizadores['ingresos'], 2) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd; background-color: #FFEBEE;">
                            <strong>Egresos</strong>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right; color: #C62828;">
                            <strong>- ${{ number_format($totalizadores['egresos'], 2) }}</strong>
                        </td>
                    </tr>
                    <tr style="background-color: #F3E5F5;">
                        <td style="padding: 12px; border: 2px solid #7C3AED;">
                            <strong style="font-size: 16px;">Balance del mes</strong>
                        </td>
                        @if ($totalizadores['balance'] >= 0)
                        <td style="padding: 12px; border: 2px solid #7C3AED; text-align: right; font-size: 18px; color: #2E7D32;">
                            @else
                        <td style="padding: 12px; border: 2px solid #7C3AED; text-align: right; font-size: 18px; color: #C62828;">
                            @endif
                            <strong>{{ $totalizadores['balance'] >= 0 ? '+' : '' }} ${{ number_format($totalizadores['balance'], 2) }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p>Si tienes alguna consulta, escríbenos a <a href="mailto:hola@findiapp.com" style="color: #7C3AED; text-decoration: none;">hola@findiapp.com</a>.</p>

        <p>Saludos,<br>
            <strong>El equipo de Findi</strong>
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #777;">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
    </div>

</body>

</html>