@extends('layouts.app')

@section('title', 'Privacy policy')
@section('description', 'How ' . config('app.name') . ' collects, uses and shares information about readers.')

@section('content')
    <x-page-header title="Privacy policy"
                   subtitle="What we collect, why we collect it, and the choices you have." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        {{--
            IMPORTANT: this is a starting template, not legal advice. Have a
            lawyer review it against the jurisdictions you operate in (GDPR,
            UK GDPR, CCPA/CPRA) and fill in every [bracketed] placeholder before
            you rely on it or submit the site for AdSense review.
        --}}
        <p class="mb-10 rounded-lg border-l-4 border-accent bg-accent/10 px-5 py-4 text-sm leading-relaxed text-ink/70">
            <strong class="font-bold text-ink">Template — review before publishing.</strong>
            This page is a starting point, not legal advice. Fill in the bracketed placeholders and
            have it reviewed against the privacy laws that apply to you before launch.
        </p>

        <div class="prose-nyvora max-w-none">
            <p><strong>Last updated:</strong> [date]</p>

            <p>
                This policy explains how {{ config('app.name') }} (&ldquo;we&rdquo;) handles information
                about people who visit [yourdomain.com]. Questions about it go to
                <a href="mailto:privacy@example.com">privacy@example.com</a>.
            </p>

            <h2>Information we collect</h2>
            <p>
                <strong>Information you give us.</strong> If you subscribe to our newsletter we store the
                email address you enter and the date you subscribed. We use it to send the newsletter and
                nothing else. Every issue includes an unsubscribe link, and unsubscribing deletes the address.
            </p>
            <p>
                <strong>Information collected automatically.</strong> Our servers record standard request
                logs — IP address, browser user agent, the page requested and the time of the request —
                which we use to keep the site running and to spot abuse. We also count page views per
                article in aggregate; those counts are not tied to individual readers.
            </p>

            <h2>Cookies and advertising</h2>
            <p>
                We use cookies to keep your session working and to measure traffic. We also serve display
                advertising, and our advertising partners set their own cookies.
            </p>
            <p>
                Third-party vendors, including Google, use cookies to serve ads based on your prior visits
                to this and other websites. Google's use of advertising cookies enables it and its partners
                to serve ads to you based on your visit to this site and other sites on the internet.
            </p>
            <p>
                You can opt out of personalised advertising by visiting
                <a href="https://www.google.com/settings/ads" rel="nofollow noopener" target="_blank">Google Ads Settings</a>,
                or opt out of a third-party vendor's use of cookies for personalised advertising at
                <a href="https://www.aboutads.info/choices/" rel="nofollow noopener" target="_blank">aboutads.info</a>.
                You can also block or delete cookies in your browser settings; the site will still work.
            </p>

            <h2>How we share information</h2>
            <p>
                We do not sell reader data. We share information only with the service providers that
                operate the site on our behalf — [hosting provider], [email service provider] and
                [analytics provider] — and only as far as they need it to do that job. We may also
                disclose information where the law requires it.
            </p>

            <h2>How long we keep it</h2>
            <p>
                Server logs are retained for [30] days. Newsletter subscriptions are kept until you
                unsubscribe. Aggregate view counts are kept indefinitely because they contain no
                personal data.
            </p>

            <h2>Your rights</h2>
            <p>
                Depending on where you live, you may have the right to access, correct, export or delete
                the personal data we hold about you, and to object to certain processing. Email
                <a href="mailto:privacy@example.com">privacy@example.com</a> and we will respond within
                the period the applicable law requires. If you are in the EU or UK, our legal basis for
                sending the newsletter is your consent, and for keeping server logs it is our legitimate
                interest in operating a secure site.
            </p>

            <h2>Children</h2>
            <p>
                This site is not directed at children under [13/16], and we do not knowingly collect
                personal information from them.
            </p>

            <h2>Changes to this policy</h2>
            <p>
                If we make material changes we will update the date at the top of this page and, where
                the change is significant, note it in the newsletter.
            </p>
        </div>
    </div>
@endsection
