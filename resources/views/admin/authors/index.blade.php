@extends('layouts.admin')

@section('title', 'Authors')

@section('actions')
    <a href="{{ route('admin.authors.create') }}" class="btn-primary">New author</a>
@endsection

@section('content')
    <p class="mb-5 max-w-2xl text-sm text-ink/55">
        Bylines are separate from login accounts — a contributor can be credited on a story without
        having access to this dashboard.
    </p>

    <div class="admin-card overflow-hidden !p-0">
        @if ($authors->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-ink/45">No authors yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left">
                    <thead class="border-b border-rule bg-paper-soft">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-ink/45">
                            <th scope="col" class="px-5 py-3">Author</th>
                            <th scope="col" class="px-3 py-3">Bio</th>
                            <th scope="col" class="px-3 py-3 text-right">Stories</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-rule">
                        @foreach ($authors as $author)
                            <tr class="hover:bg-paper-soft/60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="relative flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand text-xs font-bold text-white">
                                            {{ $author->initials() }}
                                            @if ($author->avatar_url)
                                                <img src="{{ $author->avatar_url }}" alt="" loading="lazy"
                                                     class="absolute inset-0 size-full object-cover" onerror="this.remove()">
                                            @endif
                                        </span>
                                        <span class="text-sm font-bold">{{ $author->name }}</span>
                                    </div>
                                </td>

                                <td class="px-3 py-3">
                                    <p class="max-w-md truncate text-sm text-ink/50">{{ $author->bio ?: '—' }}</p>
                                </td>

                                <td class="px-3 py-3 text-right text-sm tabular-nums text-ink/60">
                                    {{ $author->articles_count }}
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.authors.edit', $author) }}" class="btn-tiny">Edit</a>

                                        @if ($author->articles_count === 0)
                                            <form method="POST" action="{{ route('admin.authors.destroy', $author) }}"
                                                  onsubmit="return confirm('Delete {{ addslashes($author->name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger">Delete</button>
                                            </form>
                                        @else
                                            {{-- Blocked while stories still carry this byline. --}}
                                            <span class="text-xs text-ink/30" title="Reassign their stories first">Delete</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
