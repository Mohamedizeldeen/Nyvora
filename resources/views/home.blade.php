@extends('layouts.app')

@section('description', 'Breaking technology news, hardware reviews, startup funding and space coverage from the ' . config('app.name') . ' newsroom.')

@section('content')

    {{-- Brand hero: wordmark, two featured stories and the headlines widget --}}
    <x-hero :primary="$heroPrimary" :secondary="$heroSecondary" :headlines="$headlines" />

    {{-- Announcement strip — switched on and written in Admin → Settings --}}
    @if (setting_bool('promo_enabled') && setting('promo_text'))
        <x-promo-banner :eyebrow="setting('promo_eyebrow') ?: null"
                        :tone="setting('promo_tone')"
                        :href="setting('promo_cta_url') ?: null"
                        :cta="setting('promo_cta_label') ?: null">
            {{ setting('promo_text') }}
        </x-promo-banner>
    @endif

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

        {{-- Leaderboard ad above the fold of the feed --}}
        <x-ad-slot slot-id="ad-slot-2" size="728x90" placement="leaderboard" class="mb-10" />

        <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">

            {{-- Latest News --}}
            <div class="lg:col-span-8">
                <x-article-feed :articles="$articles" heading="Latest News" />
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-4">
                <x-sidebar :popular="$popular" />
            </div>
        </div>
    </div>
@endsection
