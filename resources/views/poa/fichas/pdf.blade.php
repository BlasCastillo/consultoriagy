<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Actividad</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { text-align: center; vertical-align: middle; }
        .title { text-align: center; margin: 20px 0; }
        .title h2 { margin: 0; font-size: 18px; color: #0f172a; }
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table > tbody > tr > td { width: 50%; vertical-align: top; padding: 10px; }
        .images-table { width: 100%; border-collapse: collapse; }
        .images-table td { padding: 5px; text-align: center; vertical-align: middle; }
        .images-table img { max-width: 100%; max-height: 200px; border: 1px solid #cbd5e1; }
        .data-list { list-style: none; padding: 0; margin: 0; }
        .data-list li { margin-bottom: 10px; }
        .label { font-weight: bold; color: #1e293b; display: block; margin-bottom: 3px; }
        .value { color: #334155; }
        .objective-block { text-align: justify; margin-top: 20px; }
        .objective-title { font-weight: bold; margin-bottom: 5px; display: block; }
        .footer { position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; font-size: 10px; color: #64748b; padding-bottom: 10px; }
    </style>
</head>
<body>

    @include('poa.header_oficial')

    <div class="title">
        <h2>INFORME DE ACTIVIDAD</h2>
    </div>

    <table class="main-table">
        <tr>
            <!-- Columna Izquierda (Fotos) -->
            <td style="border-right: 1px solid #cbd5e1;">
                @if($ficha->imagenes->count() > 0)
                    <table class="images-table">
                        <tr>
                            @foreach($ficha->imagenes->take(4) as $index => $imagen)
                                @if($index > 0 && $index % 2 == 0)
                                    </tr><tr>
                                @endif
                                <td>
                                    <img src="{{ public_path('storage/' . $imagen->ruta_imagen) }}" alt="Foto {{ $index + 1 }}">
                                </td>
                            @endforeach
                        </tr>
                    </table>
                @else
                    <p style="text-align: center; color: #94a3b8;">Sin evidencias fotográficas</p>
                @endif
            </td>
            
            <!-- Columna Derecha (Datos) -->
            <td style="padding-left: 20px;">
                <ul class="data-list">
                    <li>
                        <span class="label">Fecha:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($ficha->fecha)->format('d \d\e M \d\e Y') }}</span>
                    </li>
                    <li>
                        <span class="label">Actividad:</span>
                        <span class="value">{{ $ficha->titulo_actividad }}</span>
                    </li>
                    <li>
                        <span class="label">Desarrollada por:</span>
                        <span class="value">{{ $ficha->desarrollada_por }}</span>
                    </li>
                    <li>
                        <span class="label">Cantidad de personas beneficiadas:</span>
                        <span class="value">{{ $ficha->cantidad_beneficiados }}</span>
                    </li>
                    <li>
                        <span class="label">Instituciones y/o Mov. Sociales involucradas:</span>
                        <span class="value">{{ $ficha->instituciones_involucradas }}</span>
                    </li>
                </ul>
            </td>
        </tr>
    </table>

    <div class="objective-block">
        <span class="objective-title">OBJETIVO LOGRADO EN LA ACTIVIDAD:</span>
        {{ $ficha->objetivo_logrado }}
    </div>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="width: 33%; text-align: center;">[LOGO REDES SOCIALES] @Institucion</td>
                <td style="width: 34%; text-align: center;">[LOGO GOBIERNO]</td>
                <td style="width: 33%; text-align: center;">[CIERRE INSTITUCIONAL]</td>
            </tr>
        </table>
    </div>

</body>
</html>
