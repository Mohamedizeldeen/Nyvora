@extends('layouts.app')

@section('title', $author->name)
@section('description', $author->bio ? Str::limit($author->bio, 155) : $author->name . ' writes for ' . config('app.name') . '.')
@section('og_type', 'profile')
@if ($author->avatar_url)
    @section('og_image', $author->avatar_url)
@endif

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'mainEntity' => array_filter([
                '@type' => 'Person',
                'name' => $author->name,
                'description' => $author->bio,
                'image' => $author->avatar_url,
                'url' => route('authors.show', $author),
                'worksFor' => ['@type' => 'NewsMediaOrganization', 'name' => config('app.name')],
            ]),
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Authors', 'item' => route('authors.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $author->name, 'item' => route('authors.show', $author)],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
@endpush

@section('content')
    <header class="bg-ink">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <nav aria-label="Breadcrumb" class="text-xs font-semibold uppercase tracking-widest text-white/45">
                <a href="{{ route('home') }}" class="transition-colors hover:text-brand-light">Home</a>
                <span aria-hidden="true" class="mx-2">/</span>
                <a href="{{ route('authors.index') }}" class="transition-colors hover:text-brand-light">Authors</a>
            </nav>

            <div class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-center">
                <x-author-avatar :author="$author" class="size-20 text-2xl" />

                <div class="min-w-0">
                    <h1 class="text-3xl font-black tracking-tighter text-white sm:text-5xl">{{ $author->name }}</h1>
                    <p class="mt-2 text-sm font-medium text-white/50">
                        {{ $articles->total() }} {{ Str::plural('story', $articles->total()) }}
                    </p>
                </div>
            </div>

            @if ($author->bio)
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/65">{{ $author->bio }}</p>
            @endif
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-8">
                <x-article-feed :articles="$articles"
                                :heading="'Stories by ' . $author->name"
                                empty-message="Nothing published yet." />
            </div>

            <div class="lg:col-span-4">
                <x-sidebar :popular="$popular" :popular-heading="'Most read by ' . $author->name" />
            </div>
        </div>
    </div>
@endsection
