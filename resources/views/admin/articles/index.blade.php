@extends('layouts.admin')

@section('title', 'Stories')

@section('actions')
    <a href="{{ route('admin.articles.create') }}" class="btn-primary">New story</a>
@endsection

@section('content')

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.articles.index') }}"
          class="admin-card mb-5 flex flex-wrap items-end gap-3">
        <div class="min-w-56 flex-1">
            <label for="search" class="admin-label">Search headlines</label>
            <input id="search" type="search" name="search" value="{{ $search }}"
                   placeholder="Search…" class="admin-input">
        </div>

        <div>
            <label for="category" class="admin-label">Section</label>
            <select id="category" name="category" class="admin-input">
                <option value="">All sections</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="admin-label">Status</label>
            <select id="status" name="status" class="admin-input">
                @foreach (['' => 'Any status', 'published' => 'Live', 'draft' => 'Draft', 'scheduled' => 'Scheduled', 'featured' => 'Featured'] as $value => $text)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary">Filter</button>

        @if ($search !== '' || $status !== '' || $categoryId)
            <a href="{{ route('admin.articles.index') }}" class="btn-ghost">Reset</a>
        @endif
    </form>

    {{-- List --}}
    <div class="admin-card overflow-hidden !p-0">
        @if ($articles->isEmpty())
            <p class="px-5 py-14 text-center text-sm text-ink/45">No stories match those filters.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left">
                    <thead class="border-b border-rule bg-paper-soft">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-ink/45">
                            <th scope="col" class="px-5 py-3">Story</th>
                            <th scope="col" class="px-3 py-3">Section</th>
                            <th scope="col" class="px-3 py-3">Status</th>
                            <th scope="col" class="px-3 py-3 text-right">Views</th>
                            <th scope="col" class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-rule">
                        @foreach ($articles as $article)
                            <tr class="align-middle hover:bg-paper-soft/60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-thumbnail :article="$article" class="size-11 shrink-0 rounded-md" />
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.articles.edit', $article) }}"
                                               class="block max-w-md truncate text-sm font-bold hover:text-brand">
                                                {{ $article->title }}
                                            </a>
                                            <p class="mt-0.5 truncate text-xs text-ink/45">
                                                {{ $article->author?->name ?? 'No author' }}
                                                @if ($article->published_at)
                                                    <span aria-hidden="true">·</span>
                                                    {{ $article->published_at->format('j M Y') }}
                                                @endif
                                                @if ($article->is_featured)
                                                    <span class="ml-1 font-bold text-brand">★ Featured</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-3">
                                    @if ($article->category)
                                        <span class="text-xs font-bold uppercase tracking-wider"
                                              style="color: {{ $article->category->displayColor() }}">
                                            {{ $article->category->name }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3 py-3"><x-admin.status-pill :article="$article" /></td>

                                <td class="px-3 py-3 text-right text-sm tabular-nums text-ink/60">
                                    {{ number_format($article->views_count) }}
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Quick toggles --}}
                                        <form method="POST" action="{{ route('admin.articles.publish', $article) }}">
                                            @csrf
                                            <button type="submit" class="btn-tiny">
                                                {{ $article->isPublished() ? 'Unpublish' : 'Publish' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.articles.feature', $article) }}">
                                            @csrf
                                            <button type="submit" class="btn-tiny">
                                                {{ $article->is_featured ? 'Unfeature' : 'Feature' }}
                                            </button>
                                        </form>

                                        @if ($article->isPublished())
                                            <a href="{{ route('article.show', $article) }}" target="_blank" rel="noopener"
                                               class="btn-tiny">View</a>
                                        @endif

                                        {{-- Deleting lives on the edit screen, so it takes a
                                             deliberate click-through rather than sitting one
                                             stray tap away in a long list. --}}
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="btn-tiny">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($articles->hasPages())
        <nav class="pagination-nyvora mt-6" aria-label="Pagination">
            {{ $articles->onEachSide(1)->links() }}
        </nav>
    @endif
@endsection
