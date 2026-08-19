{{--
    The "Latest News" list: a heading, a divided list of article rows, an
    in-feed ad unit and Laravel's paginator underneath.

    $articles is a LengthAwarePaginator, so ->links() renders real pagination.

    Usage: <x-article-feed :articles="$articles" heading="Latest News" />
--}}
@props([
    'articles',
    'heading' => 'Latest News',
    'accent' => null,
    'emptyMessage' => 'No stories here yet. Check back soon.',
    'adAfter' => 4, // insert the in-feed ad unit after this many rows
])

{{-- Prev/next hints help crawlers walk a paginated archive in order. --}}
@if ($articles->hasPages())
    @push('head')
        @if ($articles->currentPage() > 1)
            <link rel="prev" href="{{ $articles->previousPageUrl() }}">
        @endif
        @if ($articles->hasMorePages())
            <link rel="next" href="{{ $articles->nextPageUrl() }}">
        @endif
    @endpush
@endif

<section aria-labelledby="feed-heading">
    <x-section-heading id="feed-heading" :accent="$accent">{{ $heading }}</x-section-heading>

    @if ($articles->isEmpty())
        <p class="rounded-lg border border-dashed border-rule bg-paper-soft px-6 py-12 text-center text-sm text-ink/50">
            {{ $emptyMessage }}
        </p>
    @else
        {{-- Thin divider between rows --}}
        <div class="divide-y divide-rule border-b border-rule">
            @foreach ($articles as $article)
                <x-article-row :article="$article" />

                @if ($loop->iteration === $adAfter && ! $loop->last)
                    {{-- In-feed placement: a large mobile banner between stories. --}}
                    <div class="py-6">
                        <x-ad-slot slot-id="ad-slot-3" size="320x100" placement="in_feed" />
                    </div>
                @endif
            @endforeach
        </div>

        @if ($articles->hasPages())
            <nav class="pagination-nyvora mt-8" aria-label="Pagination">
                {{ $articles->onEachSide(1)->links() }}
            </nav>
        @endif
    @endif
</section>
