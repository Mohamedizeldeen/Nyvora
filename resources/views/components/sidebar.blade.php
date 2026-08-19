{{--
    Right-hand rail: ad unit, "Most Popular" widget and — while the newsletter
    is switched on in Settings — the signup form.

    Sticks to the top of the viewport on large screens so it stays visible as
    the reader scrolls a long feed.

    Usage: <x-sidebar :popular="$popular" />
--}}
@props([
    'popular' => [],
    'popularHeading' => 'Most Popular',
])

<aside class="space-y-8 lg:sticky lg:top-24" aria-label="Sidebar">

    {{-- 1. Ad unit — 300x250 medium rectangle --}}
    <x-ad-slot slot-id="ad-slot-1" size="300x250" placement="sidebar" />

    {{-- 2. Most Popular --}}
    @if (count($popular))
        <section class="rounded-xl bg-brand p-5 text-white" aria-labelledby="most-popular">
            <h2 id="most-popular" class="text-lg font-black uppercase tracking-tight">
                {{ $popularHeading }}
            </h2>

            <ol class="mt-4 space-y-4">
                @foreach ($popular as $article)
                    <li class="flex gap-3.5">
                        <span aria-hidden="true"
                              class="shrink-0 text-2xl font-black leading-none text-white/35 tabular-nums">
                            {{ $loop->iteration }}
                        </span>

                        <div class="min-w-0">
                            <h3 class="text-sm font-bold leading-snug">
                                <a href="{{ route('article.show', $article) }}"
                                   class="transition-colors hover:text-accent">
                                    {{ $article->title }}
                                </a>
                            </h3>
                            <p class="mt-1 text-xs text-white/55">
                                {{ number_format($article->views_count) }} views
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    {{-- 3. Newsletter signup — only while the newsletter is switched on --}}
    @if (newsletter_enabled())
        <section id="newsletter" class="rounded-xl border border-rule bg-paper-soft p-5" aria-labelledby="newsletter-heading">
            <h2 id="newsletter-heading" class="text-lg font-black uppercase tracking-tight text-ink">
                The Daily Brief
            </h2>
            <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                One email each morning with the stories that actually matter. No spam, unsubscribe anytime.
            </p>

            @if (session('subscribed'))
                <p class="mt-4 rounded-md border border-brand/30 bg-brand/5 px-3.5 py-2.5 text-sm font-semibold text-brand-dark"
                   role="status">
                    {{ session('subscribed') }}
                </p>
            @else
                <form action="{{ route('subscribe') }}" method="POST" class="mt-4 space-y-2.5">
                    @csrf

                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input id="newsletter-email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autocomplete="email"
                           placeholder="you@example.com"
                           @class([
                               'w-full rounded-md border bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink/35 focus:border-brand focus:outline-none',
                               'border-red-500' => $errors->has('email'),
                               'border-rule' => ! $errors->has('email'),
                           ])
                           @if ($errors->has('email')) aria-invalid="true" aria-describedby="newsletter-email-error" @endif>

                    @error('email')
                        <p id="newsletter-email-error" class="text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            class="w-full rounded-md bg-brand px-4 py-2.5 text-sm font-bold uppercase tracking-wider text-white transition-colors hover:bg-brand-dark">
                        Subscribe
                    </button>
                </form>

                <p class="mt-3 text-xs leading-relaxed text-ink/45">
                    By subscribing you agree to our
                    <a href="{{ route('privacy-policy') }}" class="underline underline-offset-2 hover:text-brand">privacy policy</a>.
                </p>
            @endif
        </section>
    @endif

</aside>
