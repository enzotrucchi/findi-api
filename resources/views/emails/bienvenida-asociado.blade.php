<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Findi</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(90deg, #7C3AED, #EC4899); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">¡Bienvenido a Findi!</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <p>Hola <strong>{{ $asociado->nombre }}</strong>,</p>

        <p>Ahora formás parte de <strong>{{ $organizacionNombre }}</strong></p>

        <p>Te agregó <strong>{{ $adminNombre }}</strong>@if($adminEmail) ({{ $adminEmail }})@endif</p>

        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

        <p>Ingresa a <a href="https://findiapp.com" target="_blank" style="color: #7C3AED; text-decoration: none;">Findi</a> Y empezá a gestionar tus movimientos.</p>

        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

        <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>

        <p>¡Gracias por unirte a nosotros!</p>

        <p>Saludos,<br>
            <strong>El equipo de Findi</strong>
        </p>

        <p style="text-align: center; margin-top: 30px;"><a href="https://findiapp.com" target="_blank" style="display: inline-block; padding: 10px 20px; background-color: #7C3AED; color: white; text-decoration: none; border-radius: 5px; text-align: center;">Conoce más de Findi</a></p>

    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #777;">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
    </div>

</body>

</html>