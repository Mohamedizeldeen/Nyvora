@extends('layouts.app')

@section('title', 'Privacy policy')
@section('description', 'What ' . config('app.name') . ' collects, why we collect it, how long we keep it and the choices you have.')

@php
    // The advertising section describes what the site actually does today, so it
    // follows the AdSense setting rather than claiming cookies we do not set.
    $servesAds = filled(setting('adsense_client_id'));
    $usesAnalytics = analytics_id() !== null;

    // Bump this only when the policy itself changes — not on every deploy.
    $lastUpdated = '16 August 2026';
@endphp

@section('content')
    <x-page-header title="Privacy policy"
                   subtitle="What we collect, why we collect it, how long we keep it, and the choices you have." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        <p class="mb-10 text-sm text-ink/50">Last updated: {{ $lastUpdated }}</p>

        {{-- Plain-language summary. Not a substitute for the detail below, but
             most readers will only read this part. --}}
        <div class="mb-12 rounded-xl border border-rule bg-paper-soft p-6">
            <h2 class="text-sm font-black uppercase tracking-wider text-ink">The short version</h2>
            <ul class="mt-4 space-y-2.5 text-sm leading-relaxed text-ink/70">
                <li>&bull; You can read {{ config('app.name') }} without giving us anything or creating an account.</li>
                @if (newsletter_enabled())
                    <li>&bull; The only personal information we ask for is an email address, and only if you choose to subscribe to our newsletter.</li>
                @else
                    <li>&bull; The only personal information we hold is what you type into a contact form, and only if you choose to send one.</li>
                @endif
                @if ($usesAnalytics)
                    <li>&bull; Two cookies are strictly necessary. Analytics cookies are set only if you accept them, and declining changes nothing about the site.</li>
                @else
                    <li>&bull; We set two cookies, both strictly necessary for the site to work. We run no analytics and no tracking scripts.</li>
                @endif
                <li>&bull; We do not sell your data, and we do not share it with anyone except the providers that run the site for us.</li>
                {{-- Whole variants, never a directive spliced mid-sentence: Blade
                     ignores an @directive preceded by a word character. --}}
                @if (newsletter_enabled())
                    <li>&bull; You can unsubscribe, or ask us to delete everything we hold about you, at any time.</li>
                @else
                    <li>&bull; You can ask us to delete anything we hold about you at any time.</li>
                @endif
            </ul>
        </div>

        <div class="prose-nyvora max-w-none">

            <h2>Who we are</h2>
            <p>
                {{ config('app.name') }} is a technology news publication, published at
                <a href="{{ route('home') }}">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a>.
                For the purposes of data protection law we are the data controller for the information
                described here.
            </p>
            <p>
                {{-- GDPR Art. 13 asks for the controller's identity and contact details; the
                     email below satisfies that. Add a registered postal address here if the
                     company is established somewhere that expects one. --}}
                <strong>Nyvora Media</strong><br>
                Contact us through the
                <x-contact-button topic="privacy" variant="link">privacy form</x-contact-button> on this site.
            </p>

            <h2>What we collect</h2>

            @if (newsletter_enabled())
            <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Newsletter subscriptions</h3>
            <p>
                If you subscribe to The Daily Brief we store your email address, the date you signed
                up, the date you confirmed, and a random token used to build your confirmation and
                unsubscribe links. Nothing else — no name, no location, no profile.
            </p>
            <p>
                Our list is double opt-in. Entering an address does not subscribe you: we email you a
                link, and only once you click it are you added. If you never click, the pending record
                is deleted within 30 days. This is deliberate, so that someone typing your address by
                mistake cannot sign you up.
            </p>
            @endif

            <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Messages you send us</h3>
            <p>
                When you send a message through one of the forms on this site — a story tip, a
                correction, a privacy request, anything — we store the topic you picked, your name,
                your email address and the message itself. Nothing more: no IP address is saved with
                it, and there is no tracking attached to the form.
            </p>
            <p>
                We use it only to read your message and reply. Messages are stored on our own server
                and read in our newsroom dashboard; they are not sent to any email provider, and they
                are never used for marketing.
            </p>

            @if (comments_enabled())
                <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Comments</h3>
                <p>
                    If you comment on an article we store the name you type and the comment itself.
                    We do not ask for an email address, and no IP address is recorded.
                </p>
                <p>
                    Both are <strong>published on the article for anyone to read</strong> once an
                    editor approves them, so please use only a name you are happy to be public. Ask us
                    to remove a comment at any time and we will.
                </p>
            @endif

            @if ($usesAnalytics)
                <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Analytics</h3>
                <p>
                    If you accept analytics cookies, Google Analytics records how you move through
                    the site: which pages you open, roughly where you are (country or city level,
                    from your IP address, which Google does not store), the kind of device you are
                    using, and how you arrived. It is used to understand which stories are read.
                </p>
                <p>
                    Until you accept, <strong>nothing is recorded and no analytics cookie is set</strong>.
                    We use Google Consent Mode, so the tag stores nothing at all before you choose,
                    and choosing Reject keeps it that way permanently. We do not use analytics to
                    identify you or to build an advertising profile.
                </p>
            @endif

            <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Server logs</h3>
            <p>
                Like every web server, ours records each request: the IP address it came from, the
                browser's user-agent string, the page requested (which includes any search terms you
                type into our search box) and the time. We use these logs to keep the site running, to
                diagnose errors and to identify abuse. We do not use them to build a profile of you.
            </p>

            <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Cookies</h3>
            @if ($usesAnalytics)
                <p>
                    Two cookies are strictly necessary and always set. Two more belong to Google
                    Analytics and are set <strong>only if you accept them</strong> in the banner.
                    None of them follows you to other websites.
                </p>
            @else
                <p>
                    We set two cookies, both strictly necessary. Neither is used for advertising or
                    analytics, and neither follows you to other websites.
                </p>
            @endif
        </div>

        <x-cookie-table class="my-8" />

        <div class="prose-nyvora max-w-none">
            <p>
                You can block or delete cookies in your browser settings. The site will still work,
                though any form on the site may not be able to confirm your submission. Our
                <a href="{{ route('cookie-policy') }}">cookie policy</a> covers this in more detail.
            </p>

            <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Article view counts</h3>
            <p>
                We count how many times each article is read. This is a single number per article,
                incremented on each page view. It is not linked to an IP address, a cookie or a
                person, and it cannot be traced back to any individual reader.
            </p>

            <h3 class="mt-8 mb-3 text-lg font-extrabold tracking-tight text-ink">Staff accounts</h3>
            <p>
                Our editors have accounts holding a name, an email address and a hashed password.
                These are for newsroom staff only; readers never need an account.
            </p>

            <h2>Why we process it, and our legal basis</h2>
            <p>
                If you are in the UK, EU or another region with similar law, our legal bases are:
            </p>
            @if (newsletter_enabled())
                <p>
                    <strong>Consent</strong> for the newsletter. You gave it by confirming your address,
                    and you can withdraw it at any time using the unsubscribe link in any issue — that
                    withdrawal is as easy as the subscription was.
                </p>
            @endif
            @if ($usesAnalytics)
                <p>
                    <strong>Consent</strong> for analytics cookies. You give it with the Accept
                    button and nothing is stored before that. Withdraw it by clearing this site's
                    data in your browser, and the banner will ask again.
                </p>
            @endif
            <p>
                <strong>Legitimate interests</strong> for handling the messages you send us: you
                wrote to us, and replying is the whole point. You can ask us to delete a message at
                any time.
            </p>
            <p>
                <strong>Legitimate interests</strong> for server logs, security and aggregate view
                counts: operating a secure, working website, and understanding which stories are read.
                We have weighed this against your rights, and consider the impact minimal because the
                data is short-lived and never used to profile you.
            </p>

            <h2>Advertising</h2>
            @if ($servesAds)
                <p>
                    We serve display advertising through Google AdSense. Third-party vendors, including
                    Google, use cookies to serve ads based on your prior visits to this and other
                    websites. Google's use of advertising cookies enables it and its partners to serve
                    ads to you based on your visit to this site and other sites on the internet.
                </p>
                <p>
                    You can opt out of personalised advertising in
                    <a href="https://www.google.com/settings/ads" rel="nofollow noopener" target="_blank">Google Ads Settings</a>,
                    or opt out of a third-party vendor's use of cookies for personalised advertising at
                    <a href="https://www.aboutads.info/choices/" rel="nofollow noopener" target="_blank">aboutads.info</a>.
                    Our editorial team has no visibility into which advertisers appear beside a given story.
                </p>
            @else
                <p>
                    We do not currently serve advertising, and no advertising cookies are set on this site.
                </p>
                <p>
                    We intend to fund the publication through display advertising in future. If and when
                    that happens, this page will be updated before any ads appear, and we will explain
                    exactly what the advertising partner collects and how to opt out.
                </p>
            @endif

            <h2>Who we share it with</h2>
            <p>
                We do not sell your personal information, and we have not sold or shared it for
                cross-context behavioural advertising in the past twelve months. We share it only with
                the providers that operate the site on our behalf, and only as far as each needs it:
            </p>
            @if (newsletter_enabled())
                <p>
                    <strong>Mailgun</strong> (Sinch Email, Inc., United States) delivers our newsletter and
                    confirmation emails. Your email address is processed by Mailgun for that purpose.
                    If you are in the UK or EU, this means your address is transferred to the United States;
                    that transfer is covered by the standard contractual clauses in Mailgun's data
                    processing agreement.
                </p>
            @endif
            @if ($usesAnalytics)
                <p>
                    <strong>Google</strong> (Google Ireland Limited) provides Google Analytics. If you
                    accept analytics cookies, the data described above is processed by Google on our
                    behalf, and may be transferred outside the UK/EU under the safeguards in Google's
                    data processing terms. Decline, and Google receives nothing.
                </p>
            @endif
            <p>
                <strong>Our hosting provider</strong> stores the database and server logs described above.
            </p>
            <p>
                We may also disclose information where the law genuinely requires it — a court order,
                for example. If that ever happens we will tell you, unless we are legally barred from
                doing so.
            </p>

            <h2>How long we keep it</h2>
            @if (newsletter_enabled())
                <p>
                    <strong>Newsletter addresses</strong> are kept until you unsubscribe. Unconfirmed
                    signups are deleted after 30 days.
                </p>
                <p>
                    When you unsubscribe we keep a minimal record — your address and the fact that you
                    opted out — rather than deleting the row outright. This is how we make sure you are not
                    silently re-added later, and it is the record that proves we honoured your request. If
                    you would rather we erase it completely, ask us and we will.
                </p>
            @endif
            @if (comments_enabled())
                <p>
                    <strong>Comments</strong> stay on the article until you or we remove them.
                    Comments we do not approve are deleted.
                </p>
            @endif
            <p>
                <strong>Messages</strong> are kept for as long as the matter is open, and cleared out
                once it is closed. Ask us to delete yours sooner and we will.
            </p>
            <p>
                <strong>Server logs</strong> are kept for 30 days and then deleted.
                <strong>Aggregate view counts</strong> are kept indefinitely, because they contain no
                personal data.
            </p>

            <h2>Your rights</h2>
            <p>
                Depending on where you live, you have some or all of the following rights over the
                personal information we hold about you: to know what we hold and get a copy of it; to
                have it corrected; to have it deleted; to receive it in a portable format; to object to
                or restrict how we use it; and to withdraw consent at any time.
            </p>
            <p>
                Exercising any of them costs nothing and we will not treat you differently for asking.
                Send a <x-contact-button topic="privacy" variant="link">privacy request</x-contact-button> and we will
                respond within 30 days. Because we hold so little, we will usually reply the same week.
                Tell us the address you used and roughly when you wrote, so we can find the message.
            </p>
            <p>
                If you are in the UK or EU and you think we have got something wrong, you also have the
                right to complain to your national data protection authority. We would appreciate the
                chance to put it right first.
            </p>

            <h2>Security</h2>
            <p>
                The site is served over HTTPS. Passwords for staff accounts are hashed, never stored in
                readable form. Confirmation and unsubscribe links use long random tokens, so nobody can
                subscribe or unsubscribe someone else by guessing a URL. Access to the database and to
                the newsroom dashboard is limited to the people who need it.
            </p>
            <p>
                No system is perfectly secure. If we ever discover a breach affecting your information,
                we will notify you and the relevant regulator as the law requires.
            </p>

            <h2>Children</h2>
            <p>
                This site is written for a general audience and is not directed at children. We do not
                knowingly collect personal information from anyone under 16. If you believe a child has
                given us their email address, tell us and we will delete it.
            </p>

            <h2>Changes to this policy</h2>
            <p>
                If we change this policy we will update the date at the top of the page. If the change
                is significant — a new provider, a new category of data, advertising going live — the
                change will be described here before it takes effect.
            </p>

            <h2>Contact</h2>
            <p>
                Questions about this policy, or about anything we hold on you, go through the
                <x-contact-button topic="privacy" variant="link">privacy form</x-contact-button>. Everything else is on our
                <a href="{{ route('contact') }}">contact page</a>.
            </p>
        </div>
    </div>
@endsection
