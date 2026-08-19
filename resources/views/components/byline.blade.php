{{--
    "By Maya Okonkwo · 3 hours ago" — the metadata line under a headline.

    Timestamps use Carbon's diffForHumans(), with the exact date exposed through
    a <time datetime="..."> element for machines and hover tooltips.

    The author name links to their profile, except inside a card that is already
    one big link — nesting <a> inside <a> is invalid HTML, so `linked="false"`
    turns it back into plain text there.

    Usage: <x-byline :article="$article" :avatar="true" />
--}}
@props([
    'article',
    'avatar' => false,
    'linked' => true,
])

<div {{ $attributes->class('flex items-center gap-2.5 text-sm text-ink/55') }}>
    @if ($avatar && $article->author)
        <x-author-avatar :author="$article->author" class="size-9 text-xs" />
    @endif

    <span class="min-w-0">
        @if ($article->author)
            @if ($linked)
                <a href="{{ route('authors.show', $article->author) }}"
                   class="relative z-10 font-semibold text-ink/75 transition-colors hover:text-brand">
                    {{ $article->author->name }}
                </a>
            @else
                <span class="font-semibold text-ink/75">{{ $article->author->name }}</span>
            @endif
            <span aria-hidden="true" class="mx-1 text-ink/30">&middot;</span>
        @endif

        @if ($article->published_at)
            <time datetime="{{ $article->published_at->toIso8601String() }}"
                  title="{{ $article->published_at->format('j F Y, H:i') }}">
                {{ $article->published_at->diffForHumans() }}
            </time>
        @endif
    </span>
</div>
