@extends('layouts.admin')

@section('title', 'Sections')

@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="btn-primary">New section</a>
@endsection

@section('content')
    <p class="mb-5 max-w-2xl text-sm text-ink/55">
        Sections are the navbar. Each one's colour tints its labels across the whole site — the feed
        tags, the hero chips and the archive masthead.
    </p>

    <div class="admin-card overflow-hidden !p-0">
        @if ($categories->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-ink/45">No sections yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left">
                    <thead class="border-b border-rule bg-paper-soft">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-ink/45">
                            <th scope="col" class="px-5 py-3">Section</th>
                            <th scope="col" class="px-3 py-3">Slug</th>
                            <th scope="col" class="px-3 py-3">Colour</th>
                            <th scope="col" class="px-3 py-3 text-right">Stories</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-rule">
                        @foreach ($categories as $category)
                            <tr class="hover:bg-paper-soft/60">
                                <td class="px-5 py-3">
                                    <span class="text-sm font-bold uppercase tracking-wider"
                                          style="color: {{ $category->displayColor() }}">
                                        {{ $category->name }}
                                    </span>
                                </td>

                                <td class="px-3 py-3 text-sm text-ink/50">/{{ $category->slug }}</td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="size-5 rounded border border-rule"
                                              style="background-color: {{ $category->displayColor() }}"></span>
                                        <code class="text-xs text-ink/50">{{ $category->color }}</code>
                                    </span>
                                </td>

                                <td class="px-3 py-3 text-right text-sm tabular-nums text-ink/60">
                                    {{ $category->articles_count }}
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('category.show', $category) }}" target="_blank" rel="noopener"
                                           class="btn-tiny">View</a>
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-tiny">Edit</a>

                                        @if ($category->articles_count === 0)
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                                  onsubmit="return confirm('Delete the {{ addslashes($category->name) }} section?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger">Delete</button>
                                            </form>
                                        @else
                                            {{-- Deleting is blocked while stories still reference it. --}}
                                            <span class="text-xs text-ink/30" title="Move its stories first">Delete</span>
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
