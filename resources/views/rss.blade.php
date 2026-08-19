@extends('layouts.app')

@section('title', 'RSS feed')
@section('description', 'Follow ' . config('app.name') . ' by RSS — every new story delivered to your feed reader, with no email address and no tracking.')

@section('content')
    <x-page-header title="RSS feed"
                   subtitle="Every new story, delivered to your reader the moment it publishes. No account, no email address, no tracking." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        {{-- The feed address, ready to copy. --}}
        <div class="rounded-xl border border-rule bg-paper-soft p-6">
            <label for="feed-url" class="admin-label">Feed address</label>

            <div class="flex flex-col gap-2 sm:flex-row">
                <input id="feed-url"
                       type="text"
                       readonly
                       value="{{ route('feed') }}"
                       data-copy-source
                       class="w-full rounded-lg border border-rule bg-white px-3.5 py-2.5 font-mono text-sm text-ink
                              focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">

                <button type="button"
                        data-copy-button
                        data-copy-for="feed-url"
                        class="btn-primary shrink-0">
                    Copy
                </button>
            </div>

            <p class="mt-3 text-xs text-ink/50" data-copy-feedback role="status" aria-live="polite">
                Paste this into any feed reader to subscribe.
            </p>
        </div>

        <div class="prose-nyvora mt-10 max-w-none">
            <h2>What is RSS?</h2>
            <p>
                RSS is a plain file that lists everything we publish. A feed reader checks it for you
                and collects new stories in one place, alongside every other site you follow.
            </p>
            <p>
                It is the opposite of an algorithmic timeline: you see everything from the sites you
                chose, in order, and nothing else. We learn nothing about you when you read this way —
                no account, no email address, no tracking pixel.
            </p>

            <h2>Adding it to a reader</h2>
            <p>
                Most readers only need the address above. Some let you paste our homepage
                and find the feed on their own, because it is advertised in this page's
                <code>&lt;head&gt;</code>.
            </p>
        </div>

        {{-- One-click subscribe in the common web readers. --}}
        @php
            $encodedFeed = urlencode(route('feed'));
            $readers = [
                ['name' => 'Feedly', 'url' => 'https://feedly.com/i/subscription/feed/'.$encodedFeed],
                ['name' => 'Inoreader', 'url' => 'https://www.inoreader.com/?add_feed='.$encodedFeed],
                ['name' => 'The Old Reader', 'url' => 'https://theoldreader.com/feeds/subscribe?url='.$encodedFeed],
                ['name' => 'NewsBlur', 'url' => 'https://www.newsblur.com/?url='.$encodedFeed],
            ];
        @endphp

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($readers as $reader)
                <a href="{{ $reader['url'] }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn-ghost">
                    Add to {{ $reader['name'] }}
                </a>
            @endforeach
        </div>

        {{-- What the feed currently contains, so the page is not an empty promise. --}}
        <h2 class="mt-14 mb-6 border-b-2 border-rule pb-3 text-xl font-black uppercase tracking-tight">
            Currently in the feed
        </h2>

        @if ($articles->isEmpty())
            <p class="rounded-lg border border-dashed border-rule bg-paper-soft px-6 py-12 text-center text-sm text-ink/50">
                Nothing published yet.
            </p>
        @else
            <ul class="divide-y divide-rule border-b border-rule">
                @foreach ($articles as $article)
                    <li class="py-4">
                        <article class="group relative">
                            @if ($article->category)
                                <x-category-label :category="$article->category" class="relative z-10" />
                            @endif

                            <h3 class="mt-1 text-base font-extrabold leading-snug tracking-tight text-ink sm:text-lg">
                                <a href="{{ route('article.show', $article) }}"
                                   class="transition-colors after:absolute after:inset-0 after:content-[''] group-hover:text-brand">
                                    {{ $article->title }}
                                </a>
                            </h3>

                            <x-byline :article="$article" class="mt-1.5 text-xs" />
                        </article>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4 text-xs text-ink/45">
                The feed carries the 30 most recent stories.
                <a href="{{ route('feed') }}" class="font-semibold text-brand hover:text-brand-dark">View the raw XML</a>
                if you want to see what a reader sees.
            </p>
        @endif

        {{-- The alternative, for people who would rather get email. --}}
        @if (newsletter_enabled())
        <div class="mt-12 rounded-xl border border-rule bg-paper-soft p-6">
            <h2 class="text-lg font-black uppercase tracking-tight text-ink">Prefer email?</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink/60">
                The Daily Brief is one email each morning with the stories that actually matter.
                It is double opt-in, carries at most one clearly-marked sponsor, and you can leave
                from a link in any issue.
            </p>
            <a href="{{ route('home') }}#newsletter" class="btn-primary mt-4">Subscribe by email</a>
        </div>
        @endif
    </div>
@endsection
