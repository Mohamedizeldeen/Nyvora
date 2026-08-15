@extends('layouts.app')

@section('title', 'Unsubscribed')
@section('description', 'You have been removed from The Daily Brief.')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
        <h1 class="text-3xl font-black tracking-tighter sm:text-4xl">
            {{ $alreadyDone ? 'You were already unsubscribed' : 'You have been unsubscribed' }}
        </h1>

        <p class="mx-auto mt-4 max-w-lg text-base leading-relaxed text-ink/60">
            {{ $subscriber->email }} will not receive The Daily Brief again. No hard feelings —
            you can resubscribe from any page whenever you like.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="btn-primary">Back to the site</a>
        </div>
    </div>
@endsection
