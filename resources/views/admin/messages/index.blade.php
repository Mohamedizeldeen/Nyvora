@extends('layouts.admin')

@section('title', 'Messages')

@section('content')
    <p class="mb-5 max-w-2xl text-sm text-ink/55">
        Everything readers send through the forms on the site. Nothing is emailed anywhere, so this
        is where messages are read.
    </p>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.messages.index') }}"
          class="admin-card mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label for="topic" class="admin-label">Topic</label>
            <select id="topic" name="topic" class="admin-input">
                <option value="">All topics</option>
                @foreach (\App\Models\ContactMessage::topicOptions() as $value => $label)
                    <option value="{{ $value }}" @selected($topic === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <label class="flex items-center gap-2.5 pb-2.5 text-sm">
            <input type="checkbox" name="unread" value="1" @checked($unreadOnly)
                   class="size-4 rounded border-rule text-brand focus:ring-brand/30">
            <span class="font-semibold">Unread only ({{ $unreadCount }})</span>
        </label>

        <button type="submit" class="btn-primary">Filter</button>

        @if ($topic !== '' || $unreadOnly)
            <a href="{{ route('admin.messages.index') }}" class="btn-ghost">Reset</a>
        @endif
    </form>

    <div class="admin-card overflow-hidden !p-0">
        @if ($messages->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-ink/45">
                {{ $total === 0 ? 'No messages yet.' : 'No messages match those filters.' }}
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] text-left">
                    <thead class="border-b border-rule bg-paper-soft">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-ink/45">
                            <th scope="col" class="px-5 py-3">From</th>
                            <th scope="col" class="px-3 py-3">Topic</th>
                            <th scope="col" class="px-3 py-3">Message</th>
                            <th scope="col" class="px-5 py-3 text-right">Received</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-rule">
                        @foreach ($messages as $message)
                            <tr @class(['hover:bg-paper-soft/60', 'bg-brand/[3%]' => $message->isUnread()])>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.messages.show', $message) }}" class="block">
                                        <span @class(['text-sm', 'font-bold' => $message->isUnread(), 'font-semibold text-ink/70' => ! $message->isUnread()])>
                                            @if ($message->isUnread())
                                                <span class="mr-1.5 inline-block size-2 rounded-full bg-brand align-middle"
                                                      title="Unread"></span>
                                            @endif
                                            {{ $message->name }}
                                        </span>
                                        <span class="block truncate text-xs text-ink/45">{{ $message->email }}</span>
                                    </a>
                                </td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-full bg-ink/5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink/60">
                                        {{ $message->topicLabel() }}
                                    </span>
                                </td>

                                <td class="px-3 py-3">
                                    <a href="{{ route('admin.messages.show', $message) }}"
                                       class="block max-w-md truncate text-sm text-ink/60 hover:text-brand">
                                        {{ $message->body }}
                                    </a>
                                </td>

                                <td class="px-5 py-3 text-right text-xs whitespace-nowrap text-ink/50">
                                    <time datetime="{{ $message->created_at->toIso8601String() }}"
                                          title="{{ $message->created_at->format('j F Y, H:i') }}">
                                        {{ $message->created_at->diffForHumans() }}
                                    </time>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($messages->hasPages())
        <nav class="pagination-nyvora mt-6" aria-label="Pagination">
            {{ $messages->onEachSide(1)->links() }}
        </nav>
    @endif
@endsection
