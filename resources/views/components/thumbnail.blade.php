{{--
    Article image with a graceful fallback.

    The wrapper is painted with the category colour, so if the remote
    placeholder image is slow or unreachable the reader sees a tinted block
    rather than a broken-image icon.

    Usage: <x-thumbnail :article="$article" class="aspect-square" />
--}}
@props([
    'article',
    'eager' => false,
    'sizes' => null,
    // Feed thumbnails sit right next to the headline they belong to, so they
    // are decorative and take an empty alt. The lead image on an article page
    // stands alone and gets a real description instead.
    'alt' => '',
])

<div {{ $attributes
        ->class('relative overflow-hidden bg-paper-soft')
        ->style('background-color: '.($article->category?->displayColor() ?? '#5B2BEF')) }}>
    @if ($article->thumbnail_url)
        <img src="{{ $article->thumbnail_url }}"
             alt="{{ $alt }}"
             @if ($sizes) sizes="{{ $sizes }}" @endif
             loading="{{ $eager ? 'eager' : 'lazy' }}"
             decoding="async"
             @if ($eager) fetchpriority="high" @endif
             class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
             {{-- If the image 404s, drop it so the category-coloured block shows through. --}}
             onerror="this.remove()">
    @endif
</div>
