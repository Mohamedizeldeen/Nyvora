{{--
    Renders the article body.

    Bodies are stored as plain text: blank lines separate blocks, "## " marks a
    subheading and "> " marks a pull quote. Everything goes through Blade's
    escaping, so no value in the `body` column can inject markup — swap this
    component for a sanitising Markdown/HTML renderer if the CMS later stores
    rich text.

    Usage: <x-article-body :body="$article->body" />
--}}
@props(['body'])

@php
    $blocks = preg_split('/\R\s*\R/', trim((string) $body)) ?: [];
@endphp

<div {{ $attributes->class('prose-nyvora max-w-none') }}>
    @foreach ($blocks as $block)
        @php($block = trim($block))
        @continue($block === '')

        @if (str_starts_with($block, '## '))
            <h2>{{ mb_substr($block, 3) }}</h2>
        @elseif (str_starts_with($block, '> '))
            <blockquote>{{ mb_substr($block, 2) }}</blockquote>
        @else
            <p>{{ $block }}</p>
        @endif
    @endforeach
</div>
