@extends('layouts.admin')

@section('title', 'Dashboard')

@section('actions')
    <a href="{{ route('admin.articles.create') }}" class="btn-primary">New story</a>
@endsection

@section('content')

    {{-- Headline numbers --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $tiles = [
                ['label' => 'Published', 'value' => number_format($stats['published']), 'note' => $stats['drafts'].' drafts · '.$stats['scheduled'].' scheduled'],
                ['label' => 'Total views', 'value' => number_format($stats['views']), 'note' => 'across every story'],
                ['label' => 'Subscribers', 'value' => number_format($stats['subscribers']), 'note' => '+'.$stats['subscribers_week'].' this week'],
                ['label' => 'Newsroom', 'value' => $stats['authors'].' authors', 'note' => $stats['categories'].' sections'],
            ];
        @endphp

        @foreach ($tiles as $tile)
            <div class="admin-card">
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">{{ $tile['label'] }}</p>
                <p class="mt-2 text-3xl font-black tracking-tight">{{ $tile['value'] }}</p>
                <p class="mt-1 text-xs text-ink/45">{{ $tile['note'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">

        {{-- Recently created --}}
        <section class="admin-card">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-wider">Recently added</h2>
                <a href="{{ route('admin.articles.index') }}" class="text-xs font-bold text-brand hover:text-brand-dark">All stories &rarr;</a>
            </div>

            @forelse ($recent as $article)
                <div class="flex items-start justify-between gap-3 border-t border-rule py-3 first:border-t-0 first:pt-0">
                    <div class="min-w-0">
                        <a href="{{ route('admin.articles.edit', $article) }}"
                           class="block truncate text-sm font-bold hover:text-brand">{{ $article->title }}</a>
                        <p class="mt-0.5 text-xs text-ink/45">
                            {{ $article->category?->name ?? 'No section' }}
                            <span aria-hidden="true">·</span>
                            {{ $article->author?->name ?? 'No author' }}
                        </p>
                    </div>
                    <x-admin.status-pill :article="$article" />
                </div>
            @empty
                <p class="py-6 text-center text-sm text-ink/45">No stories yet.</p>
            @endforelse
        </section>

        {{-- Best read --}}
        <section class="admin-card">
            <h2 class="mb-4 text-sm font-black uppercase tracking-wider">Most read</h2>

            @forelse ($topPerforming as $article)
                <div class="flex items-center justify-between gap-3 border-t border-rule py-3 first:border-t-0 first:pt-0">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="text-lg font-black text-ink/20 tabular-nums">{{ $loop->iteration }}</span>
                        <a href="{{ route('admin.articles.edit', $article) }}"
                           class="min-w-0 truncate text-sm font-bold hover:text-brand">{{ $article->title }}</a>
                    </div>
                    <span class="shrink-0 text-xs font-semibold text-ink/50 tabular-nums">
                        {{ number_format($article->views_count) }}
                    </span>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-ink/45">Nothing published yet.</p>
            @endforelse
        </section>
    </div>
@endsection
