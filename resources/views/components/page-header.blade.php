{{--
    Dark masthead used by the static pages.

    Usage: <x-page-header title="About us" subtitle="Who we are." />
--}}
@props([
    'title',
    'subtitle' => null,
])

<header class="bg-ink">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <nav aria-label="Breadcrumb" class="text-xs font-semibold uppercase tracking-widest text-white/45">
            <a href="{{ route('home') }}" class="transition-colors hover:text-brand-light">Home</a>
            <span aria-hidden="true" class="mx-2">/</span>
            <span class="text-white/80">{{ $title }}</span>
        </nav>

        <h1 class="mt-4 text-4xl font-black uppercase tracking-tighter text-white sm:text-6xl">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/60 sm:text-lg">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</header>
