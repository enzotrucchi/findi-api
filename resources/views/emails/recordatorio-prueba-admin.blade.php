<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de periodo de prueba</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(90deg, #7C3AED, #EC4899); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Periodo de prueba en Findi</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <p>Hola <strong>{{ $asociado->nombre }}</strong>,</p>

        <p>
            Este es un recordatorio sobre tu organización <strong>{{ $organizacionNombre }}</strong>.
        </p>

        <div style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #92400E;">
                <strong>Tiempo de prueba:</strong>
                @if ($diasRestantes === 0)
                Tu periodo de prueba finaliza <strong>hoy</strong>.
                @elseif ($diasRestantes === 1)
                Te queda <strong>1 día</strong> de prueba.
                @else
                Te quedan <strong>{{ $diasRestantes }} días</strong> de prueba.
                @endif
            </p>
            <p style="margin: 10px 0 0; color: #92400E;">
                Fecha de finalización: <strong>{{ $fechaFin }}</strong>.
            </p>
        </div>

        <p>Si necesitas ayuda para continuar con tu plan o quieres conocer opciones, escribenos a
            <span>
                <a href="mailto:hola@findiapp.com" style="color: #3B82F6; text-decoration: none;">
                    hola@findiapp.com
                </a>
            </span>
            .
        </p>

        <p>Saludos,<br>
            <strong>El equipo de Findi</strong>
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #777;">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
    </div>

</body>

</html>