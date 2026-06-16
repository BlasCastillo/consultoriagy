<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Organigrama Institucional</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #333; }
        h2 { text-align: center; text-transform: uppercase; border-bottom: 2px solid #1e293b; padding-bottom: 10px; color: #0f172a; margin-bottom: 20px; }
        ul { list-style-type: none; border-left: 2px solid #94a3b8; padding-left: 20px; margin-top: 5px; margin-bottom: 5px; }
        li { margin: 0; position: relative; padding-top: 8px; }
        /* La línea horizontal que conecta el tronco con la tarjeta */
        li::before { content: ''; position: absolute; top: 25px; left: -20px; width: 20px; border-top: 2px solid #94a3b8; }
        
        .node-card {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 10px 15px;
            border-radius: 4px;
            display: block;
            width: 85%;
            margin-bottom: 5px;
        }
        .node-title { font-weight: bold; font-size: 14px; color: #0f172a; }
        .nivel-badge { 
            font-size: 10px; 
            background-color: #e2e8f0; 
            color: #475569;
            padding: 3px 6px; 
            border-radius: 3px; 
            border: 1px solid #cbd5e1;
            float: right;
            text-transform: uppercase;
            font-weight: bold;
        }
        .no-data { text-align: center; font-style: italic; color: #64748b; margin-top: 30px; }
    </style>
</head>
<body>
    <h2>Estructura Organizativa</h2>
    
    @if($raices->count() > 0)
        @foreach($raices as $raiz)
            @include('jefaturas.partials.nodo', ['nodo' => $raiz])
        @endforeach
    @else
        <p>No hay registros de estructura organizativa.</p>
    @endif
</body>
</html>
