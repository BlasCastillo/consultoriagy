<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matriz POA {{ $poa->anio }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
        .header p { font-size: 12px; margin: 5px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left; }
        .small-text { font-size: 9px; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Matriz de Indicadores - Plan Operativo Anual {{ $poa->anio }}</h1>
        <p>
            <strong>Jefatura:</strong> {{ $poa->jefatura->nombre ?? 'N/A' }} | 
            <strong>Estado:</strong> {{ $poa->estado }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="25%">Actividad Programada</th>
                <th rowspan="2" width="15%">Unidad de Medida</th>
                <th rowspan="2" width="20%">Medios de Verificación</th>
                <th colspan="4">Distribución Trimestral</th>
                <th rowspan="2" width="12%">Asignación Presupuestaria</th>
            </tr>
            <tr>
                <th width="7%">I</th>
                <th width="7%">II</th>
                <th width="7%">III</th>
                <th width="7%">IV</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poa->actividades as $actividad)
                <tr>
                    <td class="text-left">
                        {{ $actividad->descripcion }}<br>
                        <span class="small-text">Ind: {{ $actividad->indicador_nombre }}</span><br>
                        <span class="small-text">Fórmula: {{ $actividad->formulacion }}</span>
                    </td>
                    <td>{{ $actividad->unidad_medida }}</td>
                    <td>{{ $actividad->medios_verificacion }}<br><span class="small-text">Frec: {{ $actividad->frecuencia_lectura }}</span></td>
                    
                    @for($i = 1; $i <= 4; $i++)
                        @php
                            $meta = $actividad->metasTrimestrales->where('trimestre', $i)->first();
                        @endphp
                        <td>{{ $meta ? $meta->meta_actual : 0 }}</td>
                    @endfor

                    <td>{{ $actividad->partida_presupuestaria }}</td>
                </tr>
            @endforeach
            
            @if($poa->actividades->isEmpty())
                <tr>
                    <td colspan="8" style="padding: 20px;">No hay actividades registradas en este POA.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
