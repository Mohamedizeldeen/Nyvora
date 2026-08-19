@extends('layouts.app')

@section('title', 'Authors')
@section('description', 'The reporters and writers behind ' . config('app.name') . '.')

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Authors', 'item' => route('authors.index')],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>
@endpush

@section('content')
    <x-page-header title="Authors"
                   subtitle="The reporters and writers behind {{ config('app.name') }}. Every story on this site carries a byline." />

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:py-16">
        @if ($authors->isEmpty())
            <p class="rounded-lg border border-dashed border-rule bg-paper-soft px-6 py-12 text-center text-sm text-ink/50">
                No bylines yet.
            </p>
        @else
            <div class="grid gap-6 sm:grid-cols-2">
                @foreach ($authors as $author)
                    <article class="group relative flex gap-4 rounded-xl border border-rule p-5 transition-colors hover:border-brand">
                        <x-author-avatar :author="$author" class="size-14 text-base" />

                        <div class="min-w-0">
                            <h2 class="text-base font-extrabold tracking-tight text-ink transition-colors group-hover:text-brand">
                                <a href="{{ route('authors.show', $author) }}" class="after:absolute after:inset-0 after:content-['']">
                                    {{ $author->name }}
                                </a>
                            </h2>

                            <p class="mt-0.5 text-xs font-semibold uppercase tracking-wider text-ink/40">
                                {{ $author->articles_count }} {{ Str::plural('story', $author->articles_count) }}
                            </p>

                            @if ($author->bio)
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink/60">{{ $author->bio }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <p class="mt-10 text-sm text-ink/55">
            Want to know how we decide what to publish? Read our
            <a href="{{ route('editorial-policy') }}" class="font-semibold text-brand hover:text-brand-dark">editorial policy</a>,
            or see how the newsroom is organised on our
            <a href="{{ route('team') }}" class="font-semibold text-brand hover:text-brand-dark">team page</a>.
        </p>
    </div>
@endsection
