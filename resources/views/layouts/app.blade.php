{{--
    The one page shell every route renders through.

    Child views supply:
      @section('title')       — page title, appended with the site name
      @section('description') — meta description used for SEO and social cards
      @section('og_type')     — "article" on story pages, otherwise "website"
      @section('og_image')    — social card image
      @section('content')     — the page body
      @push('head')           — anything extra for <head> (canonical overrides, JSON-LD)
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-pt-20">
<head>
    @php
        // Resolved once here so the <title> and every social tag agree.
        $siteName = config('app.name');
        $siteTagline = 'Technology news, reviews and analysis';
        $pageTitle = View::hasSection('title') ? trim(View::yieldContent('title')) : null;
        $pageDescription = View::hasSection('description')
            ? trim(View::yieldContent('description'))
            : $siteTagline.' from the '.$siteName.' newsroom.';
        $pageImage = View::hasSection('og_image') ? trim(View::yieldContent('og_image')) : null;
        $pageType = View::hasSection('og_type') ? trim(View::yieldContent('og_type')) : 'website';

        // Self-referencing canonical. Paginated pages point at themselves —
        // pointing page 2 back at page 1 hides its stories from the index.
        $page = (int) request()->query('page', 1);
        $canonical = url()->current().($page > 1 ? '?page='.$page : '');
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle ? $pageTitle.' · '.$siteName : $siteName.' · '.$siteTagline }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Feed discovery for readers and aggregators --}}
    <link rel="alternate" type="application/rss+xml"
          title="{{ $siteName }} — latest stories" href="{{ route('feed') }}">

    {{-- Social sharing cards --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $pageType }}">
    <meta property="og:title" content="{{ $pageTitle ?? $siteName }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
    @if ($pageImage)
        <meta property="og:image" content="{{ $pageImage }}">
        <meta property="og:image:alt" content="{{ $pageTitle ?? $siteName }}">
    @endif
    <meta name="twitter:card" content="{{ $pageImage ? 'summary_large_image' : 'summary' }}">

    {{-- Organisation and site-level structured data, on every page. --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'NewsMediaOrganization',
                    '@id' => url('/').'#organization',
                    'name' => $siteName,
                    'url' => url('/'),
                    'description' => $siteTagline,
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#website',
                    'name' => $siteName,
                    'url' => url('/'),
                    'publisher' => ['@id' => url('/').'#organization'],
                    // Lets Google offer a search box for the site directly in results.
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => route('search').'?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
    </script>

    <meta name="theme-color" content="#0B0B12">

    {{-- Inter, self-hosted by the Vite fonts plugin (see vite.config.js). --}}
    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--
        AdSense loader. Added automatically once a publisher ID is saved in
        Admin → Settings; the individual ad units still live in <x-ad-slot>.
    --}}
    @if ($adsenseClient = setting('adsense_client_id'))
        <script async
                src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseClient }}"
                crossorigin="anonymous"></script>
    @endif

    @stack('head')
</head>
<body class="flex min-h-full flex-col bg-paper font-sans text-ink">
    {{-- Keyboard users can jump past the navbar. --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-white">
        Skip to content
    </a>

    <x-header />

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <x-footer />
</body>
</html>
