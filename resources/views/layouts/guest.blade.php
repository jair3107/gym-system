<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AURUM') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|montserrat:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <a href="/" class="flex flex-col items-center group">
                <x-application-logo class="w-16 h-16 text-gold-400 transition group-hover:scale-105" />
                <span class="mt-3 font-brand text-3xl font-bold tracking-[0.3em] text-zinc-100">AURUM</span>
                <span class="mt-1 text-[0.7rem] uppercase tracking-[0.35em] text-gold-500">Fitness Club</span>
            </a>

            <div class="w-full sm:max-w-md mt-8 px-6 py-8 card sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-zinc-600">&copy; {{ date('Y') }} AURUM Fitness Club</p>
        </div>
    </body>
</html>
