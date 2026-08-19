{{--
    Site footer. $navCategories comes from the same view composer as the header.

    Four columns: the brand blurb, the sections, the company pages and the legal
    pages. Splitting company from legal keeps each list short enough to scan —
    AdSense review and readers both look for these, so they are not buried.
--}}
<footer class="mt-16 bg-ink text-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-5">

            {{-- Brand blurb --}}
            <div class="lg:col-span-2">
                <p class="text-2xl font-black uppercase tracking-tight">
                    {{ config('app.name') }}<span class="text-brand">.</span>
                </p>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-white/60">
                    {{ setting('footer_description') }}
                </p>

                <a href="{{ route('feed') }}"
                   class="mt-5 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/50 transition-colors hover:text-brand-light">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6.503 20.752A3.5 3.5 0 1 1 3 17.252a3.5 3.5 0 0 1 3.503 3.5ZM1.5 10.031v3.985A9.98 9.98 0 0 1 11.484 24h3.985A13.96 13.96 0 0 0 1.5 10.031Zm0-6.031v3.985C10.343 7.985 16.015 13.657 16.015 22.5H20A18.5 18.5 0 0 0 1.5 4Z" />
                    </svg>
                    RSS feed
                </a>
            </div>

            {{-- Sections --}}
            <nav aria-labelledby="footer-sections">
                <h2 id="footer-sections" class="text-xs font-bold uppercase tracking-widest text-white/40">Sections</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($navCategories as $category)
                        <li>
                            <a href="{{ route('category.show', $category) }}"
                               class="text-sm text-white/75 transition-colors hover:text-brand-light">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Company --}}
            <nav aria-labelledby="footer-company">
                <h2 id="footer-company" class="text-xs font-bold uppercase tracking-widest text-white/40">Company</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ([
                        'about' => 'About us',
                        'team' => 'Our team',
                        'authors.index' => 'Authors',
                        'editorial-policy' => 'Editorial policy',
                        'advertise' => 'Advertise with us',
                        'contact' => 'Contact us',
                    ] as $route => $label)
                        <li>
                            <a href="{{ route($route) }}"
                               class="text-sm text-white/75 transition-colors hover:text-brand-light">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Legal --}}
            <nav aria-labelledby="footer-legal">
                <h2 id="footer-legal" class="text-xs font-bold uppercase tracking-widest text-white/40">Legal</h2>
                <ul class="mt-4 space-y-2.5">
                    @foreach ([
                        'privacy-policy' => 'Privacy policy',
                        'cookie-policy' => 'Cookie policy',
                        'terms' => 'Terms of use',
                    ] as $route => $label)
                        <li>
                            <a href="{{ route($route) }}"
                               class="text-sm text-white/75 transition-colors hover:text-brand-light">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        <div class="mt-12 border-t border-ink-line pt-6">
            <p class="text-xs text-white/45">
                &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</footer>
