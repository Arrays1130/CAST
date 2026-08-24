<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'CAST' }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="grid min-h-screen lg:grid-cols-2">
            <div class="relative hidden overflow-hidden bg-ink text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
                <div class="pointer-events-none absolute -left-20 top-10 h-72 w-72 rounded-full bg-ember/30 blur-3xl"></div>
                <div class="pointer-events-none absolute bottom-0 right-0 h-80 w-80 rounded-full bg-[#2563eb]/20 blur-3xl"></div>
                <a href="{{ url('/') }}" class="relative flex items-center gap-2 text-sm font-medium">
                    <span class="cast-mark">C</span>
                    CAST Studio
                </a>
                <div class="relative max-w-md">
                    <p class="font-display text-4xl leading-tight">Capstone papers, reviewed like a production.</p>
                    <p class="mt-4 text-white/55">Submit, comment, score, and ship a defense-ready manuscript in one studio.</p>
                </div>
                <p class="relative text-xs text-white/35">Adviser workspace · student submissions · version history</p>
            </div>
            <div class="flex items-center justify-center bg-paper px-6 py-12">
                <div class="w-full max-w-sm">
                    <a href="{{ url('/') }}" class="mb-8 flex items-center gap-2 text-sm font-semibold lg:hidden">
                        <span class="cast-mark">C</span>
                        CAST
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
