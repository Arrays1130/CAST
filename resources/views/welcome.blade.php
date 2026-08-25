<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>CAST · Capstone Assessment Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/landing-3d.js'])
</head>
<body class="landing-3d font-sans text-white">
    <canvas id="cast-3d" class="landing-canvas" aria-hidden="true"></canvas>
    <div class="pointer-events-none landing-vignette"></div>
    <div class="pointer-events-none landing-grain"></div>
    <div class="landing-progress pointer-events-none" aria-hidden="true"><span class="landing-progress-fill"></span></div>
    <p class="landing-scene-counter pointer-events-none" aria-hidden="true">01</p>

    <header class="landing-nav fixed inset-x-0 top-0 z-30">
        <div class="landing-nav-shell">
            <a href="{{ url('/') }}" class="flex items-center gap-3 text-sm font-semibold">
                <span class="cast-mark landing-brand-mark">C</span>
                <span>
                    <span class="block font-display leading-none tracking-tight">CAST</span>
                    <span class="mt-1 block text-[9px] font-medium uppercase tracking-[0.22em] text-white/35">Assessment studio</span>
                </span>
            </a>
            <nav class="hidden items-center gap-5 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/45 md:flex" aria-label="Landing sections">
                <a href="#scene-0" class="hover:text-white">Studio</a>
                <a href="#scene-1" class="hover:text-white">Submit</a>
                <a href="#scene-2" class="hover:text-white">Review</a>
                <a href="#scene-4" class="hover:text-white">Ship</a>
            </nav>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('login') }}" class="landing-nav-link">Log in</a>
                <a href="{{ route('register') }}" class="landing-nav-cta">Get started <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </header>

    <nav class="landing-dots fixed right-5 top-1/2 z-30 hidden -translate-y-1/2 flex-col sm:flex" aria-label="Scroll scenes">
        @foreach(['Studio', 'Submit', 'Review', 'Scan', 'Ship'] as $i => $label)
            <button type="button" class="landing-dot" data-scroll-to="{{ $i }}" aria-label="{{ $label }}">
                <span class="landing-dot-label">{{ $label }}</span>
                <span class="landing-dot-mark"></span>
            </button>
        @endforeach
    </nav>

    <main id="landing-scroll" class="relative z-10">
        <section id="scene-0" class="landing-scene" data-landing-scene="0">
            <div class="landing-scene-copy landing-hero-grid">
                <div class="landing-copy-panel landing-hero-brand">
                    <p class="landing-kicker landing-stagger" style="--d:0"><span class="landing-live-dot"></span> Capstone Assessment Studio</p>
                    <p class="landing-brand-word landing-stagger" style="--d:1">CAST</p>
                    <h1 class="landing-gradient-text landing-stagger font-display text-[2.1rem] leading-[1.02] tracking-[-0.04em] sm:text-5xl lg:text-[3.6rem]" style="--d:2">Turn drafts into defense-ready work.</h1>
                </div>
                <div class="landing-copy-panel landing-hero-side">
                    <p class="landing-body-copy landing-stagger !mt-0" style="--d:3">Submit, review, and refine every capstone chapter inside one focused workspace — built for students and advisers.</p>
                    <div class="mt-8 flex flex-wrap gap-3 landing-stagger" style="--d:4">
                        <a href="{{ route('register') }}" class="landing-primary-cta">Start your workspace <span aria-hidden="true">→</span></a>
                        <a href="{{ route('login.adviser') }}" class="landing-secondary-cta">Adviser log in</a>
                    </div>
                    <div class="landing-proof-row landing-stagger" style="--d:5" aria-label="CAST capabilities">
                        <span>PDF preview</span><i></i><span>Live feedback</span><i></i><span>Citation scan</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="scene-1" class="landing-scene" data-landing-scene="1">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker landing-stagger" style="--d:0">01 · Submit</p>
                    <h2 class="landing-gradient-text landing-stagger font-display text-4xl leading-none tracking-tight sm:text-6xl" style="--d:1">Drop the manuscript.</h2>
                    <p class="landing-body-copy landing-stagger" style="--d:2">PDF or Google Drive — tagged, dated, and sent to the review queue in seconds.</p>
                    <div class="landing-feature-card landing-stagger" style="--d:3"><span class="landing-feature-icon">↑</span><span><strong>One clean handoff</strong><small>Files, links, versions, and deadlines</small></span></div>
                </div>
            </div>
        </section>

        <section id="scene-2" class="landing-scene" data-landing-scene="2">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker landing-stagger" style="--d:0">02 · Review</p>
                    <h2 class="landing-gradient-text landing-stagger font-display text-4xl leading-none tracking-tight sm:text-6xl" style="--d:1">Review it live.</h2>
                    <p class="landing-body-copy landing-stagger" style="--d:2">Preview in-browser, set a status, score the work, and leave feedback without downloading first.</p>
                    <div class="landing-feature-card landing-stagger" style="--d:3"><span class="landing-feature-icon">✓</span><span><strong>Feedback in context</strong><small>One paper, one review trail</small></span></div>
                </div>
            </div>
        </section>

        <section id="scene-3" class="landing-scene" data-landing-scene="3">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker landing-stagger" style="--d:0">03 · Reference Detective</p>
                    <h2 class="landing-gradient-text landing-stagger font-display text-4xl leading-none tracking-tight sm:text-6xl" style="--d:1">Catch citation gaps.</h2>
                    <p class="landing-body-copy landing-stagger" style="--d:2">Spot unused references and in-text citations missing from the bibliography before submission.</p>
                    <div class="landing-feature-card landing-stagger" style="--d:3"><span class="landing-feature-icon">⌕</span><span><strong>Scan before defense</strong><small>Find issues while they are still easy to fix</small></span></div>
                </div>
            </div>
        </section>

        <section id="scene-4" class="landing-scene" data-landing-scene="4">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker landing-stagger" style="--d:0">04 · Ship</p>
                    <h2 class="landing-gradient-text landing-stagger font-display text-4xl leading-none tracking-tight sm:text-6xl" style="--d:1">Cleared for defense.</h2>
                    <p class="landing-body-copy landing-stagger" style="--d:2">Keep every version, decision, and approval in one complete capstone trail.</p>
                    <div class="mt-9 flex flex-wrap gap-3 landing-stagger" style="--d:3">
                        <a href="{{ route('register') }}" class="landing-primary-cta">Join CAST <span aria-hidden="true">→</span></a>
                        <a href="{{ route('login.adviser') }}" class="landing-secondary-cta">Adviser log in</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="landing-scroll-hint fixed bottom-6 left-1/2 z-30 -translate-x-1/2" aria-hidden="true">
        <span class="landing-scroll-wheel"><i></i></span>
        <span>Scroll to explore</span>
    </div>
</body>
</html>
