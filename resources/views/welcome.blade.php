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
<body class="landing-3d font-sans text-white">
    <canvas id="cast-3d" class="landing-canvas" aria-hidden="true"></canvas>
    <div class="pointer-events-none landing-vignette"></div>
    <div class="pointer-events-none landing-grain"></div>

    <header class="landing-nav">
        <a href="{{ url('/') }}" class="flex items-center gap-2 text-sm font-semibold">
            <span class="cast-mark">C</span>
            CAST Studio
        </a>
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('login') }}" class="rounded-full px-3 py-1.5 text-white/70 hover:text-white">Log in</a>
            <a href="{{ route('register') }}" class="btn-primary !bg-white !text-ink hover:!bg-paper">Get started</a>
        </div>
    </header>

    <main class="relative z-10 mx-auto flex min-h-[100dvh] max-w-6xl flex-col justify-end px-6 pb-10 pt-28 sm:justify-center sm:pb-16 lg:pt-16">
        <div class="landing-copy max-w-xl lg:max-w-[34rem]">
            <p class="mb-5 inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] uppercase tracking-[0.22em] text-white/55 backdrop-blur">Capstone Assessment Studio</p>
            <h1 class="font-display text-[2.4rem] leading-[1.05] tracking-tight sm:text-6xl lg:text-[4.4rem]">Defense-ready papers, without the Notion chaos.</h1>
            <p class="mt-6 max-w-md text-base text-white/65 sm:text-lg">Students drop a manuscript. Advisers review, comment, score, and clear it for defense — live, in one dark studio.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn-primary !bg-ember px-5 shadow-glow">Start as a student</a>
                <a href="{{ route('login') }}" class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/85 backdrop-blur hover:bg-white/10">Adviser log in</a>
            </div>
            <p class="mt-6 text-[11px] uppercase tracking-[0.2em] text-white/35">Move your cursor — the studio follows</p>
        </div>

        <div class="landing-steps mt-14 grid max-w-xl gap-3 sm:grid-cols-3 lg:mt-20">
            <div class="landing-card">
                <div class="text-ember">01</div>
                <div class="mt-2 font-medium">Submit</div>
                <p class="mt-1 text-sm text-white/50">PDF, Drive, tags, due date.</p>
            </div>
            <div class="landing-card">
                <div class="text-ember">02</div>
                <div class="mt-2 font-medium">Review</div>
                <p class="mt-1 text-sm text-white/50">Status, score, remarks, scan.</p>
            </div>
            <div class="landing-card">
                <div class="text-ember">03</div>
                <div class="mt-2 font-medium">Ship</div>
                <p class="mt-1 text-sm text-white/50">Versions, archive, CSV.</p>
            </div>
        </div>
    </main>
</body>
</html>
