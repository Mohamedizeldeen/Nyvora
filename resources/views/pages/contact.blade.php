@extends('layouts.app')

@section('title', 'Contact')
@section('description', 'How to reach the ' . config('app.name') . ' newsroom — story tips, corrections, advertising and press.')

@section('content')
    <x-page-header title="Contact"
                   subtitle="Tips, corrections, advertising or press — here is where each one goes." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        {{-- Replace every address below with your real contact details. --}}
        <dl class="grid gap-5 sm:grid-cols-2">
            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Story tips</dt>
                <dd class="mt-2">
                    <a href="mailto:tips@example.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        tips@example.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Tell us what you know. We treat every tip as confidential unless you say otherwise.
                    </p>
                </dd>
            </div>

            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Corrections</dt>
                <dd class="mt-2">
                    <a href="mailto:corrections@example.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        corrections@example.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Include the article URL and what we got wrong. We fix errors in place and note the change.
                    </p>
                </dd>
            </div>

            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Advertising</dt>
                <dd class="mt-2">
                    <a href="mailto:ads@example.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        ads@example.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Rate card, formats and availability for display and newsletter placements.
                    </p>
                </dd>
            </div>

            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Press &amp; general</dt>
                <dd class="mt-2">
                    <a href="mailto:hello@example.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        hello@example.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Everything else, including partnership and syndication enquiries.
                    </p>
                </dd>
            </div>
        </dl>

        <div class="prose-nyvora mt-12 max-w-none">
            <h2>Postal address</h2>
            <p>
                {{ config('app.name') }} Media<br>
                [Street address]<br>
                [City, postcode]<br>
                [Country]
            </p>

            <h2>Response times</h2>
            <p>
                We read everything. We reply to most messages within two business days — tips about
                active security incidents get looked at the same day.
            </p>
        </div>

        <p class="mt-10 rounded-lg border border-dashed border-rule bg-paper-soft px-5 py-4 text-sm text-ink/55">
            <strong class="font-bold text-ink/75">Demo site.</strong>
            The addresses above are placeholders. Swap in real, monitored inboxes before applying
            for AdSense — reviewers check that contact details work.
        </p>
    </div>
@endsection
