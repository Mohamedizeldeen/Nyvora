@extends('layouts.app')

@section('title', $category->name)
@section('description', 'The latest ' . $category->name . ' news, reviews and analysis from ' . config('app.name') . '.')

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $category->name, 'item' => route('category.show', $category)],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
@endpush

@section('content')

    {{-- Archive masthead, tinted with the category's own colour --}}
    <header class="border-b border-rule" style="background-color: {{ $category->displayColor() }}">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <nav aria-label="Breadcrumb" class="text-xs font-semibold uppercase tracking-widest text-white/70">
                <a href="{{ route('home') }}" class="transition-colors hover:text-white">Home</a>
                <span aria-hidden="true" class="mx-2">/</span>
                <span class="text-white">{{ $category->name }}</span>
            </nav>

            <h1 class="mt-4 text-4xl font-black uppercase tracking-tighter text-white sm:text-6xl">
                {{ $category->name }}
            </h1>
            <p class="mt-3 text-sm font-medium text-white/70">
                {{ $articles->total() }} {{ Str::plural('story', $articles->total()) }}
            </p>
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">

            <div class="lg:col-span-8">
                <x-article-feed :articles="$articles"
                                :heading="'All ' . $category->name"
                                :accent="$category->displayColor()"
                                empty-message="Nothing filed under {{ $category->name }} yet." />
            </div>

            <div class="lg:col-span-4">
                <x-sidebar :popular="$popular" :popular-heading="'Most Popular in ' . $category->name" />
            </div>
        </div>
    </div>
@endsection
