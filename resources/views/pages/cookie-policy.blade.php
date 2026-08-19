@extends('layouts.app')

@section('title', 'Cookie policy')
@section('description', 'Exactly which cookies ' . config('app.name') . ' sets, what each one does, and how to refuse them.')

@php
    $servesAds = filled(setting('adsense_client_id'));
    $usesAnalytics = analytics_id() !== null;
    $lastUpdated = '16 August 2026';
@endphp

@section('content')
    <x-page-header title="Cookie policy"
                   subtitle="Exactly which cookies we set, what each one does, and how to refuse them." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        <p class="mb-10 text-sm text-ink/50">Last updated: {{ $lastUpdated }}</p>

        <div class="mb-12 rounded-xl border border-rule bg-paper-soft p-6">
            <h2 class="text-sm font-black uppercase tracking-wider text-ink">The short version</h2>
            <p class="mt-3 text-sm leading-relaxed text-ink/70">
                @if ($usesAnalytics)
                    Two cookies are strictly necessary and always set. Analytics cookies are only
                    set if you accept them, and the site works exactly the same if you decline.
                @else
                    We set two cookies. Both are strictly necessary for the site to function, neither
                    tracks you, and we run no analytics.
                @endif
                @unless ($servesAds)
                    We serve no advertising, so there are no advertising cookies either.
                @endunless
                That is the whole story — the detail below just explains it properly.
            </p>
        </div>

        <div class="prose-nyvora max-w-none">
            <h2>What a cookie is</h2>
            <p>
                A cookie is a small text file a website asks your browser to store and send back on
                later visits. Cookies are how a site remembers anything between page loads. They are
                often associated with tracking, but the technology itself is neutral — what matters is
                what each cookie is used for.
            </p>

            <h2>The cookies we set</h2>
            <p>
                The first two are &ldquo;strictly necessary&rdquo;: the site cannot work correctly
                without them, and under UK and EU rules they do not require consent. Neither is
                readable by another website.
            </p>
            @if ($usesAnalytics)
                <p>
                    The analytics cookies are different. They are optional, and we ask before setting
                    them &mdash; see &ldquo;Analytics&rdquo; below.
                </p>
            @endif
        </div>

        <x-cookie-table class="my-8" />

        <div class="prose-nyvora max-w-none">
            <p>
                Both are first-party session cookies. They expire on their own, and they disappear when
                you close your browser or sign out.
            </p>

            <h2>What we do not set</h2>
            <p>
                We think it is worth being specific about the absences, because most sites in this
                category have all of them. {{ config('app.name') }} runs:
            </p>
            <p>
                @unless ($usesAnalytics)
                    <strong>No analytics.</strong> No Google Analytics, no Plausible, no Matomo, no
                    Fathom. We count article views as a single number per story, stored on our own
                    server and not linked to any reader.<br>
                @endunless
                <strong>No social media pixels.</strong> Our share buttons are plain links. They load
                nothing from Facebook, X or LinkedIn, so those companies learn nothing about you unless
                you actually click through.<br>
                <strong>No session recording, heatmaps, or fingerprinting.</strong><br>
                <strong>No third-party fonts.</strong> Our typeface is served from our own domain, so
                no font provider sees your IP address.
            </p>

            <h2>Advertising cookies</h2>
            @if ($servesAds)
                <p>
                    We serve display advertising through Google AdSense, and Google and its partners set
                    their own cookies to do it. These are not covered by the table above because we do
                    not control them.
                </p>
                <p>
                    You can opt out of personalised advertising in
                    <a href="https://www.google.com/settings/ads" rel="nofollow noopener" target="_blank">Google Ads Settings</a>,
                    or opt out of a range of vendors at
                    <a href="https://www.aboutads.info/choices/" rel="nofollow noopener" target="_blank">aboutads.info</a>
                    and <a href="https://www.youronlinechoices.eu/" rel="nofollow noopener" target="_blank">youronlinechoices.eu</a>.
                </p>
            @else
                <p>
                    We do not currently serve advertising, so no advertising cookies are set on this
                    site at all.
                </p>
                <p>
                    We intend to fund the publication through display advertising in future. If that
                    happens, this page will be updated before the first ad appears, and it will name the
                    provider and explain how to opt out.
                </p>
            @endif

            @if ($usesAnalytics)
                <h2>Analytics</h2>
                <p>
                    We use Google Analytics to see which stories are read and how people find them.
                    It tells us about traffic, not about you personally: we do not use it to build
                    profiles, and we have not connected it to advertising or to any other Google
                    product.
                </p>
                <p>
                    Analytics cookies are <strong>off until you accept them</strong>. We use Google
                    Consent Mode, which means the tag is present on the page but stores nothing at
                    all &mdash; no cookies, no identifiers &mdash; unless you choose Accept in the
                    banner. Choosing Reject keeps it that way, and the site behaves identically.
                </p>
                <p>
                    Changed your mind? Clear this site's data in your browser and the banner will ask
                    again. You can also opt out of Google Analytics everywhere with Google's
                    <a href="https://tools.google.com/dlpage/gaoptout" rel="nofollow noopener" target="_blank">browser add-on</a>.
                </p>
            @endif

            <h2>How to refuse or delete cookies</h2>
            <p>
                Every browser lets you block or delete cookies, per site or entirely — look under
                Settings → Privacy. You can also browse in a private window, which discards them when
                you close it.
            </p>
            <p>
                @if ($usesAnalytics)
                    We show a banner for the optional cookies only. The strictly necessary two are
                    not part of that choice, because the site cannot work without them and the rules
                    do not require consent for them.
                @else
                    Because ours are strictly necessary rather than optional, we do not show a cookie
                    banner: there is nothing to consent to.
                @endif
                If you block them, the site still works and you can still read everything. The only
                thing that breaks is any form on the site, which needs the security cookie to verify
                the submission.
            </p>

            <h2>Changes</h2>
            <p>
                If we add a cookie, this page and the table above change with it, and the date at the
                top is updated. The table is generated from the application's own configuration, so it
                cannot silently fall out of step with what the site actually sets.
            </p>

            <h2>Questions</h2>
            <p>
                Send us a <x-contact-button topic="privacy" variant="link">privacy question</x-contact-button>. Our
                <a href="{{ route('privacy-policy') }}">privacy policy</a> covers everything else we
                collect.
            </p>
        </div>
    </div>
@endsection
