@extends('layouts.app')

@section('title', 'Editorial policy')
@section('description', 'How ' . config('app.name') . ' decides what to publish, how we source and verify stories, and how we handle corrections.')

@php($lastUpdated = '16 August 2026')

@section('content')
    <x-page-header title="Editorial policy"
                   subtitle="How we decide what to publish, how we verify it, and what we do when we get it wrong." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">

        <p class="mb-10 text-sm text-ink/50">Last updated: {{ $lastUpdated }}</p>

        <div class="prose-nyvora max-w-none">
            <p>
                This page exists so you can hold us to something specific. It describes how
                {{ config('app.name') }} actually works — not aspirations, but the rules our newsroom
                follows.
            </p>

            <h2>What we cover</h2>
            <p>
                We cover the technology industry across five beats: artificial intelligence, startups
                and venture funding, security, consumer hardware, and space and science. Each beat has
                a reporter who follows it continuously rather than chasing whatever is trending.
            </p>
            <p>
                We are more interested in what actually shipped than in what was announced. A funding
                round matters because of what it lets a company build; a product launch matters because
                of who can now do something they could not before.
            </p>

            <h2>Sourcing and verification</h2>
            <p>
                We aim to confirm factual claims with at least two independent sources, or with primary
                documentation. Where a story rests on a single source, we say so in the article.
            </p>
            <p>
                We prefer named sources. We grant anonymity when someone risks their job or safety by
                speaking, when the information is in the public interest, and when we can corroborate
                it — not as a convenience for people who simply prefer not to be quoted. When we do, we
                explain in the article why the source is unnamed.
            </p>
            <p>
                We contact any company or person who is the subject of critical reporting before
                publication, and give them a fair opportunity to respond. If they decline or do not
                reply in time, we say that too.
            </p>

            <h2>Reviews and hands-on coverage</h2>
            <p>
                We test hardware ourselves before reviewing it. Where a manufacturer lends us a review
                unit, we disclose it in the article and return or pay for the device afterwards. We do
                not accept payment, gifts or travel in exchange for coverage, and we do not give
                manufacturers copy approval or advance sight of a review.
            </p>

            <h2>Independence from advertising</h2>
            <p>
                Advertising pays for this publication, and it buys nothing else. Advertisers get no
                influence over what we cover or how we cover it, no advance notice of stories about
                them, and no right of veto. Our reporters have no visibility into which advertisers run
                beside their work.
            </p>
            <p>
                If we ever publish sponsored or affiliate content, it is labelled clearly at the top of
                the page, before the headline — never in a footnote. It is not written by the newsroom.
                See <a href="{{ route('advertise') }}">advertise with us</a>.
            </p>

            <h2>Conflicts of interest</h2>
            <p>
                Reporters disclose to their editor any financial holding, personal relationship or prior
                employment that touches a company they cover, and do not write about companies in which
                they hold a stake. Where a conflict is relevant to a published story, we disclose it in
                the article itself.
            </p>

            <h2>Corrections</h2>
            <p>
                We correct errors quickly and visibly. We do not quietly edit a story and hope nobody
                noticed.
            </p>
            <p>
                <strong>Factual errors</strong> are fixed in the article, with a note at the foot
                recording what was wrong and when it was corrected.<br>
                <strong>Significant errors</strong> — anything that changes the meaning of a story —
                also get a note at the top.<br>
                <strong>Typos and broken links</strong> are simply fixed.<br>
                <strong>We do not unpublish</strong> accurate stories because a subject dislikes them.
                We will remove or amend content where there is a legal obligation or a genuine risk to
                someone's safety.
            </p>
            <p>
                To request a correction, email
                <a href="mailto:corrections@ny-vora.com">corrections@ny-vora.com</a> with the article
                URL and what is wrong. We reply to every request.
            </p>

            <h2>Updating stories</h2>
            <p>
                Developing stories are updated as we learn more, and each article shows when it was
                published. Substantive additions are marked as updates rather than folded silently into
                the original text.
            </p>

            <h2>Artificial intelligence</h2>
            <p>
                Our articles are written by people. We do not publish machine-generated articles under
                a human byline, and we do not use AI to invent quotes, sources or facts.
            </p>
            <p>
                We do use software as a tool — for transcription, for searching large document sets, for
                spotting patterns in data — in the same way we use a spreadsheet or a search engine. A
                human reporter verifies anything that comes out of it, and a human is accountable for
                every word we publish.
            </p>

            <h2>Bylines and accountability</h2>
            <p>
                Every story carries the name of the person who wrote it, linking to
                <a href="{{ route('authors.index') }}">their profile</a> and everything else they have
                published. If you want to know who is responsible for a piece of reporting, it takes one
                click.
            </p>

            <h2>Complaints</h2>
            <p>
                If you believe we have breached this policy, email
                <a href="mailto:editor@ny-vora.com">editor@ny-vora.com</a>. Tell us the article and what
                you think went wrong. We will investigate and reply, and if we got it wrong we will say
                so publicly.
            </p>
        </div>
    </div>
@endsection
