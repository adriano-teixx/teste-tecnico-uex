<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Space+Grotesk:wght@400;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="md-background">
            <div class="md-card">
                <div class="md-card__header">
                    <a href="/" class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/60 flex items-center justify-center shadow-lg">
                            <x-application-logo class="w-8 h-8 text-indigo-600" />
                        </div>
                        <div>
                            <span class="md-pill">Material 3</span>
                            <p class="md-card__title">{{ config('app.name', 'Laravel') }}</p>
                            <p class="md-card__subtitle">Entrar em sua conta e administrar contatos com estilo.</p>
                        </div>
                    </a>
                </div>

                <div class="md-card__content">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
