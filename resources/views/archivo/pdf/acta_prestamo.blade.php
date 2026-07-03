<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Préstamo y Entrega</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 14px; 
            color: #333;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 30px; }
        .title { font-size: 18px; margin-bottom: 30px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; width: 35%; }
        .no-border-table td, .no-border-table th { border: none; background-color: transparent; }
        .footer-signatures { margin-top: 60px; }
        .signature-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; margin-top: 50px; padding-top: 5px; text-align: center; }
        .legal-terms { font-size: 12px; margin-top: 20px; margin-bottom: 20px; text-align: justify; line-height: 1.5; }
        .section-title { margin-bottom: 5px; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header center">
        <div class="bold" style="font-size: 16px;">GOBERNACIÓN DEL ESTADO YARACUY</div>
        <div>Jefatura de Archivo y Publicaciones Oficiales</div>
    </div>

    <div class="title center bold">
        ACTA DE ENTREGA Y PRÉSTAMO DE DOCUMENTO FÍSICO
    </div>

    <div class="bold section-title">Detalles del Préstamo</div>
    <table>
        <tr>
            <th>Fecha de Salida</th>
            <td>{{ \Carbon\Carbon::parse($prestamo->fecha_salida)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Fecha Estimada de Devolución</th>
            <td>{{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_estimada)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="bold section-title">Datos de la Gaceta</div>
    <table>
        <tr>
            <th>Número</th>
            <td>{{ $prestamo->gaceta->numero }}</td>
        </tr>
        <tr>
            <th>Tipo</th>
            <td>{{ $prestamo->gaceta->tipo }}</td>
        </tr>
        <tr>
            <th>Año / Mes Político</th>
            <td>{{ $prestamo->gaceta->ano_politico }} / {{ $prestamo->gaceta->mes_politico }}</td>
        </tr>
    </table>

    <div class="bold section-title">Datos del Solicitante</div>
    <table>
        <tr>
            <th>Cédula de Identidad</th>
            <td>{{ $prestamo->solicitante_cedula }}</td>
        </tr>
        <tr>
            <th>Nombres y Apellidos</th>
            <td>{{ $prestamo->solicitante_nombres }}</td>
        </tr>
        <tr>
            <th>Teléfono de Contacto</th>
            <td>{{ $prestamo->telefono ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Correo Electrónico</th>
            <td>{{ $prestamo->correo ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Motivo del Préstamo</th>
            <td>{{ $prestamo->motivo_uso }}</td>
        </tr>
    </table>

    <div class="legal-terms">
        <span class="bold">Términos del Préstamo:</span> El solicitante se compromete a devolver el documento original en buen estado, sin rayaduras, mutilaciones, desmembramientos o cualquier tipo de alteraciones, a más tardar en la fecha estipulada para su devolución. El incumplimiento de esta disposición podrá acarrear las sanciones administrativas o legales correspondientes.
    </div>

    <table class="no-border-table footer-signatures">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div class="signature-line">
                    <span class="bold">Firma del Solicitante</span><br>
                    C.I: {{ $prestamo->solicitante_cedula }}
                </div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="signature-line">
                    <span class="bold">Firma del Jefe de Archivo</span><br>
                    (Sello de la Institución)
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
