@extends('layouts.app')

@section('title', 'Terms of use')
@section('description', 'The terms on which you may read and use ' . config('app.name') . '.')

@php($lastUpdated = '16 August 2026')

@section('content')
    <x-page-header title="Terms of use"
                   subtitle="The rules for using this site — short, and in plain language." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        <p class="mb-10 text-sm text-ink/50">Last updated: {{ $lastUpdated }}</p>

        <div class="mb-12 rounded-xl border border-rule bg-paper-soft p-6">
            <h2 class="text-sm font-black uppercase tracking-wider text-ink">The short version</h2>
            <p class="mt-3 text-sm leading-relaxed text-ink/70">
                Read anything you like. Quote us with credit and a link. Do not republish whole
                articles, scrape the site at scale, or pass our work off as your own. We try hard to be
                accurate but we are not liable for decisions you make based on what you read here.
            </p>
        </div>

        <div class="prose-nyvora max-w-none">
            <h2>1. Who these terms are between</h2>
            <p>
                These terms govern your use of {{ config('app.name') }}, published at
                <a href="{{ route('home') }}">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a> by
                Nyvora Media (&ldquo;we&rdquo;, &ldquo;us&rdquo;). By using the site you accept them.
                If you do not, please stop using it.
            </p>

            <h2>2. Using the site</h2>
            <p>
                Reading {{ config('app.name') }} is free and requires no account. You agree not to:
            </p>
            <p>
                &bull; scrape, crawl or harvest the site beyond what a normal reader or a well-behaved
                search engine would do, or in a way that degrades it for others;<br>
                &bull; attempt to gain access to any part of the site, server or database you are not
                authorised to reach;<br>
                &bull; interfere with the site's operation, including by probing for vulnerabilities
                other than as described under &ldquo;Reporting a security problem&rdquo; below;<br>
                &bull; use the site for anything unlawful.
            </p>
            <p>
                We publish an <a href="{{ route('feed') }}">RSS feed</a> precisely so that reading our
                headlines programmatically does not require scraping. Please use it.
            </p>

            <h2>3. Our content</h2>
            <p>
                Unless stated otherwise, the articles, photographs, graphics and code on this site are
                owned by us or licensed to us, and are protected by copyright.
            </p>
            <p>
                <strong>You may</strong> quote a reasonable extract — a paragraph or two — for the
                purposes of reporting, commentary, criticism or review, provided you credit
                {{ config('app.name') }} and link to the original article.
            </p>
            <p>
                <strong>You may not</strong> republish an article in full, translate it in full, use our
                work to train machine learning models, or present it as your own, without our written
                permission. For syndication or licensing, write to
                <a href="mailto:hello@ny-vora.com">hello@ny-vora.com</a>.
            </p>

            <h2>4. Accuracy, and what this site is not</h2>
            <p>
                We take accuracy seriously and correct our mistakes openly — see our
                <a href="{{ route('editorial-policy') }}">editorial policy</a>. But journalism is
                written to deadlines and against incomplete information, and articles reflect what was
                known when they were published.
            </p>
            <p>
                Nothing here is professional advice. Our coverage of a company, a product, a funding
                round or a security incident is not investment advice, legal advice or a
                recommendation to buy anything. Decisions you make based on what you read here are
                yours.
            </p>

            <h2>5. Links to other sites</h2>
            <p>
                We link out constantly — that is how the web works, and how you check our sourcing. We
                do not control those sites and are not responsible for their content, their accuracy or
                their privacy practices.
            </p>

            <h2>6. The newsletter</h2>
            <p>
                Subscribing is optional and double opt-in: an address is only added once you confirm it
                by email. Every issue carries an unsubscribe link that works immediately. We do not sell
                or rent the list. See the
                <a href="{{ route('privacy-policy') }}">privacy policy</a> for what we store.
            </p>

            <h2>7. Advertising</h2>
            <p>
                The site is supported by advertising. Advertisers have no influence over our editorial
                coverage, and our newsroom has no visibility into which advertisers appear beside a
                given story. Any commercial content that is not independent journalism is labelled at
                the top of the page. Details are on our
                <a href="{{ route('advertise') }}">advertise with us</a> page.
            </p>

            <h2>8. Availability</h2>
            <p>
                We aim to keep the site up and running, but we do not guarantee it will be available
                without interruption, and we may change, suspend or withdraw any part of it — including
                individual articles — at any time.
            </p>

            <h2>9. Liability</h2>
            <p>
                To the extent the law allows, we are not liable for any indirect or consequential loss
                arising from your use of the site, or from reliance on anything published here.
            </p>
            <p>
                Nothing in these terms limits or excludes our liability for death or personal injury
                caused by negligence, for fraud, or for anything else that cannot lawfully be limited.
                If you are a consumer, these terms do not affect your statutory rights.
            </p>

            <h2>10. Reporting a security problem</h2>
            <p>
                If you find a vulnerability, email <a href="mailto:security@ny-vora.com">security@ny-vora.com</a>
                with enough detail to reproduce it. Please give us a reasonable chance to fix it before
                publishing. We will not pursue action against anyone who reports a genuine issue in good
                faith, does not access or alter other people's data, and does not degrade the service.
            </p>

            <h2>11. Changes to these terms</h2>
            <p>
                We may update these terms. The date at the top shows when they last changed, and
                continuing to use the site after a change means you accept the revised version.
            </p>

            {{-- No choice-of-law clause is stated, because naming a jurisdiction
                 requires knowing where Nyvora Media is established. Without one,
                 the ordinary conflict-of-laws rules apply — which for consumers is
                 usually their own country anyway. If you want to nominate a
                 governing law and forum, add a "Governing law" section here. --}}
            <h2>12. Your local law still applies</h2>
            <p>
                Wherever you are reading from, the mandatory consumer protections of your own country
                continue to apply, and nothing in these terms takes them away. If a term here conflicts
                with a protection you have by law, that protection wins and the rest of these terms
                remain in force.
            </p>

            <h2>13. Contact</h2>
            <p>
                Questions about these terms go to <a href="mailto:hello@ny-vora.com">hello@ny-vora.com</a>.
            </p>
        </div>
    </div>
@endsection
