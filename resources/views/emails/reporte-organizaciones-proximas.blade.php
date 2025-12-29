<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de organizaciones próximas a vencer</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(90deg, #7C3AED, #EC4899); color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Reporte Diario - Organizaciones Próximas a Vencer</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <p>Hola,</p>

        <p>
            Se adjunta el reporte diario de organizaciones cuya prueba vence dentro de los próximos 3 días.
        </p>

        @if (count($organizaciones) > 0)
        <div style="margin: 20px 0;">
            <h2 style="color: #7C3AED; border-bottom: 2px solid #7C3AED; padding-bottom: 10px;">
                Organizaciones a vencer ({{ count($organizaciones) }})
            </h2>

            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background-color: #7C3AED; color: white;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">ID</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Nombre</th>
                        <th style="padding: 10px; text-align: center; border: 1px solid #ddd;">Fecha Vencimiento</th>
                        <th style="padding: 10px; text-align: center; border: 1px solid #ddd;">Días Restantes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($organizaciones as $org)
                    <tr
                        @if($org['dias_restantes']===0)
                        style="background-color: #FEE2E2;"
                        @else
                        style="background-color: #FEF3C7;"
                        @endif>
                        <td style="padding: 10px; border: 1px solid #ddd;">{{ $org['id'] }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: 500;">{{ $org['nombre'] }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ $org['fecha_formateada'] }}</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                            <strong
                                @if($org['dias_restantes']===0)
                                style="color: #DC2626;"
                                @else
                                style="color: #F59E0B;"
                                @endif>
                                {{ $org['dias_restantes'] }}
                                {{ $org['dias_restantes'] === 1 ? 'día' : 'días' }}
                            </strong>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="background-color: #FEE2E2; border-left: 4px solid #DC2626; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #7F1D1D;">
                <strong>⚠️ Importante:</strong> Las organizaciones con 0 días restantes vencen hoy. Se recomienda seguimiento inmediato.
            </p>
        </div>
        @else
        <div style="background-color: #DBEAFE; border-left: 4px solid #3B82F6; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #1E3A8A;">
                ✓ No hay organizaciones próximas a vencer en los próximos 3 días.
            </p>
        </div>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
            <p style="margin: 0;">Reporte generado: {{ now()->format('d/m/Y H:i') }}</p>
            <p style="margin: 5px 0;">Este es un mensaje automático, por favor no responda a este correo.</p>
        </div>
    </div>

</body>

</html>