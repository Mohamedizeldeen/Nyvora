@extends('layouts.app')

@section('title', 'Our team')
@section('description', 'The people who report, edit and publish ' . config('app.name') . ', and how the newsroom is organised.')

@section('content')
    <x-page-header title="Our team"
                   subtitle="Who reports, who edits, and how the newsroom is organised." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        <div class="prose-nyvora max-w-none">
            <p>
                {{ config('app.name') }} is a small newsroom. We would rather cover five beats properly
                than twenty badly, so the team is organised around those beats: each has one reporter
                who follows it continuously and owns what we publish on it.
            </p>

            <h2>How the newsroom is organised</h2>
            <p>
                <strong>Reporters</strong> own a beat. They find the stories, do the reporting and write
                them. Every article carries their byline, and their name links to everything else they
                have written.
            </p>
            <p>
                <strong>Editors</strong> commission and edit. Nothing is published without a second
                pair of eyes, and an editor is responsible for checking sourcing before a story goes
                live. Editors also handle corrections.
            </p>
            <p>
                <strong>Nobody sells advertising and writes at the same time.</strong> The commercial
                side has no say in coverage — the reasoning is set out in our
                <a href="{{ route('editorial-policy') }}">editorial policy</a>.
            </p>
        </div>

        {{-- Pulled from the authors table, so this page cannot drift out of date
             when a byline is added or removed in the newsroom dashboard. --}}
        <h2 class="mt-12 mb-6 border-b-2 border-rule pb-3 text-xl font-black uppercase tracking-tight">
            The newsroom
        </h2>

        @if ($authors->isEmpty())
            <p class="rounded-lg border border-dashed border-rule bg-paper-soft px-6 py-12 text-center text-sm text-ink/50">
                No bylines yet.
            </p>
        @else
            <div class="space-y-6">
                @foreach ($authors as $author)
                    <article class="flex gap-4">
                        <x-author-avatar :author="$author" class="size-14 text-base" />

                        <div class="min-w-0">
                            <h3 class="text-base font-extrabold tracking-tight text-ink">
                                @if ($author->articles_count > 0)
                                    <a href="{{ route('authors.show', $author) }}" class="transition-colors hover:text-brand">
                                        {{ $author->name }}
                                    </a>
                                @else
                                    {{ $author->name }}
                                @endif
                            </h3>

                            @if ($author->bio)
                                <p class="mt-1.5 text-sm leading-relaxed text-ink/60">{{ $author->bio }}</p>
                            @endif

                            @if ($author->articles_count > 0)
                                <p class="mt-1.5 text-xs font-semibold uppercase tracking-wider text-ink/40">
                                    <a href="{{ route('authors.show', $author) }}" class="hover:text-brand">
                                        {{ $author->articles_count }} {{ Str::plural('story', $author->articles_count) }} &rarr;
                                    </a>
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="prose-nyvora mt-12 max-w-none">
            <h2>Work with us</h2>
            <p>
                We take pitches from freelance reporters who know a beat well. Send a short pitch — what
                the story is, why now, and who you would talk to — to
                <a href="mailto:pitches@ny-vora.com">pitches@ny-vora.com</a>. Please do not send
                finished articles on spec.
            </p>

            <h2>Get in touch</h2>
            <p>
                Story tips, corrections and everything else are on our
                <a href="{{ route('contact') }}">contact page</a>.
            </p>
        </div>
    </div>
@endsection
