{{--
    Vertical card — image on top, headline underneath. Used for the "Related"
    grid below a story.

    Usage: <x-article-card :article="$article" />
--}}
@props(['article'])

<article class="group relative flex flex-col">
    <a href="{{ route('article.show', $article) }}" class="block">
        <x-thumbnail :article="$article"
                     sizes="(min-width: 1024px) 320px, (min-width: 640px) 50vw, 100vw"
                     class="aspect-[16/10] w-full rounded-lg" />

        <h3 class="mt-3.5 text-base font-extrabold leading-snug tracking-tight text-ink transition-colors group-hover:text-brand">
            {{ $article->title }}
        </h3>
    </a>

    @if ($article->category)
        <x-category-label :category="$article->category" variant="chip" class="absolute left-3 top-3" />
    @endif

    <x-byline :article="$article" class="mt-2 text-xs" />
</article>
