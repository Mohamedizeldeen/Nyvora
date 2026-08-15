{{--
    Full-bleed brand block at the top of the homepage.

    Wordmark, then a three-column grid:
      1. the lead story  — large image with the headline overlaid
      2. a second story  — smaller, headline underneath
      3. Top Headlines   — a linked list of the most-read stories

    On each card the whole card is one <a>, and the category tag is a sibling
    link layered on top of the image — nesting <a> inside <a> is invalid HTML.

    Usage: <x-hero :primary="$heroPrimary" :secondary="$heroSecondary" :headlines="$headlines" />
--}}
@props([
    'primary' => null,
    'secondary' => null,
    'headlines' => [],
])

<section class="bg-brand" aria-labelledby="hero-wordmark">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

        {{-- Wordmark --}}
        <div class="text-center">
            <h1 id="hero-wordmark"
                class="text-5xl font-black uppercase leading-none tracking-tighter text-white sm:text-7xl lg:text-8xl">
                {{ config('app.name') }}
            </h1>
            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.3em] text-white/60 sm:text-sm">
                {{ setting('site_tagline') }}
            </p>
        </div>

        {{-- Featured grid --}}
        <div class="mt-10 grid gap-6 md:grid-cols-2 lg:mt-12 lg:grid-cols-12">

            {{-- 1. Lead story --}}
            @if ($primary)
                <article class="group relative overflow-hidden rounded-xl md:col-span-2 lg:col-span-6">
                    <a href="{{ route('article.show', $primary) }}" class="block">
                        <x-thumbnail :article="$primary" :eager="true"
                                     sizes="(min-width: 1024px) 620px, 100vw"
                                     class="aspect-[16/10] w-full" />

                        {{-- Scrim so white type stays readable over any photo --}}
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-transparent"></div>

                        <div class="absolute inset-x-0 bottom-0 p-5 sm:p-7">
                            <h2 class="text-2xl font-black leading-[1.1] tracking-tight text-white sm:text-3xl lg:text-4xl">
                                {{ $primary->title }}
                            </h2>

                            @if ($primary->author)
                                <p class="mt-3 text-sm text-white/70">
                                    {{ $primary->author->name }}
                                    <span aria-hidden="true" class="mx-1">&middot;</span>
                                    <time datetime="{{ $primary->published_at?->toIso8601String() }}">
                                        {{ $primary->published_at?->diffForHumans() }}
                                    </time>
                                </p>
                            @endif
                        </div>
                    </a>

                    @if ($primary->category)
                        <x-category-label :category="$primary->category" variant="chip"
                                          class="absolute left-5 top-5 sm:left-7 sm:top-7" />
                    @endif
                </article>
            @endif

            {{-- 2. Second story --}}
            @if ($secondary)
                <article class="group relative lg:col-span-3">
                    <a href="{{ route('article.show', $secondary) }}" class="block">
                        <x-thumbnail :article="$secondary"
                                     sizes="(min-width: 1024px) 300px, (min-width: 768px) 50vw, 100vw"
                                     class="aspect-[16/10] w-full rounded-xl" />

                        <h2 class="mt-4 text-lg font-extrabold leading-snug tracking-tight text-white">
                            {{ $secondary->title }}
                        </h2>

                        @if ($secondary->excerpt)
                            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-white/65">
                                {{ $secondary->excerpt }}
                            </p>
                        @endif
                    </a>

                    @if ($secondary->category)
                        <x-category-label :category="$secondary->category" variant="chip"
                                          class="absolute left-4 top-4" />
                    @endif
                </article>
            @endif

            {{-- 3. Top Headlines --}}
            @if (count($headlines))
                <aside class="rounded-xl bg-black/25 p-5 lg:col-span-3" aria-labelledby="top-headlines">
                    <h2 id="top-headlines"
                        class="border-b border-white/20 pb-3 text-sm font-black uppercase tracking-[0.16em] text-white">
                        Top Headlines
                    </h2>

                    <ul class="mt-1 divide-y divide-white/10">
                        @foreach ($headlines as $headline)
                            <li class="py-3">
                                <a href="{{ route('article.show', $headline) }}"
                                   class="flex gap-2.5 text-sm font-semibold leading-snug text-white/90 transition-colors hover:text-white">
                                    <span aria-hidden="true" class="mt-1.5 size-1.5 shrink-0 rounded-full bg-white/50"></span>
                                    <span>{{ $headline->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>
    </div>
</section>
