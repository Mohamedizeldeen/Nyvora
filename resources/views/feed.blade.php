{{-- RSS 2.0 feed. The declaration must be the first bytes of the response. --}}
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <description>{{ setting('site_tagline') }}</description>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
@if ($articles->isNotEmpty())
        <lastBuildDate>{{ $articles->first()->published_at?->toRfc2822String() }}</lastBuildDate>
@endif

@foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('article.show', $article) }}</link>
            {{-- The permalink doubles as the globally unique id. --}}
            <guid isPermaLink="true">{{ route('article.show', $article) }}</guid>
            <pubDate>{{ $article->published_at?->toRfc2822String() }}</pubDate>
@if ($article->author)
            <dc:creator>{{ $article->author->name }}</dc:creator>
@endif
@if ($article->category)
            <category>{{ $article->category->name }}</category>
@endif
            <description>{{ $article->excerpt ?? Str::limit(strip_tags($article->body), 200) }}</description>
        </item>
@endforeach
    </channel>
</rss>
