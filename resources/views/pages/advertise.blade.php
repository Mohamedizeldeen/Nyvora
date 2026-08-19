@extends('layouts.app')

@section('title', 'Advertise with us')
@section('description', 'Reach the people who build and buy technology. Display advertising on ' . config('app.name') . '.')

@section('content')
    <x-page-header title="Advertise with us"
                   subtitle="Reach the people who build and buy technology — engineers, founders, and the teams who sign the purchase orders." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        <div class="prose-nyvora max-w-none">
            <p>
                {{ config('app.name') }} is read by people who make technology decisions: engineers and
                security teams, founders and operators, and the buyers who choose what their companies
                run. If that is who you are trying to reach, we would like to hear from you.
            </p>
        </div>

        {{-- Formats match the ad units actually implemented on the site. --}}
        <h2 class="mt-12 mb-6 border-b-2 border-rule pb-3 text-xl font-black uppercase tracking-tight">
            Display formats
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[34rem] border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b-2 border-rule text-[11px] font-bold uppercase tracking-wider text-ink/45">
                        <th scope="col" class="py-3 pr-4">Format</th>
                        <th scope="col" class="py-3 pr-4">Size</th>
                        <th scope="col" class="py-3">Placement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rule">
                    <tr>
                        <td class="py-3 pr-4 font-bold">Medium rectangle</td>
                        <td class="py-3 pr-4 font-mono text-xs text-ink/70">300 &times; 250</td>
                        <td class="py-3 text-ink/70">Sidebar, beside the article and on every archive page</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-bold">Leaderboard</td>
                        <td class="py-3 pr-4 font-mono text-xs text-ink/70">728 &times; 90</td>
                        <td class="py-3 text-ink/70">Above the homepage feed, first thing below the hero</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-bold">Large mobile banner</td>
                        <td class="py-3 pr-4 font-mono text-xs text-ink/70">320 &times; 100</td>
                        <td class="py-3 text-ink/70">In-feed, between stories</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="prose-nyvora mt-10 max-w-none">
            @if (newsletter_enabled())
            <h2>Newsletter sponsorship</h2>
            <p>
                The Daily Brief goes out each morning to a double opt-in list — every subscriber
                confirmed their address, so it is a list of people who actually asked to be there. A
                sponsorship is a short text placement at the top of the issue, clearly marked. One
                sponsor per issue.
            </p>
            @endif

            <h2>What we do not do</h2>
            <p>
                Some things are not for sale, and it is fairer to say so up front than to discuss it
                later:
            </p>
            <p>
                &bull; <strong>Coverage.</strong> You cannot buy a story, influence one, or get a story
                about your company taken down.<br>
                &bull; <strong>Advance notice.</strong> We do not tell advertisers what we are about to
                publish about them.<br>
                &bull; <strong>Unlabelled content.</strong> Anything commercial is labelled at the top
                of the page, before the headline.<br>
                &bull; <strong>Your data.</strong> We do not sell or share reader data with advertisers.
            </p>
            <p>
                This is set out in full in our <a href="{{ route('editorial-policy') }}">editorial
                policy</a>. It is not a formality — it is the reason our readers trust what they read
                here, which is the only thing that makes this audience worth reaching.
            </p>

            <h2>Technical notes</h2>
            <p>
                Display inventory is served through Google AdSense. We do not run pop-ups, interstitials,
                auto-playing video with sound, or anything that moves the page while you are reading it.
                Ad slots reserve their space before they load, so an ad never pushes the article out
                from under a reader.
            </p>

            <h2>Get in touch</h2>
            <p>
                Tell us what you are trying to achieve and roughly when, using the
                <x-contact-button topic="advertising" variant="link">advertising form</x-contact-button>. We will come back with availability, current traffic figures
                and pricing. We are a small team, so you will be talking to a person rather than a
                portal.
            </p>
        </div>

        @if (newsletter_enabled())
        <div class="mt-10 rounded-xl border border-rule bg-paper-soft p-6">
            <p class="text-sm leading-relaxed text-ink/65">
                <strong class="font-bold text-ink">Prefer no ads at all?</strong>
                Subscribe to <a href="{{ route('home') }}#newsletter" class="font-semibold text-brand hover:text-brand-dark">The Daily Brief</a>
                — the newsletter carries at most one clearly-marked sponsor and no tracking.
            </p>
        </div>
        @endif
    </div>
@endsection
