@extends('layouts.app')

@section('title', 'Contact')
@section('description', 'How to reach the ' . config('app.name') . ' newsroom — story tips, corrections, advertising and press.')

@section('content')
    <x-page-header title="Contact"
                   subtitle="Tips, corrections, advertising or press — here is where each one goes." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        {{-- These mailboxes must exist and be monitored on the sending domain.
             The editorial policy, terms and privacy policy all point readers at
             addresses on ny-vora.com, and AdSense review checks that contact
             details actually work. --}}
        <dl class="grid gap-5 sm:grid-cols-2">
            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Story tips</dt>
                <dd class="mt-2">
                    <a href="mailto:tips@ny-vora.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        tips@ny-vora.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Tell us what you know. We treat every tip as confidential unless you say otherwise.
                    </p>
                </dd>
            </div>

            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Corrections</dt>
                <dd class="mt-2">
                    <a href="mailto:corrections@ny-vora.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        corrections@ny-vora.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Include the article URL and what we got wrong. We fix errors in place and note the change.
                    </p>
                </dd>
            </div>

            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Advertising</dt>
                <dd class="mt-2">
                    <a href="mailto:ads@ny-vora.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        ads@ny-vora.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Rate card, formats and availability for display and newsletter placements.
                    </p>
                </dd>
            </div>

            <div class="rounded-xl border border-rule bg-paper-soft p-5">
                <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">Press &amp; general</dt>
                <dd class="mt-2">
                    <a href="mailto:hello@ny-vora.com" class="text-base font-extrabold tracking-tight text-brand hover:text-brand-dark">
                        hello@ny-vora.com
                    </a>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink/60">
                        Everything else, including partnership and syndication enquiries.
                    </p>
                </dd>
            </div>
        </dl>

        <div class="prose-nyvora mt-12 max-w-none">
            {{-- If you later need a postal address here (some jurisdictions and
                 ad networks expect one), add it back as a "Postal address" section. --}}
            <h2>Response times</h2>
            <p>
                We read everything. We reply to most messages within two business days — tips about
                active security incidents get looked at the same day.
            </p>
        </div>
    </div>
@endsection
