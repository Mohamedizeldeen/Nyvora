{{--
    An ad placement.

    Renders a real AdSense unit once a publisher ID and this slot's ID are
    saved in Admin → Settings; until then it shows a correctly sized
    placeholder so the layout can still be judged.

    Either way the container reserves its height before anything loads, so an
    ad never pushes the article out from under a reader — which is what Core
    Web Vitals measures and what Google's own policies ask for.

    Usage: <x-ad-slot slot-id="ad-slot-1" size="300x250" placement="sidebar" />
--}}
@props([
    'slotId',
    'size' => '300x250',
    'label' => 'Advertisement',
    // Which configured slot ID to use: sidebar | leaderboard | in_feed
    'placement' => null,
])

@php
    $units = [
        '300x250' => ['w' => 300, 'h' => 250, 'name' => 'Medium Rectangle'],
        '728x90' => ['w' => 728, 'h' => 90, 'name' => 'Leaderboard'],
        '320x100' => ['w' => 320, 'h' => 100, 'name' => 'Large Mobile Banner'],
        '300x600' => ['w' => 300, 'h' => 600, 'name' => 'Half Page'],
    ];

    $unit = $units[$size] ?? $units['300x250'];

    $client = trim((string) setting('adsense_client_id'));
    $adSlot = $placement ? trim((string) setting('adsense_slot_'.$placement)) : '';
    $live = $client !== '' && $adSlot !== '';
@endphp

<aside {{ $attributes->merge(['class' => 'flex flex-col items-center']) }} aria-label="{{ $label }}">
    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-ink/35">{{ $label }}</p>

    <div id="{{ $slotId }}"
         @class([
             'flex w-full items-center justify-center',
             'rounded-sm border border-dashed border-rule bg-paper-soft' => ! $live,
         ])
         style="max-width: {{ $unit['w'] }}px; height: {{ $unit['h'] }}px;">

        @if ($live)
            {{-- Live unit. The height above is already reserved, so this cannot shift the page. --}}
            <ins class="adsbygoogle"
                 style="display:inline-block;width:{{ $unit['w'] }}px;height:{{ $unit['h'] }}px"
                 data-ad-client="{{ $client }}"
                 data-ad-slot="{{ $adSlot }}"></ins>
            <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
        @else
            <!-- ==========================================================
                 AD SLOT: {{ $slotId }} — {{ $unit['name'] }} ({{ $unit['w'] }}x{{ $unit['h'] }})
                 Nothing to paste in this file. Save the AdSense unit code in
                 Admin -> Settings -> Google AdSense and this becomes a live unit.
                 ========================================================== -->
            <span class="select-none text-[11px] font-medium uppercase tracking-widest text-ink/25">
                {{ $unit['w'] }} &times; {{ $unit['h'] }}
            </span>
        @endif
    </div>
</aside>
