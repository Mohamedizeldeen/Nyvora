@extends('layouts.admin')

@section('title', 'Message')

@section('actions')
    <a href="{{ route('admin.messages.index') }}" class="btn-ghost">Back to messages</a>
@endsection

@section('content')
    <div class="admin-card max-w-3xl">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-rule pb-5">
            <div>
                <span class="inline-flex rounded-full bg-ink/5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink/60">
                    {{ $message->topicLabel() }}
                </span>

                <h2 class="mt-3 text-xl font-black tracking-tight">{{ $message->name }}</h2>

                {{-- The reader's own address. mailto: is fine here: it opens the
                     admin's mail client, and costs the site nothing. --}}
                <a href="mailto:{{ $message->email }}"
                   class="text-sm font-semibold text-brand hover:text-brand-dark">{{ $message->email }}</a>
            </div>

            <p class="text-xs text-ink/45">
                <time datetime="{{ $message->created_at->toIso8601String() }}">
                    {{ $message->created_at->format('j F Y, H:i') }}
                </time>
            </p>
        </div>

        {{-- Escaped, and whitespace preserved so paragraphing survives. --}}
        <div class="mt-6 text-sm leading-relaxed whitespace-pre-line text-ink/80">{{ $message->body }}</div>
    </div>

    <div class="mt-6 max-w-3xl rounded-xl border border-red-200 bg-red-50/50 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <p class="max-w-lg text-sm text-ink/60">
                Deleting removes this message permanently. There is no copy anywhere else.
            </p>

            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}"
                  onsubmit="return confirm('Delete this message? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger !px-4 !py-2.5 !text-sm">Delete message</button>
            </form>
        </div>
    </div>
@endsection
