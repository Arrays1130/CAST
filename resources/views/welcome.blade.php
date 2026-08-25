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

    <header class="landing-nav fixed inset-x-0 top-0 z-30">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-sm font-semibold">
                <span class="cast-mark">C</span>
                CAST Studio
            </a>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('login') }}" class="rounded-full px-3 py-1.5 text-white/70 hover:text-white">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary !bg-white !text-ink hover:!bg-paper">Get started</a>
            </div>
        </div>
    </header>

    <nav class="landing-dots fixed right-5 top-1/2 z-30 hidden -translate-y-1/2 flex-col gap-2 sm:flex" aria-label="Scroll scenes">
        @foreach(['Studio', 'Submit', 'Review', 'Scan', 'Ship'] as $i => $label)
            <button type="button" class="landing-dot" data-scroll-to="{{ $i }}" aria-label="{{ $label }}"></button>
        @endforeach
    </nav>

    <main id="landing-scroll" class="relative z-10">
        <section class="landing-scene" data-landing-scene="0">
            <div class="landing-scene-copy">
                <p class="landing-kicker">Capstone Assessment Studio</p>
                <h1 class="font-display text-[2.35rem] leading-[1.05] tracking-tight sm:text-6xl lg:text-[4.2rem]">Defense-ready papers, without the Notion chaos.</h1>
                <p class="mt-6 max-w-md text-base text-white/65 sm:text-lg">Scroll — each chapter of the capstone journey gets its own studio moment.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary !bg-ember px-5 shadow-glow">Start as a student</a>
                    <a href="{{ route('login') }}" class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/85 backdrop-blur hover:bg-white/10">Adviser log in</a>
                </div>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="1">
            <div class="landing-scene-copy">
                <p class="landing-kicker">01 · Submit</p>
                <h2 class="font-display text-4xl tracking-tight sm:text-5xl">Drop the manuscript.</h2>
                <p class="mt-4 max-w-md text-white/65">PDF upload or Google Drive link — tagged, dated, and in the queue in seconds.</p>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="2">
            <div class="landing-scene-copy">
                <p class="landing-kicker">02 · Review</p>
                <h2 class="font-display text-4xl tracking-tight sm:text-5xl">Sir opens it live.</h2>
                <p class="mt-4 max-w-md text-white/65">Preview in-browser, set status, score, and leave feedback — no download-first workflow.</p>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="3">
            <div class="landing-scene-copy">
                <p class="landing-kicker">03 · Reference Detective</p>
                <h2 class="font-display text-4xl tracking-tight sm:text-5xl">Catch missing citations.</h2>
                <p class="mt-4 max-w-md text-white/65">Flags bibliography entries not used in the body — and citations missing from the list.</p>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="4">
            <div class="landing-scene-copy">
                <p class="landing-kicker">04 · Ship</p>
                <h2 class="font-display text-4xl tracking-tight sm:text-5xl">Cleared for defense.</h2>
                <p class="mt-4 max-w-md text-white/65">Versions, archive, CSV export — the whole capstone trail in one studio.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary !bg-ember px-5 shadow-glow">Join CAST</a>
                    <a href="{{ route('login') }}" class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/85 backdrop-blur hover:bg-white/10">Adviser log in</a>
                </div>
            </div>
        </section>
    </main>

    <p class="landing-scroll-hint fixed bottom-6 left-1/2 z-30 -translate-x-1/2 text-[11px] uppercase tracking-[0.2em] text-white/35">Scroll to explore</p>
</body>
</html>
