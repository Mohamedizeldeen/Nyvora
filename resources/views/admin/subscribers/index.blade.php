@extends('layouts.admin')

@section('title', 'Subscribers')

@section('actions')
    @if (array_sum($counts) > 0)
        <a href="{{ route('admin.subscribers.export') }}" class="btn-primary">Export confirmed</a>
        <a href="{{ route('admin.subscribers.export', ['all' => 1]) }}" class="btn-ghost">Export all</a>
    @endif
@endsection

@section('content')

    {{-- Counts. Only "confirmed" is a mailable list. --}}
    <div class="mb-5 grid gap-4 sm:grid-cols-3">
        @php
            $tiles = [
                ['label' => 'Confirmed', 'value' => $counts['confirmed'], 'note' => 'on the mailing list', 'key' => 'confirmed'],
                ['label' => 'Pending', 'value' => $counts['pending'], 'note' => 'not yet clicked the link', 'key' => 'pending'],
                ['label' => 'Unsubscribed', 'value' => $counts['unsubscribed'], 'note' => 'opted out', 'key' => 'unsubscribed'],
            ];
        @endphp

        @foreach ($tiles as $tile)
            <a href="{{ route('admin.subscribers.index', ['status' => $tile['key']]) }}"
               @class(['admin-card transition-colors hover:border-brand', 'border-brand' => $status === $tile['key']])>
                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-ink/45">{{ $tile['label'] }}</p>
                <p class="mt-2 text-3xl font-black tracking-tight">{{ number_format($tile['value']) }}</p>
                <p class="mt-1 text-xs text-ink/45">{{ $tile['note'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <p class="max-w-xl text-sm text-ink/55">
            The list is double opt-in — a signup only becomes a subscriber once the reader clicks the
            confirmation link Mailgun sends.
            @if ($listSyncEnabled)
                <span class="font-semibold text-ink/70">Confirmed addresses are synced to your Mailgun mailing list.</span>
            @else
                Set <code>MAILGUN_LIST_ADDRESS</code> in <code>.env</code> to sync confirmed addresses
                to a Mailgun mailing list automatically.
            @endif
        </p>

        <form method="GET" action="{{ route('admin.subscribers.index') }}" class="flex items-end gap-2">
            <div>
                <label for="search" class="admin-label">Find an address</label>
                <input id="search" type="search" name="search" value="{{ $search }}"
                       placeholder="name@example.com" class="admin-input">
            </div>
            <button type="submit" class="btn-primary">Search</button>
            @if ($search !== '' || $status !== '')
                <a href="{{ route('admin.subscribers.index') }}" class="btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    <div class="admin-card overflow-hidden !p-0">
        @if ($subscribers->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-ink/45">
                {{ $search !== '' || $status !== '' ? 'No addresses match those filters.' : 'Nobody has subscribed yet.' }}
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[38rem] text-left">
                    <thead class="border-b border-rule bg-paper-soft">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-ink/45">
                            <th scope="col" class="px-5 py-3">Email</th>
                            <th scope="col" class="px-3 py-3">Status</th>
                            <th scope="col" class="px-3 py-3">Signed up</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-rule">
                        @foreach ($subscribers as $subscriber)
                            @php
                                $tone = match ($subscriber->status()) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-800',
                                    'pending' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-ink/10 text-ink/60',
                                };
                            @endphp
                            <tr class="hover:bg-paper-soft/60">
                                <td class="px-5 py-3 text-sm font-semibold">{{ $subscriber->email }}</td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $tone }}">
                                        {{ $subscriber->status() }}
                                    </span>
                                </td>

                                <td class="px-3 py-3 text-sm text-ink/50">
                                    @if ($subscriber->subscribed_at)
                                        <time datetime="{{ $subscriber->subscribed_at->toIso8601String() }}">
                                            {{ $subscriber->subscribed_at->format('j M Y') }}
                                        </time>
                                    @else
                                        &mdash;
                                    @endif
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.subscribers.destroy', $subscriber) }}"
                                          onsubmit="return confirm('Delete {{ $subscriber->email }} from the list?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($subscribers->hasPages())
        <nav class="pagination-nyvora mt-6" aria-label="Pagination">
            {{ $subscribers->onEachSide(1)->links() }}
        </nav>
    @endif
@endsection
