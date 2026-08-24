<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ isset($title) ? $title.' · ' : '' }}CAST</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans" x-data>
        @php
            $palette = [
                ['label' => 'Papers', 'hint' => 'Studio database', 'href' => route('papers.index')],
                ['label' => 'Updates', 'hint' => 'Inbox', 'href' => route('notices.index')],
                ['label' => 'Settings', 'hint' => 'Account', 'href' => route('profile.edit')],
            ];
            if (auth()->user()->isStudent()) {
                $palette[] = ['label' => 'New paper', 'hint' => 'Submit manuscript', 'href' => route('papers.create')];
            }
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]" x-data="commandPalette(@js($palette))">
            <div x-show="nav" x-cloak class="fixed inset-0 z-30 bg-ink/50 lg:hidden" @click="nav = false"></div>

            <aside
                class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col bg-ink px-3 py-3 text-white transition-transform lg:static lg:translate-x-0"
                :class="nav ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                <div class="flex items-center gap-2.5 rounded-xl px-2 py-2">
                    <span class="cast-mark">C</span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[15px] font-semibold tracking-tight">CAST Studio</div>
                        <div class="text-[11px] text-white/40">Capstone Assessment</div>
                    </div>
                </div>

                <button type="button" @click="open = true" class="mt-3 flex w-full items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-[13px] text-white/60 hover:bg-white/10">
                    <span>Search</span>
                    <span class="k-chip ml-auto">Ctrl K</span>
                </button>

                <nav class="mt-4 space-y-1">
                    <a href="{{ route('papers.index') }}" class="nav-item {{ request()->routeIs('papers.index', 'papers.show', 'papers.export') ? 'nav-item-active' : '' }}">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-white/10 text-xs">▣</span>
                        Papers
                    </a>
                    <a href="{{ route('notices.index') }}" class="nav-item {{ request()->routeIs('notices.*') ? 'nav-item-active' : '' }}">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-white/10 text-xs">◉</span>
                        Updates
                        @if(($unreadNotices ?? 0) > 0)
                            <span class="ml-auto rounded-full bg-ember px-1.5 text-[10px] font-semibold">{{ $unreadNotices }}</span>
                        @endif
                    </a>
                    @if(auth()->user()->isStudent())
                        <a href="{{ route('papers.create') }}" class="nav-item {{ request()->routeIs('papers.create') ? 'nav-item-active' : '' }}">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-ember/20 text-xs text-ember">+</span>
                            New paper
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'nav-item-active' : '' }}">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-white/10 text-xs">⚙</span>
                        Settings
                    </a>
                </nav>

                <div class="mt-auto rounded-2xl border border-white/10 bg-white/5 p-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-gradient-to-br from-[#7dd3fc] to-[#2563eb] text-[11px] font-bold">{{ auth()->user()->initials() }}</span>
                        <div class="min-w-0">
                            <div class="truncate text-[13px] font-medium">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-white/40">{{ auth()->user()->isTeacher() ? 'Adviser' : 'Student' }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full rounded-xl px-2 py-1.5 text-left text-[12px] text-white/45 hover:bg-white/10 hover:text-white">Log out</button>
                    </form>
                </div>
            </aside>

            <main class="min-h-screen">
                <div class="sticky top-0 z-20 flex items-center justify-between border-b border-notion-line/80 bg-paper/80 px-4 py-3 backdrop-blur lg:hidden">
                    <button type="button" @click="nav = true" class="rounded-xl border border-notion-line bg-white px-3 py-1.5 text-sm">Menu</button>
                    <div class="flex items-center gap-2 text-sm font-semibold"><span class="cast-mark !h-6 !w-6 !text-[11px]">C</span> CAST</div>
                    <button type="button" @click="open = true" class="rounded-xl border border-notion-line bg-white px-3 py-1.5 text-sm">Search</button>
                </div>

                @if(session('status') && ! request()->routeIs('profile.*'))
                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3600)" class="studio-toast">
                        {{ session('status') }}
                    </div>
                @endif
                {{ $slot }}
            </main>

            <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-ink/50 backdrop-blur-sm" @click.self="open = false">
                <div class="mx-auto mt-[14vh] w-full max-w-xl overflow-hidden rounded-3xl border border-white/10 bg-[#1a1d26] shadow-notion" @click.stop x-trap.noscroll="open">
                    <div class="flex items-center gap-2 border-b border-white/10 px-4">
                        <input x-model="q" x-ref="search" @keydown.enter.prevent="results[0] && go(results[0].href)" type="search" placeholder="Jump to a page…" class="w-full border-0 bg-transparent py-4 text-sm text-white placeholder:text-white/35 focus:ring-0">
                        <span class="k-chip">esc</span>
                    </div>
                    <div class="max-h-72 overflow-y-auto p-2">
                        <template x-for="item in results" :key="item.href">
                            <button type="button" @click="go(item.href)" class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm text-white/80 hover:bg-white/10">
                                <span x-text="item.label"></span>
                                <span class="text-xs text-white/35" x-text="item.hint"></span>
                            </button>
                        </template>
                        <p x-show="results.length === 0" class="px-2 py-8 text-center text-sm text-white/35">Nothing matches that.</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
