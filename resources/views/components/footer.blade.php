{{--
    Site footer. $navCategories comes from the same view composer as the header.
    The legal links here are the ones AdSense review looks for.
--}}
<footer class="mt-16 bg-ink text-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-4">

            {{-- Brand blurb --}}
            <div class="md:col-span-2">
                <p class="text-2xl font-black uppercase tracking-tight">
                    {{ config('app.name') }}<span class="text-brand">.</span>
                </p>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-white/60">
                    {{ setting('footer_description') }}
                </p>
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

            {{-- Legal / company --}}
            <nav aria-labelledby="footer-company">
                <h2 id="footer-company" class="text-xs font-bold uppercase tracking-widest text-white/40">Company</h2>
                <ul class="mt-4 space-y-2.5">
                    <li><a href="{{ route('about') }}" class="text-sm text-white/75 transition-colors hover:text-brand-light">About us</a></li>
                    <li><a href="{{ route('contact') }}" class="text-sm text-white/75 transition-colors hover:text-brand-light">Contact</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="text-sm text-white/75 transition-colors hover:text-brand-light">Privacy policy</a></li>
                </ul>
            </nav>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-ink-line pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-white/45">
                &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
            </p>
            <p class="text-xs text-white/45">
                Demo site — all articles, companies and bylines are fictional.
            </p>
        </div>
    </div>
</footer>
