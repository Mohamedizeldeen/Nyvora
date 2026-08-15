@extends('layouts.app')

@section('title', 'Page not found')
@section('description', 'That page does not exist. Browse the latest technology news instead.')

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6 sm:py-28">
        <p class="text-7xl font-black tracking-tighter text-brand sm:text-8xl">404</p>

        <h1 class="mt-4 text-3xl font-black tracking-tighter sm:text-4xl">
            We cannot find that page
        </h1>

        <p class="mx-auto mt-4 max-w-lg text-base leading-relaxed text-ink/60">
            The story may have moved, or the link may be wrong. Try one of the sections below,
            or search for what you were after.
        </p>

        <form action="{{ route('search') }}" method="GET" role="search"
              class="mx-auto mt-8 flex max-w-md gap-2">
            <label for="notfound-search" class="sr-only">Search articles</label>
            <input id="notfound-search" type="search" name="q"
                   placeholder="Search {{ config('app.name') }}…"
                   class="w-full rounded-md border border-rule px-4 py-2.5 text-sm focus:border-brand focus:outline-none">
            <button type="submit" class="btn-primary shrink-0">Search</button>
        </form>

        <div class="mt-10 flex flex-wrap justify-center gap-2">
            @foreach (\App\Models\Category::query()->orderBy('name')->get() as $category)
                <a href="{{ route('category.show', $category) }}"
                   class="rounded-full px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-white transition-opacity hover:opacity-85"
                   style="background-color: {{ $category->displayColor() }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <p class="mt-10">
            <a href="{{ route('home') }}" class="text-sm font-bold text-brand hover:text-brand-dark">
                &larr; Back to the homepage
            </a>
        </p>
    </div>
@endsection
