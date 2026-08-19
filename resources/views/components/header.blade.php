{{--
    Sticky dark navbar.

    Left   — the wordmark
    Centre — one link per row in the `categories` table ($navCategories is
             supplied by the view composer in AppServiceProvider)
    Right  — search toggle and, on small screens, the hamburger

    The two toggles are wired up in resources/js/app.js via the data-* hooks.
--}}
<header class="sticky top-0 z-50 bg-ink text-white shadow-lg shadow-black/10">
    <nav aria-label="Main navigation" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">

            {{-- Wordmark --}}
            <a href="{{ route('home') }}"
               class="shrink-0 text-2xl font-black uppercase tracking-tight"
               aria-label="{{ config('app.name') }} home">
                {{ config('app.name') }}<span class="text-brand">.</span>
            </a>

            {{-- Category nav (desktop) --}}
            <ul class="hidden flex-1 items-center justify-center gap-7 lg:flex">
                @foreach ($navCategories as $category)
                    {{-- On a 404 for an unknown slug the route parameter is still the
                         raw string, not a model, so check the type before calling is(). --}}
                    @php($routeCategory = request()->route('category'))
                    @php($isActive = $routeCategory instanceof \App\Models\Category && $routeCategory->is($category))
                    <li>
                        <a href="{{ route('category.show', $category) }}"
                           @class([
                               'text-xs font-bold uppercase tracking-widest transition-colors hover:text-brand-light',
                               'text-brand-light' => $isActive,
                               'text-white/80' => ! $isActive,
                           ])
                           @if ($isActive) aria-current="page" @endif>
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- Actions --}}
            <div class="flex shrink-0 items-center gap-1">
                {{-- Shortcut back to the dashboard, only for signed-in staff. --}}
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}"
                           class="mr-1 hidden rounded-md border border-ink-line px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white/70 transition-colors hover:border-brand hover:text-white sm:inline-block">
                            Admin
                        </a>
                    @endif
                @endauth

                <button type="button"
                        data-search-toggle
                        aria-expanded="false"
                        aria-controls="site-search"
                        class="rounded-md p-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white">
                    <span class="sr-only">Search</span>
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                    </svg>
                </button>

                <button type="button"
                        data-nav-toggle
                        aria-expanded="false"
                        aria-controls="mobile-nav"
                        class="rounded-md p-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white lg:hidden">
                    <span class="sr-only">Open menu</span>
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Search panel, revealed by the search button --}}
        <div id="site-search" data-search-panel hidden class="border-t border-ink-line py-3">
            <form action="{{ route('search') }}" method="GET" role="search" class="flex items-center gap-2">
                <label for="site-search-input" class="sr-only">Search articles</label>
                <input id="site-search-input"
                       data-search-input
                       type="search"
                       name="q"
                       value="{{ request()->routeIs('search') ? request()->query('q') : '' }}"
                       placeholder="Search {{ config('app.name') }}…"
                       autocomplete="off"
                       class="w-full rounded-md border border-ink-line bg-ink-soft px-4 py-2.5 text-sm text-white placeholder:text-white/40 focus:border-brand focus:outline-none">
                <button type="submit"
                        class="rounded-md bg-brand px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-brand-dark">
                    Search
                </button>
            </form>
        </div>

        {{-- Category nav (mobile), revealed by the hamburger --}}
        <div id="mobile-nav" data-nav-panel hidden class="border-t border-ink-line py-3 lg:hidden">
            <ul class="flex flex-col">
                @foreach ($navCategories as $category)
                    <li>
                        <a href="{{ route('category.show', $category) }}"
                           class="flex items-center gap-3 rounded-md px-2 py-3 text-sm font-bold uppercase tracking-widest text-white/80 transition-colors hover:bg-white/5 hover:text-white">
                            <span class="size-2.5 rounded-full" style="background-color: {{ $category->displayColor() }}"></span>
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
                <li class="mt-2 border-t border-ink-line pt-2">
                    <a href="{{ route('about') }}" class="block rounded-md px-2 py-3 text-sm font-semibold text-white/70 hover:bg-white/5 hover:text-white">About</a>
                </li>
                <li>
                    <a href="{{ route('contact') }}" class="block rounded-md px-2 py-3 text-sm font-semibold text-white/70 hover:bg-white/5 hover:text-white">Contact</a>
                </li>
            </ul>
        </div>
    </nav>
</header>
