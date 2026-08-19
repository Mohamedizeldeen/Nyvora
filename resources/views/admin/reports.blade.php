@extends('layouts.admin')

@section('title', 'Reports')

@section('actions')
    @foreach ($ranges as $value => $label)
        <a href="{{ route('admin.reports.index', ['days' => $value]) }}"
           @class([
               'rounded-lg border px-3.5 py-2 text-xs font-bold transition-colors',
               'border-brand bg-brand text-white' => $days === $value,
               'border-rule bg-white text-ink/65 hover:bg-paper-soft' => $days !== $value,
           ])>{{ $label }}</a>
    @endforeach
@endsection

@section('content')

    {{-- ============ Headline numbers ============ --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $tiles = [
                [
                    'label' => 'Views',
                    'value' => number_format($viewsInRange),
                    'note' => $change === null
                        ? 'in the last '.$days.' days'
                        : sprintf('%s%s%% vs the previous %d days', $change >= 0 ? '+' : '', $change, $days),
                    'tone' => $change === null ? 'neutral' : ($change >= 0 ? 'up' : 'down'),
                ],
                ['label' => 'Published', 'value' => number_format($publishedInRange), 'note' => $totalPublished.' live in total', 'tone' => 'neutral'],
                ['label' => 'Comments', 'value' => number_format($commentsInRange), 'note' => $pendingComments.' waiting for approval', 'tone' => $pendingComments > 0 ? 'attention' : 'neutral'],
                ['label' => 'Messages', 'value' => number_format($messagesInRange), 'note' => $unreadMessages.' unread', 'tone' => $unreadMessages > 0 ? 'attention' : 'neutral'],
            ];
        @endphp

        @foreach ($tiles as $tile)
            <div class="admin-card">
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">{{ $tile['label'] }}</p>
                <p class="mt-2 text-3xl font-black tracking-tight">{{ $tile['value'] }}</p>
                <p @class([
                    'mt-1 text-xs',
                    'text-emerald-700 font-semibold' => $tile['tone'] === 'up',
                    'text-red-600 font-semibold' => $tile['tone'] === 'down',
                    'text-brand font-semibold' => $tile['tone'] === 'attention',
                    'text-ink/45' => $tile['tone'] === 'neutral',
                ])>{{ $tile['note'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ============ Views over time ============ --}}
    {{-- Plain CSS bars rather than a charting library: no third-party script,
         nothing to load, and it prints and scales like the rest of the page. --}}
    <section class="admin-card mt-6">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-sm font-black uppercase tracking-wider">Views per day</h2>
            <p class="text-xs text-ink/45">
                {{ $from->format('j M') }} &ndash; {{ now()->format('j M Y') }} &middot; peak {{ number_format($peak) }}
            </p>
        </div>

        @if ($viewsInRange === 0)
            <p class="py-12 text-center text-sm text-ink/45">
                No views recorded yet in this range. Counting starts the first time an article is read.
            </p>
        @else
            <div class="mt-6 flex h-48 items-end gap-px" role="img"
                 aria-label="Daily views for the last {{ $days }} days, peaking at {{ $peak }}.">
                @foreach ($series as $point)
                    <div class="group relative flex h-full flex-1 items-end">
                        <div class="w-full rounded-t-sm bg-brand/85 transition-colors group-hover:bg-brand"
                             style="height: {{ max(1, round($point['views'] / $peak * 100)) }}%"></div>

                        {{-- Hover detail, kept as a title so it needs no JS. --}}
                        <span class="absolute inset-0" title="{{ $point['date']->format('D j M') }}: {{ number_format($point['views']) }} views"></span>
                    </div>
                @endforeach
            </div>

            <div class="mt-2 flex justify-between text-[10px] font-semibold uppercase tracking-wider text-ink/35">
                <span>{{ $from->format('j M') }}</span>
                <span>{{ now()->format('j M') }}</span>
            </div>
        @endif
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- ============ Most read ============ --}}
        <section class="admin-card">
            <h2 class="mb-4 text-sm font-black uppercase tracking-wider">Most read in this period</h2>

            @forelse ($topArticles as $row)
                <div class="flex items-center justify-between gap-3 border-t border-rule py-3 first:border-t-0 first:pt-0">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="w-5 shrink-0 text-sm font-black text-ink/25 tabular-nums">{{ $loop->iteration }}</span>
                        <a href="{{ route('admin.articles.edit', $row->slug) }}"
                           class="min-w-0 truncate text-sm font-bold hover:text-brand">{{ $row->title }}</a>
                    </div>
                    <span class="shrink-0 text-xs font-semibold tabular-nums text-ink/50">{{ number_format($row->views) }}</span>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-ink/45">Nothing read yet in this period.</p>
            @endforelse
        </section>

        {{-- ============ By section ============ --}}
        <section class="admin-card">
            <h2 class="mb-4 text-sm font-black uppercase tracking-wider">Views by section</h2>

            @php($sectionPeak = max(1, (int) ($byCategory->max('views') ?? 0)))

            @forelse ($byCategory as $row)
                <div class="border-t border-rule py-3 first:border-t-0 first:pt-0">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider"
                              style="color: {{ preg_match('/^#[0-9A-Fa-f]{6}$/', $row->color) ? $row->color : '#5B2BEF' }}">
                            {{ $row->name }}
                        </span>
                        <span class="text-xs font-semibold tabular-nums text-ink/50">{{ number_format($row->views) }}</span>
                    </div>
                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-ink/5">
                        <div class="h-full rounded-full"
                             style="width: {{ round($row->views / $sectionPeak * 100) }}%; background-color: {{ preg_match('/^#[0-9A-Fa-f]{6}$/', $row->color) ? $row->color : '#5B2BEF' }}"></div>
                    </div>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-ink/45">No section data yet.</p>
            @endforelse
        </section>

        {{-- ============ By author ============ --}}
        <section class="admin-card">
            <h2 class="mb-4 text-sm font-black uppercase tracking-wider">Views by author</h2>

            @forelse ($byAuthor as $row)
                <div class="flex items-center justify-between gap-3 border-t border-rule py-3 first:border-t-0 first:pt-0">
                    <a href="{{ route('authors.show', $row->slug) }}" target="_blank" rel="noopener"
                       class="min-w-0 truncate text-sm font-semibold hover:text-brand">{{ $row->name }}</a>
                    <span class="shrink-0 text-xs font-semibold tabular-nums text-ink/50">{{ number_format($row->views) }}</span>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-ink/45">No author data yet.</p>
            @endforelse
        </section>

        {{-- ============ Google Analytics ============ --}}
        <section class="admin-card">
            <h2 class="mb-4 text-sm font-black uppercase tracking-wider">Google Analytics</h2>

            @if ($analyticsId)
                <p class="text-sm leading-relaxed text-ink/60">
                    Analytics is connected as <code class="rounded bg-paper-soft px-1.5 py-0.5 text-xs">{{ $analyticsId }}</code>.
                    The numbers on this page come from our own database and count every read.
                    Analytics answers a different question — where readers came from, what they did
                    next — and only counts visitors who accepted cookies.
                </p>

                <a href="https://analytics.google.com/analytics/web/" target="_blank" rel="noopener"
                   class="btn-primary mt-4">Open Google Analytics</a>

                <p class="admin-hint">
                    Both are worth watching: ours is complete but only about this site, theirs is
                    partial but tells you how people found you.
                </p>
            @else
                <p class="text-sm leading-relaxed text-ink/60">
                    No measurement ID saved, so no analytics code loads. Add one in
                    <a href="{{ route('admin.settings.edit') }}" class="font-semibold text-brand hover:text-brand-dark">Settings</a>
                    to see acquisition and audience data alongside these figures.
                </p>
            @endif
        </section>
    </div>

    <p class="mt-6 text-xs leading-relaxed text-ink/45">
        Lifetime views across every article: <strong class="text-ink/60">{{ number_format($lifetimeViews) }}</strong>.
        Daily figures are counted from the day this feature was added, so early ranges may look thin.
        All of it is aggregate — no reader is identified, and nothing here depends on cookies.
    </p>
@endsection
