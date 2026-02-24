<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recordatorio de cuota</title>
</head>

<body>
    <h2>Recordatorio de pago</h2>

    <p>
        Te recordamos que la cuota <strong>{{ $cuota->numero }}</strong> del plan
        <strong>{{ $planPago->descripcion }}</strong> vence el
        <strong>{{ \Illuminate\Support\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') }}</strong>.
    </p>

    <p>
        Importe: <strong>${{ number_format((float) $cuota->importe, 2, ',', '.') }}</strong>
    </p>

    <p>
        Gracias.
    </p>
</body>

</html>
