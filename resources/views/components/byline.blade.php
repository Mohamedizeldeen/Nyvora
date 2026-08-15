{{--
    "By Maya Okonkwo · 3 hours ago" — the metadata line under a headline.

    Timestamps use Carbon's diffForHumans(), with the exact date exposed through
    a <time datetime="..."> element for machines and hover tooltips.

    Usage: <x-byline :article="$article" :avatar="true" />
--}}
@props([
    'article',
    'avatar' => false,
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5 text-sm text-ink/55']) }}>
    @if ($avatar && $article->author)
        <span class="relative flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand text-xs font-bold text-white">
            {{-- Initials sit underneath as the fallback if the avatar fails to load. --}}
            {{ $article->author->initials() }}
            @if ($article->author->avatar_url)
                <img src="{{ $article->author->avatar_url }}"
                     alt=""
                     loading="lazy"
                     decoding="async"
                     class="absolute inset-0 size-full object-cover"
                     onerror="this.remove()">
            @endif
        </span>
    @endif

    <span class="min-w-0">
        @if ($article->author)
            <span class="font-semibold text-ink/75">{{ $article->author->name }}</span>
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
