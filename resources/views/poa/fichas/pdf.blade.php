<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Actividad</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0; }
        .subtitle { font-size: 14px; color: #64748b; margin-top: 5px; }
        .section { margin-bottom: 20px; }
        .label { font-weight: bold; color: #1e293b; width: 150px; display: inline-block; }
        .value { color: #334155; }
        .row { margin-bottom: 8px; }
        .images-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .images-table td { padding: 5px; text-align: center; vertical-align: middle; }
        .images-table img { max-width: 100%; max-height: 250px; border: 1px solid #cbd5e1; padding: 2px; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">INFORME DE ACTIVIDAD</h1>
        <p class="subtitle">Plan Operativo Anual - {{ date('Y') }}</p>
    </div>

    @if($ficha->imagenes->count() > 0)
        <table class="images-table">
            <tr>
                @foreach($ficha->imagenes as $index => $imagen)
                    @if($index > 0 && $index % 2 == 0)
                        </tr><tr>
                    @endif
                    <td>
                        <img src="{{ public_path('storage/' . $imagen->ruta_imagen) }}" alt="Evidencia">
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <div class="section">
        <div class="row">
            <span class="label">Fecha:</span>
            <span class="value">{{ $ficha->fecha->format('d/m/Y') }}</span>
        </div>
        <div class="row">
            <span class="label">Actividad:</span>
            <span class="value">{{ $ficha->titulo_actividad }}</span>
        </div>
        <div class="row">
            <span class="label">Desarrollada por:</span>
            <span class="value">{{ $ficha->desarrollada_por }}</span>
        </div>
        <div class="row">
            <span class="label">Beneficiados:</span>
            <span class="value">{{ $ficha->cantidad_beneficiados }} personas</span>
        </div>
    </div>

    <div class="section">
        <h3 style="font-size: 16px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">Instituciones Involucradas</h3>
        <p class="value">{{ $ficha->instituciones_involucradas ?? 'Ninguna' }}</p>
    </div>

    <div class="section">
        <h3 style="font-size: 16px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">Objetivo Logrado</h3>
        <p class="value">{{ $ficha->objetivo_logrado ?? 'No especificado' }}</p>
    </div>

</body>
</html>
