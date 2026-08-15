{{--
    Ad placeholder sized to a standard AdSense unit.

    Usage: <x-ad-slot slot-id="ad-slot-1" size="300x250" />

    Each slot renders an empty, correctly sized container plus an HTML comment
    (visible in "view source") marking exactly where the AdSense <ins> tag goes.
    Reserving the height up front keeps ads from shifting the layout when they
    load, which is what Google's Core Web Vitals measure.
--}}
@props([
    'slotId',
    'size' => '300x250',
    'label' => 'Advertisement',
])

@php
    $units = [
        '300x250' => ['w' => 300, 'h' => 250, 'name' => 'Medium Rectangle'],
        '728x90' => ['w' => 728, 'h' => 90, 'name' => 'Leaderboard'],
        '320x100' => ['w' => 320, 'h' => 100, 'name' => 'Large Mobile Banner'],
        '300x600' => ['w' => 300, 'h' => 600, 'name' => 'Half Page'],
    ];

    $unit = $units[$size] ?? $units['300x250'];
@endphp

<aside {{ $attributes->merge(['class' => 'flex flex-col items-center']) }} aria-label="{{ $label }}">
    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">{{ $label }}</p>

    <div id="{{ $slotId }}"
         class="flex w-full items-center justify-center rounded-sm border border-dashed border-rule bg-paper-soft"
         style="max-width: {{ $unit['w'] }}px; height: {{ $unit['h'] }}px;">

        <!-- ==========================================================
             ADSENSE SLOT: {{ $slotId }} — {{ $unit['name'] }} ({{ $unit['w'] }}x{{ $unit['h'] }})
             Paste the <ins class="adsbygoogle"> unit for this slot here,
             then delete the placeholder <span> below.
             The site-level adsbygoogle.js loader goes in layouts/app.blade.php.
             ========================================================== -->

        <span class="select-none text-[11px] font-medium uppercase tracking-widest text-ink/25">
            {{ $unit['w'] }} &times; {{ $unit['h'] }}
        </span>
    </div>
</aside>
