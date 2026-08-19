{{--
    Author avatar with an initials fallback underneath, so a missing or broken
    image still renders as something deliberate rather than a broken icon.

    Usage: <x-author-avatar :author="$author" class="size-16 text-lg" />
--}}
@props(['author'])

<span {{ $attributes->class('relative flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand font-bold text-white') }}>
    {{ $author->initials() }}
    @if ($author->avatar_url)
        <img src="{{ $author->avatar_url }}"
             alt=""
             loading="lazy"
             decoding="async"
             class="absolute inset-0 size-full object-cover"
             onerror="this.remove()">
    @endif
</span>
