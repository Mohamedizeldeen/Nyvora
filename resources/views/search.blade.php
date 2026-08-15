@extends('layouts.app')

@section('title', $term !== '' ? 'Search: ' . $term : 'Search')
@section('description', 'Search ' . config('app.name') . ' for technology news, reviews and analysis.')

@push('head')
    {{-- Result pages should not compete with the articles themselves in search engines. --}}
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <header class="border-b border-rule bg-ink">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-black uppercase tracking-tighter text-white sm:text-5xl">Search</h1>

            <form action="{{ route('search') }}" method="GET" role="search" class="mt-6 flex max-w-2xl gap-2">
                <label for="search-page-input" class="sr-only">Search articles</label>
                <input id="search-page-input"
                       type="search"
                       name="q"
                       value="{{ $term }}"
                       placeholder="Search {{ config('app.name') }}…"
                       autocomplete="off"
                       class="w-full rounded-md border border-ink-line bg-ink-soft px-4 py-3 text-white placeholder:text-white/40 focus:border-brand focus:outline-none">
                <button type="submit"
                        class="shrink-0 rounded-md bg-brand px-5 py-3 text-sm font-bold uppercase tracking-wider text-white transition-colors hover:bg-brand-dark">
                    Search
                </button>
            </form>

            @if ($term !== '')
                <p class="mt-4 text-sm text-white/60">
                    {{ $articles->total() }} {{ Str::plural('result', $articles->total()) }} for
                    <span class="font-semibold text-white">&ldquo;{{ $term }}&rdquo;</span>
                </p>
            @endif
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-8">
                <x-article-feed :articles="$articles"
                                :heading="$term !== '' ? 'Results' : 'All stories'"
                                empty-message="No stories matched that search. Try a broader term." />
            </div>

            <div class="lg:col-span-4">
                <x-sidebar :popular="$popular" />
            </div>
        </div>
    </div>
@endsection
