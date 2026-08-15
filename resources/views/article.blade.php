@extends('layouts.app')

@section('title', $article->title)
@section('description', Str::limit($article->excerpt ?? Str::of($article->body)->stripTags(), 155))
@section('og_type', 'article')
@if ($article->thumbnail_url)
    @section('og_image', $article->thumbnail_url)
@endif

@push('head')
    {{-- Open Graph article metadata, so shared links show the right byline,
         section and publish date on social platforms. --}}
    @if ($article->published_at)
        <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
    @endif
    <meta property="article:modified_time" content="{{ $article->updated_at?->toIso8601String() }}">
    @if ($article->author)
        <meta property="article:author" content="{{ $article->author->name }}">
    @endif
    @if ($article->category)
        <meta property="article:section" content="{{ $article->category->name }}">
    @endif

    {{-- Breadcrumb trail, so search results show "Home › Security › …". --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_filter([
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                $article->category
                    ? ['@type' => 'ListItem', 'position' => 2, 'name' => $article->category->name, 'item' => route('category.show', $article->category)]
                    : null,
                ['@type' => 'ListItem', 'position' => $article->category ? 3 : 2, 'name' => $article->title],
            ])),
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>

    {{-- NewsArticle structured data. JSON_HEX_* keeps a stray "</script>" in the
         content from breaking out of this block. --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'description' => $article->excerpt,
            'image' => array_filter([$article->thumbnail_url]),
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'articleSection' => $article->category?->name,
            'author' => $article->author ? ['@type' => 'Person', 'name' => $article->author->name] : null,
            'publisher' => ['@type' => 'Organization', 'name' => config('app.name')],
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
@endpush

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">

            {{-- ============ Story ============ --}}
            <article class="lg:col-span-8">

                {{-- Breadcrumb --}}
                <nav aria-label="Breadcrumb" class="text-xs font-semibold uppercase tracking-widest text-ink/45">
                    <a href="{{ route('home') }}" class="transition-colors hover:text-brand">Home</a>
                    @if ($article->category)
                        <span aria-hidden="true" class="mx-2">/</span>
                        <a href="{{ route('category.show', $article->category) }}" class="transition-colors hover:text-brand">
                            {{ $article->category->name }}
                        </a>
                    @endif
                </nav>

                <header class="mt-5">
                    @if ($article->category)
                        <x-category-label :category="$article->category" />
                    @endif

                    <h1 class="mt-2.5 text-3xl font-black leading-[1.1] tracking-tighter text-ink sm:text-4xl lg:text-5xl">
                        {{ $article->title }}
                    </h1>

                    @if ($article->excerpt)
                        {{-- Standfirst --}}
                        <p class="mt-5 text-lg leading-relaxed text-ink/65 sm:text-xl">
                            {{ $article->excerpt }}
                        </p>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-rule pt-4">
                        <x-byline :article="$article" :avatar="true" />

                        <p class="text-xs font-medium uppercase tracking-wider text-ink/40">
                            {{ $article->readingTime() }} min read
                            <span aria-hidden="true" class="mx-1.5">&middot;</span>
                            {{ number_format($article->views_count) }} views
                        </p>
                    </div>

                    {{-- Share row, directly under the byline --}}
                    <x-share-buttons :article="$article" class="border-b border-rule py-4" />
                </header>

                {{-- Lead image --}}
                @if ($article->thumbnail_url)
                    <figure class="mt-8">
                        <x-thumbnail :article="$article" :eager="true"
                                     :alt="$article->title"
                                     sizes="(min-width: 1024px) 780px, 100vw"
                                     class="aspect-[16/9] w-full rounded-xl" />
                        <figcaption class="mt-2.5 text-xs text-ink/45">
                            Placeholder image for demonstration purposes.
                        </figcaption>
                    </figure>
                @endif

                {{-- Body --}}
                <x-article-body :body="$article->body" class="mt-9" />

                {{-- Share again once the reader has finished the story --}}
                <x-share-buttons :article="$article"
                                 label="Share"
                                 class="mt-10 rounded-xl border border-rule bg-paper-soft p-4" />

                {{-- Author card --}}
                @if ($article->author)
                    <aside class="mt-12 flex gap-4 rounded-xl border border-rule bg-paper-soft p-5"
                           aria-label="About the author">
                        <span class="relative flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand text-base font-bold text-white">
                            {{ $article->author->initials() }}
                            @if ($article->author->avatar_url)
                                <img src="{{ $article->author->avatar_url }}" alt="" loading="lazy" decoding="async"
                                     class="absolute inset-0 size-full object-cover" onerror="this.remove()">
                            @endif
                        </span>

                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/40">Written by</p>
                            <p class="mt-0.5 text-base font-extrabold tracking-tight text-ink">{{ $article->author->name }}</p>
                            @if ($article->author->bio)
                                <p class="mt-1.5 text-sm leading-relaxed text-ink/60">{{ $article->author->bio }}</p>
                            @endif
                        </div>
                    </aside>
                @endif

                {{-- Related stories --}}
                @if ($related->isNotEmpty())
                    <section class="mt-14" aria-labelledby="related-heading">
                        <x-section-heading id="related-heading"
                                           :accent="$article->category?->displayColor()">
                            More in {{ $article->category?->name ?? 'the newsroom' }}
                        </x-section-heading>

                        <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($related as $relatedArticle)
                                <x-article-card :article="$relatedArticle" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>

            {{-- ============ Sidebar ============ --}}
            <div class="lg:col-span-4">
                <x-sidebar :popular="$popular" />
            </div>
        </div>
    </div>
@endsection
