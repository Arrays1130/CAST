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
    <div class="landing-progress pointer-events-none" aria-hidden="true"><span class="landing-progress-fill"></span></div>
    <p class="landing-scene-counter pointer-events-none" aria-hidden="true">01</p>

    <header class="landing-nav fixed inset-x-0 top-0 z-30">
        <div class="landing-nav-shell">
            <a href="{{ url('/') }}" class="flex items-center gap-3 text-sm font-semibold">
                <span class="cast-mark landing-brand-mark">C</span>
                <span>
                    <span class="block leading-none">CAST</span>
                    <span class="mt-1 block text-[9px] font-medium uppercase tracking-[0.2em] text-white/35">Assessment studio</span>
                </span>
            </a>
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
        <section class="landing-scene" data-landing-scene="0">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker"><span class="landing-live-dot"></span> Capstone Assessment Studio</p>
                    <h1 class="landing-gradient-text font-display text-[2.6rem] leading-[0.98] tracking-[-0.035em] sm:text-6xl lg:text-[4.6rem]">Turn drafts into defense-ready work.</h1>
                    <p class="landing-body-copy">Submit, review, and refine every capstone chapter inside one focused workspace.</p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="landing-primary-cta">Start your workspace <span aria-hidden="true">→</span></a>
                        <a href="{{ route('login') }}" class="landing-secondary-cta">Adviser log in</a>
                    </div>
                    <div class="landing-proof-row" aria-label="CAST capabilities">
                        <span>PDF preview</span><i></i><span>Live feedback</span><i></i><span>Citation scan</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="1">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker">01 · Submit</p>
                    <h2 class="landing-gradient-text font-display text-4xl leading-none tracking-tight sm:text-6xl">Drop the manuscript.</h2>
                    <p class="landing-body-copy">PDF or Google Drive—tagged, dated, and sent to the review queue in seconds.</p>
                    <div class="landing-feature-card"><span class="landing-feature-icon">↑</span><span><strong>One clean handoff</strong><small>Files, links, versions, and deadlines</small></span></div>
                </div>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="2">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker">02 · Review</p>
                    <h2 class="landing-gradient-text font-display text-4xl leading-none tracking-tight sm:text-6xl">Review it live.</h2>
                    <p class="landing-body-copy">Preview in-browser, set a status, score the work, and leave feedback without downloading first.</p>
                    <div class="landing-feature-card"><span class="landing-feature-icon">✓</span><span><strong>Feedback in context</strong><small>One paper, one review trail</small></span></div>
                </div>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="3">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker">03 · Reference Detective</p>
                    <h2 class="landing-gradient-text font-display text-4xl leading-none tracking-tight sm:text-6xl">Catch citation gaps.</h2>
                    <p class="landing-body-copy">Spot unused references and in-text citations missing from the bibliography before submission.</p>
                    <div class="landing-feature-card"><span class="landing-feature-icon">⌕</span><span><strong>Scan before defense</strong><small>Find issues while they are still easy to fix</small></span></div>
                </div>
            </div>
        </section>

        <section class="landing-scene" data-landing-scene="4">
            <div class="landing-scene-copy">
                <div class="landing-copy-panel">
                    <p class="landing-kicker">04 · Ship</p>
                    <h2 class="landing-gradient-text font-display text-4xl leading-none tracking-tight sm:text-6xl">Cleared for defense.</h2>
                    <p class="landing-body-copy">Keep every version, decision, and approval in one complete capstone trail.</p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="landing-primary-cta">Join CAST <span aria-hidden="true">→</span></a>
                        <a href="{{ route('login') }}" class="landing-secondary-cta">Adviser log in</a>
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
