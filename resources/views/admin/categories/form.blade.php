@extends('layouts.admin')

@section('title', $category->exists ? 'Edit section' : 'New section')

@section('actions')
    <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Back to sections</a>
@endsection

@section('content')
<form method="POST"
      action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
      class="max-w-xl">
    @csrf
    @if ($category->exists)
        @method('PUT')
    @endif

    <div class="admin-card space-y-4">
        <div>
            <label for="name" class="admin-label">Name <span class="text-red-500">*</span></label>
            <input id="name" type="text" name="name" required maxlength="60"
                   value="{{ old('name', $category->name) }}"
                   placeholder="Security"
                   @class(['admin-input', 'admin-input-invalid' => $errors->has('name')])>
            <p class="admin-hint">Shown in the navbar, so keep it short.</p>
            @error('name')<p class="admin-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="slug" class="admin-label">URL slug</label>
            <div class="flex items-center gap-2">
                <span class="shrink-0 text-xs text-ink/40">/category/</span>
                <input id="slug" type="text" name="slug" maxlength="60"
                       value="{{ old('slug', $category->slug) }}"
                       placeholder="security"
                       @class(['admin-input', 'admin-input-invalid' => $errors->has('slug')])>
            </div>
            <p class="admin-hint">Leave blank to build it from the name.</p>
            @error('slug')<p class="admin-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="color" class="admin-label">Label colour <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-3">
                {{-- The picker and the text field edit the same value; the text
                     field is what actually submits, so a hex can be pasted. --}}
                <input type="color" value="{{ old('color', $category->color ?? '#5B2BEF') }}"
                       aria-label="Colour picker"
                       class="size-11 shrink-0 cursor-pointer rounded-lg border border-rule bg-white p-1"
                       oninput="document.getElementById('color').value = this.value.toUpperCase()">
                <input id="color" type="text" name="color" required maxlength="7"
                       value="{{ old('color', $category->color ?? '#5B2BEF') }}"
                       pattern="#[0-9A-Fa-f]{6}"
                       placeholder="#5B2BEF"
                       @class(['admin-input font-mono uppercase', 'admin-input-invalid' => $errors->has('color')])>
            </div>
            <p class="admin-hint">
                Labels sit on a white background, so pick something dark enough to read.
            </p>
            @error('color')<p class="admin-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="btn-primary">
            {{ $category->exists ? 'Save section' : 'Create section' }}
        </button>
        <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>
@endsection
