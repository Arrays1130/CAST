<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>CAST · Capstone Assessment Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/landing-3d.js'])
</head>
<body class="landing-3d bg-[#07080c] font-sans text-white">
    <div class="pointer-events-none fixed inset-0 landing-grain"></div>
    <header class="relative z-20 mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
        <a href="{{ url('/') }}" class="flex items-center gap-2 text-sm font-semibold">
            <span class="cast-mark">C</span>
            CAST Studio
        </a>
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('login') }}" class="rounded-full px-3 py-1.5 text-white/70 hover:text-white">Log in</a>
            <a href="{{ route('register') }}" class="btn-primary !bg-white !text-ink hover:!bg-paper">Get started</a>
        </div>
    </header>

    <main class="relative z-10 mx-auto grid min-h-[calc(100vh-5rem)] max-w-6xl items-center gap-8 px-6 pb-16 pt-6 lg:grid-cols-[1.05fr_0.95fr] lg:pb-10">
        <div class="relative z-10 max-w-xl lg:max-w-none">
            <p class="mb-4 inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs uppercase tracking-[0.2em] text-white/55">Capstone Assessment Studio</p>
            <h1 class="font-display text-[2.15rem] leading-[1.08] tracking-tight sm:text-6xl lg:text-7xl">Defense-ready papers, without the Notion chaos.</h1>
            <p class="mt-6 max-w-xl text-lg text-white/60">Students drop a manuscript. Advisers review, comment, score, and clear it for defense — all in one dark studio.</p>
            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn-primary !bg-ember shadow-glow">Start as a student</a>
                <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-4 py-2 text-sm font-medium text-white/80 hover:bg-white/10">Adviser log in</a>
            </div>
            <div class="mt-12 grid max-w-xl gap-3 text-left sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <div class="text-ember">01</div>
                    <div class="mt-2 font-medium">Submit</div>
                    <p class="mt-1 text-sm text-white/50">File or Drive link, tags, due date.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <div class="text-ember">02</div>
                    <div class="mt-2 font-medium">Review</div>
                    <p class="mt-1 text-sm text-white/50">Status, score, remarks, comments.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <div class="text-ember">03</div>
                    <div class="mt-2 font-medium">Ship</div>
                    <p class="mt-1 text-sm text-white/50">Versions, archive, CSV export.</p>
                </div>
            </div>
        </div>

        <div class="relative h-[380px] w-full sm:h-[460px] lg:h-[620px]">
            <div class="absolute inset-8 rounded-full bg-ember/20 blur-3xl"></div>
            <div class="absolute right-4 top-10 h-40 w-40 rounded-full bg-[#38bdf8]/20 blur-3xl"></div>
            <canvas id="cast-3d" class="relative z-10 h-full w-full" aria-hidden="true"></canvas>
            <p class="pointer-events-none absolute bottom-3 left-1/2 z-10 -translate-x-1/2 text-[11px] uppercase tracking-[0.18em] text-white/35">Move your cursor</p>
        </div>
    </main>
</body>
</html>
