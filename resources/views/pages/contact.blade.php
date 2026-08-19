@extends('layouts.app')

@section('title', 'Contact')
@section('description', 'How to reach the ' . config('app.name') . ' newsroom — story tips, corrections, advertising and press.')

@section('content')
    <x-page-header title="Contact"
                   subtitle="Tips, corrections, advertising or press — pick the one that fits and we will read it." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        {{-- One card per topic. Each opens the same popup with that topic
             already selected, so the reader never has to choose twice. --}}
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach (\App\Models\ContactMessage::TOPICS as $key => $topic)
                <div class="flex flex-col rounded-xl border border-rule bg-paper-soft p-5">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">
                        {{ $topic['label'] }}
                    </h2>

                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink/60">
                        {{ $topic['blurb'] }}
                    </p>

                    <x-contact-button :topic="$key" class="mt-4 w-full">
                        {{ $topic['label'] }}
                    </x-contact-button>
                </div>
            @endforeach
        </div>

        {{-- The same form, inline. This is where the popup triggers link to
             when JavaScript is unavailable, so the page always works. --}}
        <section id="contact-form" class="mt-12 scroll-mt-24">
            <h2 class="mb-6 border-b-2 border-rule pb-3 text-xl font-black uppercase tracking-tight">
                Send a message
            </h2>

            @if (session('contact_sent'))
                <p role="status"
                   class="rounded-xl border border-brand/25 bg-brand/5 px-5 py-4 text-sm font-semibold text-brand-dark">
                    {{ session('contact_sent') }}
                </p>
            @else
                <x-contact-form />
            @endif
        </section>

        <div class="prose-nyvora mt-12 max-w-none">
            <h2>Response times</h2>
            <p>
                We read everything. We reply to most messages within two business days — reports about
                active security incidents get looked at the same day.
            </p>
        </div>
    </div>
@endsection
