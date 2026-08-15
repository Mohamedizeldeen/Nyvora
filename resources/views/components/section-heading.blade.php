{{--
    Section title with the brand rule underneath — "Latest News", "Related",
    "More in Security", and so on.

    Usage: <x-section-heading id="latest-news">Latest News</x-section-heading>
--}}
@props([
    'id' => null,
    'accent' => null, // hex override; defaults to the brand colour
])

<div {{ $attributes->class('mb-6 border-b-2 border-rule') }}>
    <h2 @if ($id) id="{{ $id }}" @endif
        class="-mb-0.5 inline-block border-b-4 pb-2 text-xl font-black uppercase tracking-tight text-ink sm:text-2xl"
        @style(['border-color: '.($accent ?? '#5B2BEF')])>
        {{ $slot }}
    </h2>
</div>
