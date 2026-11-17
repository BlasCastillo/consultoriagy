<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gaceta Oficial - Sumario (acto único)</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0;
        }

        body {
            margin: 0;
        }
    </style>
</head>

<body>
    @include('gacetas.partials.hoja_portada_acto_unico', ['paraPdf' => true])
</body>

</html>
