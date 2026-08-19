@extends('layouts.admin')

@section('title', $author->exists ? 'Edit author' : 'New author')

@section('actions')
    @if ($author->exists && $author->articles()->published()->exists())
        <a href="{{ route('authors.show', $author) }}" target="_blank" rel="noopener" class="btn-ghost">View profile</a>
    @endif
    <a href="{{ route('admin.authors.index') }}" class="btn-ghost">Back to authors</a>
@endsection

@section('content')
<form method="POST"
      action="{{ $author->exists ? route('admin.authors.update', $author) : route('admin.authors.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if ($author->exists)
        @method('PUT')
    @endif

    <div class="grid max-w-4xl gap-6 lg:grid-cols-3">
        <div class="admin-card space-y-4 lg:col-span-2">
            <div>
                <label for="name" class="admin-label">Name <span class="text-red-500">*</span></label>
                <input id="name" type="text" name="name" required maxlength="120"
                       value="{{ old('name', $author->name) }}"
                       @class(['admin-input', 'admin-input-invalid' => $errors->has('name')])>
                @error('name')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="slug" class="admin-label">URL slug</label>
                <div class="flex items-center gap-2">
                    <span class="shrink-0 text-xs text-ink/40">/author/</span>
                    <input id="slug" type="text" name="slug" maxlength="140"
                           value="{{ old('slug', $author->slug) }}"
                           placeholder="generated-from-the-name"
                           @class(['admin-input', 'admin-input-invalid' => $errors->has('slug')])>
                </div>
                <p class="admin-hint">Leave blank to build it from the name. Changing it breaks existing links.</p>
                @error('slug')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="bio" class="admin-label">Bio</label>
                <textarea id="bio" name="bio" rows="4" maxlength="600"
                          placeholder="One or two sentences about what they cover."
                          @class(['admin-input resize-y', 'admin-input-invalid' => $errors->has('bio')])>{{ old('bio', $author->bio) }}</textarea>
                <p class="admin-hint">Shown in the “Written by” card at the end of each of their stories.</p>
                @error('bio')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <x-admin.image-field
            label="Avatar"
            file-name="avatar"
            url-name="avatar_url"
            :current="old('avatar_url', $author->avatar_url)"
            preview="aspect-square"
            hint="Square images work best. Without one, the byline falls back to their initials." />
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="btn-primary">
            {{ $author->exists ? 'Save author' : 'Create author' }}
        </button>
        <a href="{{ route('admin.authors.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>
@endsection
