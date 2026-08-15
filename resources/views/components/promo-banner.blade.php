{{--
    Optional announcement strip — events, live coverage, a newsletter push.

    Drop it in (or delete it) anywhere in a page; nothing else depends on it.

    Usage:
      <x-promo-banner eyebrow="Nyvora Live" href="{{ route('about') }}" cta="Get tickets">
          Join us in Berlin on 14 November for a day on what ships next.
      </x-promo-banner>
--}}
@props([
    'eyebrow' => null,
    'href' => null,
    'cta' => null,
    'tone' => 'accent', // accent (amber) | brand (violet) | ink (near-black)
])

@php
    $tones = [
        'accent' => ['bar' => 'bg-accent', 'text' => 'text-ink', 'eyebrow' => 'bg-ink text-accent', 'cta' => 'bg-ink text-white hover:bg-ink-soft'],
        'brand' => ['bar' => 'bg-brand', 'text' => 'text-white', 'eyebrow' => 'bg-white text-brand', 'cta' => 'bg-white text-brand hover:bg-white/90'],
        'ink' => ['bar' => 'bg-ink', 'text' => 'text-white', 'eyebrow' => 'bg-accent text-ink', 'cta' => 'bg-accent text-ink hover:bg-accent/90'],
    ];

    $style = $tones[$tone] ?? $tones['accent'];
@endphp

<aside {{ $attributes->class([$style['bar'], $style['text']]) }} role="region" aria-label="Announcement">
    <div class="mx-auto flex max-w-7xl flex-col items-start gap-3 px-4 py-3.5 sm:flex-row sm:items-center sm:px-6 lg:px-8">
        @if ($eyebrow)
            <span class="{{ $style['eyebrow'] }} shrink-0 rounded-sm px-2 py-1 text-[11px] font-black uppercase tracking-[0.14em]">
                {{ $eyebrow }}
            </span>
        @endif

        <p class="flex-1 text-sm font-semibold leading-snug">
            {{ $slot }}
        </p>

        @if ($href && $cta)
            <a href="{{ $href }}"
               class="{{ $style['cta'] }} shrink-0 rounded-md px-4 py-2 text-xs font-bold uppercase tracking-wider transition-colors">
                {{ $cta }}
            </a>
        @endif
    </div>
</aside>
