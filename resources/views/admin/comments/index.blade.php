@extends('layouts.admin')

@section('title', 'Comments')

@section('content')
    <p class="mb-5 max-w-2xl text-sm text-ink/55">
        Every comment a reader writes waits here. Nothing appears on the site until you approve it.
    </p>

    {{-- Queue / published tabs --}}
    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ([
            'pending' => ['label' => 'Waiting for approval', 'count' => $pendingCount],
            'approved' => ['label' => 'Published', 'count' => $approvedCount],
            '' => ['label' => 'All', 'count' => $pendingCount + $approvedCount],
        ] as $key => $tab)
            <a href="{{ route('admin.comments.index', $key === '' ? [] : ['status' => $key]) }}"
               @class([
                   'rounded-lg border px-4 py-2 text-sm font-bold transition-colors',
                   'border-brand bg-brand text-white' => $status === $key,
                   'border-rule bg-white text-ink/65 hover:bg-paper-soft' => $status !== $key,
               ])>
                {{ $tab['label'] }}
                <span class="ml-1 tabular-nums opacity-70">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </div>

    @if ($comments->isEmpty())
        <div class="admin-card">
            <p class="py-12 text-center text-sm text-ink/45">
                {{ $status === 'pending' ? 'Nothing waiting — the queue is clear.' : 'No comments here.' }}
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($comments as $comment)
                <article @class(['admin-card', 'border-brand/40' => ! $comment->isApproved()])>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-baseline gap-x-2.5">
                                <p class="text-sm font-extrabold tracking-tight">{{ $comment->name }}</p>

                                @if ($comment->isApproved())
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                                        Published
                                    </span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-800">
                                        Waiting
                                    </span>
                                @endif

                                <time datetime="{{ $comment->created_at->toIso8601String() }}"
                                      class="text-xs text-ink/40">{{ $comment->created_at->diffForHumans() }}</time>
                            </div>

                            <p class="mt-1 text-xs text-ink/45">
                                on
                                @if ($comment->article)
                                    <a href="{{ route('article.show', $comment->article) }}" target="_blank" rel="noopener"
                                       class="font-semibold text-brand hover:text-brand-dark">{{ $comment->article->title }}</a>
                                @else
                                    a deleted story
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Escaped, line breaks kept. --}}
                    <p class="mt-3 text-sm leading-relaxed whitespace-pre-line text-ink/80">{{ $comment->body }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-rule pt-4">
                        @if ($comment->isApproved())
                            <form method="POST" action="{{ route('admin.comments.unapprove', $comment) }}">
                                @csrf
                                <button type="submit" class="btn-ghost !px-3 !py-2 !text-xs">Hide again</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                @csrf
                                <button type="submit" class="btn-primary !px-4 !py-2 !text-xs">Approve</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}"
                              onsubmit="return confirm('Delete this comment permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger">Delete</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @if ($comments->hasPages())
        <nav class="pagination-nyvora mt-6" aria-label="Pagination">
            {{ $comments->onEachSide(1)->links() }}
        </nav>
    @endif
@endsection
