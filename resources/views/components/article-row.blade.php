{{--
    One row of the "Latest News" list: square thumbnail, category tag, headline,
    byline and relative timestamp.

    The headline is the link and its ::after covers the row, so clicking
    anywhere on the row opens the story while the category tag stays its own
    link. The row itself is `relative` for that to resolve correctly.

    Usage: <x-article-row :article="$article" />
--}}
@props(['article'])

<article class="group relative flex items-start gap-4 py-5 sm:gap-5">

    {{-- Square thumbnail --}}
    <x-thumbnail :article="$article"
                 sizes="(min-width: 640px) 128px, 96px"
                 class="size-24 shrink-0 rounded-lg sm:size-32" />

    <div class="min-w-0 flex-1">
        @if ($article->category)
            <x-category-label :category="$article->category" class="relative z-10" />
        @endif

        <h3 class="mt-1.5 text-base font-extrabold leading-snug tracking-tight text-ink sm:text-lg">
            <a href="{{ route('article.show', $article) }}"
               class="transition-colors after:absolute after:inset-0 after:content-[''] group-hover:text-brand">
                {{ $article->title }}
            </a>
        </h3>

        @if ($article->excerpt)
            {{-- max-sm:hidden rather than "hidden sm:block": line-clamp needs to
                 own the display property, so a `block` utility would cancel it. --}}
            <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-ink/60 max-sm:hidden">
                {{ $article->excerpt }}
            </p>
        @endif

        <x-byline :article="$article" class="mt-2 text-xs sm:text-sm" />
    </div>
</article>
