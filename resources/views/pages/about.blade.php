@extends('layouts.app')

@section('title', 'About us')
@section('description', 'Who we are, what we cover and how ' . config('app.name') . ' is funded.')

@section('content')
    <x-page-header title="About us"
                   subtitle="Independent reporting on the technology industry — the funding, the hardware, the security holes and the science underneath it all." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">
        <div class="prose-nyvora max-w-none">
            {{-- Replace this copy with your own before launch. --}}
            <p>
                {{ config('app.name') }} is a technology publication covering artificial intelligence,
                startups, security, consumer hardware and space. We write for people who build and
                buy technology and who want the detail behind the announcement.
            </p>

            <h2>What we cover</h2>
            <p>
                Our newsroom is organised around five beats. Each has a dedicated reporter who follows
                the companies, the funding and the research in that area rather than chasing whatever
                is trending that morning.
            </p>

            <h2>How we work</h2>
            <p>
                We publish corrections in place and mark them clearly. We do not accept payment for
                editorial coverage, and sponsored content — if we ever run it — is labelled as such at
                the top of the page, not in a footnote.
            </p>

            <h2>How we are funded</h2>
            <p>
                {{ config('app.name') }} is supported by display advertising and by readers who
                subscribe to our newsletter. Advertising is sold and served by third parties; the
                editorial team has no visibility into which advertisers appear beside a given story.
                Our <a href="{{ route('privacy-policy') }}">privacy policy</a> explains what those
                advertising partners collect.
            </p>

            <h2>Get in touch</h2>
            <p>
                Story tips, corrections and press enquiries all go through our
                <a href="{{ route('contact') }}">contact page</a>.
            </p>
        </div>
    </div>
@endsection
