@extends('layouts.app')

@section('title', 'Subscription confirmed')
@section('description', 'Your subscription to The Daily Brief is confirmed.')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
        <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand/10 text-brand">
            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </span>

        <h1 class="mt-6 text-3xl font-black tracking-tighter sm:text-4xl">
            {{ $alreadyDone ? 'You were already subscribed' : 'You are on the list' }}
        </h1>

        <p class="mx-auto mt-4 max-w-lg text-base leading-relaxed text-ink/60">
            {{ $alreadyDone
                ? 'No change needed — The Daily Brief is already going to this address.'
                : 'The Daily Brief will land in your inbox each morning. One email, the stories that actually matter.' }}
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary">Read the latest</a>
            <a href="{{ route('newsletter.unsubscribe', $subscriber) }}" class="btn-ghost">Unsubscribe</a>
        </div>

        <p class="mt-8 text-xs text-ink/40">
            You can unsubscribe from the link at the bottom of any issue.
            See our <a href="{{ route('privacy-policy') }}" class="underline hover:text-brand">privacy policy</a>.
        </p>
    </div>
@endsection
