<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario Físico</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; font-size: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { background-color: #e0e0e0; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Reporte de Inventario Físico de Gacetas</h1>
        <p>Fecha de emisión: {{ now()->format('d/m/Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Gaceta (Número/Año)</th>
                <th>Sede</th>
                <th>Estante</th>
                <th>Gaveta</th>
                <th class="text-center">Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventario as $item)
            <tr>
                <td>{{ $item->gaceta->numero }} / {{ $item->gaceta->anio }}</td>
                <td>{{ $item->sede->nombre ?? 'N/A' }}</td>
                <td>{{ $item->estante->nombre ?? 'N/A' }}</td>
                <td>{{ $item->gaveta->nombre ?? 'N/A' }}</td>
                <td class="text-center"><span class="badge">{{ $item->stock }}</span></td>
            </tr>
            @endforeach
            @if($inventario->isEmpty())
            <tr>
                <td colspan="5" class="text-center">No se encontraron registros de inventario con stock disponible.</td>
            </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
