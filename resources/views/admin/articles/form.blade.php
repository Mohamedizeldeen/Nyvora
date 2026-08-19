@extends('layouts.admin')

@section('title', $article->exists ? 'Edit story' : 'New story')

@section('actions')
    @if ($article->exists && $article->isPublished())
        <a href="{{ route('article.show', $article) }}" target="_blank" rel="noopener" class="btn-ghost">View live</a>
    @endif
    <a href="{{ route('admin.articles.index') }}" class="btn-ghost">Back to stories</a>
@endsection

@section('content')
<form method="POST"
      action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if ($article->exists)
        @method('PUT')
    @endif

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- ================= Main column ================= --}}
        <div class="space-y-5 lg:col-span-2">

            <div class="admin-card space-y-4">
                <div>
                    <label for="title" class="admin-label">Headline <span class="text-red-500">*</span></label>
                    <input id="title" type="text" name="title" required maxlength="255"
                           value="{{ old('title', $article->title) }}"
                           @class(['admin-input text-base font-bold', 'admin-input-invalid' => $errors->has('title')])>
                    @error('title')<p class="admin-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="slug" class="admin-label">URL slug</label>
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs text-ink/40">/article/</span>
                        <input id="slug" type="text" name="slug" maxlength="255"
                               value="{{ old('slug', $article->slug) }}"
                               placeholder="generated-from-the-headline"
                               @class(['admin-input', 'admin-input-invalid' => $errors->has('slug')])>
                    </div>
                    <p class="admin-hint">Leave blank to build it from the headline. Changing it breaks existing links.</p>
                    @error('slug')<p class="admin-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="excerpt" class="admin-label">Standfirst / excerpt</label>
                    <textarea id="excerpt" name="excerpt" rows="3" maxlength="500"
                              placeholder="One or two sentences shown in the feed and on social cards."
                              @class(['admin-input resize-y', 'admin-input-invalid' => $errors->has('excerpt')])>{{ old('excerpt', $article->excerpt) }}</textarea>
                    @error('excerpt')<p class="admin-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="admin-card">
                <label for="body" class="admin-label">Body <span class="text-red-500">*</span></label>
                <textarea id="body" name="body" rows="24" required
                          @class(['admin-input resize-y font-mono text-sm leading-relaxed', 'admin-input-invalid' => $errors->has('body')])>{{ old('body', $article->body) }}</textarea>

                {{-- The renderer in <x-article-body> understands exactly these three shapes. --}}
                <div class="admin-hint">
                    <p class="font-bold text-ink/60">Formatting</p>
                    <ul class="mt-1 space-y-0.5">
                        <li>Separate paragraphs with a <strong>blank line</strong>.</li>
                        <li>Start a line with <code class="rounded bg-paper-soft px-1">##&nbsp;</code> for a subheading.</li>
                        <li>Start a line with <code class="rounded bg-paper-soft px-1">&gt;&nbsp;</code> for a pull quote.</li>
                    </ul>
                    <p class="mt-1.5">Text is escaped on output, so HTML tags will show as plain text rather than render.</p>
                </div>
                @error('body')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- ================= Side column ================= --}}
        <div class="space-y-5">

            {{-- Publishing --}}
            <div class="admin-card space-y-4">
                <h2 class="text-sm font-black uppercase tracking-wider">Publishing</h2>

                <div>
                    <label for="published_at" class="admin-label">Publish date &amp; time</label>
                    <input id="published_at" type="datetime-local" name="published_at"
                           value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}"
                           @class(['admin-input', 'admin-input-invalid' => $errors->has('published_at')])>
                    <p class="admin-hint">
                        Empty = draft. A future date schedules the story — it stays hidden until then.
                        {{-- The field is read in the app timezone, not the browser's, so state it
                             plainly rather than letting an editor guess. --}}
                        <span class="mt-1 block font-semibold text-ink/60">
                            Times are {{ config('app.timezone') }} — it is
                            {{ now()->format('H:i') }} there now.
                        </span>
                    </p>
                    @error('published_at')<p class="admin-error">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-start gap-2.5 text-sm">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $article->is_featured))
                           class="mt-0.5 size-4 rounded border-rule text-brand focus:ring-brand/30">
                    <span>
                        <span class="font-semibold">Feature on the homepage</span>
                        <span class="block text-xs text-ink/45">The two newest featured stories fill the hero.</span>
                    </span>
                </label>

                <label class="flex items-start gap-2.5 text-sm">
                    <input type="checkbox" name="comments_open" value="1"
                           @checked(old('comments_open', $article->exists ? $article->comments_open : true))
                           class="mt-0.5 size-4 rounded border-rule text-brand focus:ring-brand/30">
                    <span>
                        <span class="font-semibold">Allow comments on this story</span>
                        <span class="block text-xs text-ink/45">
                            Untick to close comments here without affecting other stories. Comments
                            already approved stay visible.
                        </span>
                    </span>
                </label>

                <div>
                    <label for="views_count" class="admin-label">View count</label>
                    <input id="views_count" type="number" name="views_count" min="0"
                           value="{{ old('views_count', $article->views_count ?? 0) }}"
                           @class(['admin-input', 'admin-input-invalid' => $errors->has('views_count')])>
                    <p class="admin-hint">Drives the “Most Popular” widget. Normally left alone.</p>
                    @error('views_count')<p class="admin-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Filing --}}
            <div class="admin-card space-y-4">
                <h2 class="text-sm font-black uppercase tracking-wider">Filing</h2>

                <div>
                    <label for="category_id" class="admin-label">Section <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required
                            @class(['admin-input', 'admin-input-invalid' => $errors->has('category_id')])>
                        <option value="">Choose a section…</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $article->category_id) === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="admin-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="author_id" class="admin-label">Author <span class="text-red-500">*</span></label>
                    <select id="author_id" name="author_id" required
                            @class(['admin-input', 'admin-input-invalid' => $errors->has('author_id')])>
                        <option value="">Choose an author…</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}" @selected((string) old('author_id', $article->author_id) === (string) $author->id)>
                                {{ $author->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('author_id')<p class="admin-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Image --}}
            <x-admin.image-field
                label="Thumbnail"
                file-name="thumbnail"
                url-name="thumbnail_url"
                :current="old('thumbnail_url', $article->thumbnail_url)"
                hint="Used in the feed, the hero and the social card. 1200×800 or larger works best." />
        </div>
    </div>

    {{-- Save bar --}}
    <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-rule pt-5">
        <button type="submit" class="btn-primary">
            {{ $article->exists ? 'Save changes' : 'Create story' }}
        </button>
        <a href="{{ route('admin.articles.index') }}" class="btn-ghost">Cancel</a>

        @if ($article->exists)
            <span class="ml-auto text-xs text-ink/40">
                Last saved {{ $article->updated_at?->diffForHumans() }}
            </span>
        @endif
    </div>
</form>

@if ($article->exists)
    {{-- Kept outside the edit form: nested <form> elements are invalid HTML. --}}
    <div class="mt-8 rounded-xl border border-red-200 bg-red-50/50 p-5">
        <h2 class="text-sm font-black uppercase tracking-wider text-red-700">Danger zone</h2>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-4">
            <p class="max-w-lg text-sm text-ink/60">
                Deleting removes the story, its uploaded image and its view count. Anyone following
                a link to it will get a 404. To take it offline without losing it, set the publish
                date to empty instead.
            </p>

            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
                  onsubmit="return confirm('Delete “{{ addslashes($article->title) }}”? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger !px-4 !py-2.5 !text-sm">Delete this story</button>
            </form>
        </div>
    </div>
@endif
@endsection
