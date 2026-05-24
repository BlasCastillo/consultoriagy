<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGCJ') }}</title>

    <!-- Fonts -->
    <link rel="icon" href="{{ asset('img/logo-gobernacion.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/figtree.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/all.min.css') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Imagen de Fondo */
        .bg-login-image {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5));
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-800">
    <div class="min-h-screen bg-slate-50 bg-login-image">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow border-b border-slate-200">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>