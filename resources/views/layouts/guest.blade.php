<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGCJ') }}</title>

    <!-- Fonts -->
    <link rel="icon" href="{{ asset('img/logo-gobernacion.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Imagen de Fondo */
        .bg-login-image {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5)),
                url('/img/dama-justicia.jpg');
            background-size: cover;
            background-position: center;
        }

        /* Contenedor Principal (Ahora este es el cristal) */
        .card {
            position: relative;
            width: 320px;
            padding: 2px;
            border-radius: 22px;
            transition: all 0.3s;
            /* Aplicamos el Glassmorphism aquí para que vea hacia el fondo de pantalla */
            background-color: rgba(23, 23, 23, 0.3);
            backdrop-filter: blur(1.5px);
            -webkit-backdrop-filter: blur(20px);
        }

        /* El truco mágico: Un pseudo-elemento que crea el borde de color "hueco" */
        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 22px;
            padding: 2px;
            /* Grosor del borde */
            background-image: linear-gradient(163deg, #d6d6d6ff 0%, #313131ff 100%);

            /* Esta máscara recorta el centro, dejando solo el borde de color */
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .card:hover {
            box-shadow: 0px 0px 30px 1px rgba(255, 255, 255, 0.99);
        }

        /* Contenedor interno */
        .card2 {
            border-radius: 20px;
            transition: all 0.2s;
            /* Lo dejamos totalmente transparente porque el blur ya lo hace el padre (.card) */
            background-color: transparent;
            height: 100%;
            width: 100%;
        }

        .card2:hover {
            transform: scale(0.98);
        }

        .form {
            display: flex;
            flex-direction: column;
            padding: 2em;
            background-color: transparent;
        }

        #heading {
            text-align: center;
            margin-bottom: 2em;
        }

        /* Inputs con ligero Blur también */
        .field {
            display: flex;
            align-items: center;
            gap: 0.5em;
            border-radius: 25px;
            padding: 0.7em 1em;
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            box-shadow: inset 2px 5px 10px rgba(0, 0, 0, 0.2);
        }

        .input-field {
            background: none;
            border: none;
            outline: none;
            width: 100%;
            color: #fff;
            font-size: 0.9em;
        }

        /* Opcional: Si quieres asegurar compatibilidad con navegadores antiguos */
        .input-field::-webkit-input-placeholder {
            color: #ffffff;
            opacity: 1;
        }

        .input-field::-moz-placeholder {
            color: #ffffff;
            opacity: 1;
        }

        .input-field:-ms-input-placeholder {
            color: #ffffff;
            opacity: 1;
        }

        .input-field::placeholder {
            color: #ffffff;
            opacity: 1;
            /* Firefox baja la opacidad por defecto, esto lo evita */
        }

        .button1 {
            width: 100%;
            padding: 0.7em;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.4s ease-in-out;
        }

        .button1:hover {
            background-color: #0F172A;
            /* Azul oscuro */
        }

        .button3 {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75em;
            text-decoration: none;
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>