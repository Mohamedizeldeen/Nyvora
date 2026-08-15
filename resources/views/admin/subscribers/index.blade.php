@extends('layouts.admin')

@section('title', 'Subscribers')

@section('actions')
    @if ($total > 0)
        <a href="{{ route('admin.subscribers.export') }}" class="btn-primary">Export CSV</a>
    @endif
@endsection

@section('content')
    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-3xl font-black tracking-tight">{{ number_format($total) }}</p>
            <p class="text-sm text-ink/50">{{ Str::plural('address', $total) }} on the list</p>
        </div>

        <form method="GET" action="{{ route('admin.subscribers.index') }}" class="flex items-end gap-2">
            <div>
                <label for="search" class="admin-label">Find an address</label>
                <input id="search" type="search" name="search" value="{{ $search }}"
                       placeholder="name@example.com" class="admin-input">
            </div>
            <button type="submit" class="btn-primary">Search</button>
            @if ($search !== '')
                <a href="{{ route('admin.subscribers.index') }}" class="btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    <div class="admin-card overflow-hidden !p-0">
        @if ($subscribers->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-ink/45">
                {{ $search !== '' ? 'No addresses match that search.' : 'Nobody has subscribed yet.' }}
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[34rem] text-left">
                    <thead class="border-b border-rule bg-paper-soft">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-ink/45">
                            <th scope="col" class="px-5 py-3">Email</th>
                            <th scope="col" class="px-3 py-3">Subscribed</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-rule">
                        @foreach ($subscribers as $subscriber)
                            <tr class="hover:bg-paper-soft/60">
                                <td class="px-5 py-3 text-sm font-semibold">{{ $subscriber->email }}</td>
                                <td class="px-3 py-3 text-sm text-ink/50">
                                    @if ($subscriber->subscribed_at)
                                        <time datetime="{{ $subscriber->subscribed_at->toIso8601String() }}">
                                            {{ $subscriber->subscribed_at->format('j M Y') }}
                                        </time>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.subscribers.destroy', $subscriber) }}"
                                          onsubmit="return confirm('Remove {{ $subscriber->email }} from the list?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Remove</button>
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
