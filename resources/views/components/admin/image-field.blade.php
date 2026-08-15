{{--
    "Upload a file, or paste a URL" image control, shared by the article
    thumbnail and the author avatar.

    It posts three inputs, which ManagesImageField reads in this order:
      {file-name}          — an uploaded file wins
      remove_{file-name}   — otherwise, the clear checkbox
      {url-name}           — otherwise, the URL text field is the value

    Usage:
      <x-admin.image-field label="Thumbnail" file-name="thumbnail"
                           url-name="thumbnail_url" :current="$article->thumbnail_url" />
--}}
@props([
    'label',
    'fileName',
    'urlName',
    'current' => null,
    'hint' => null,
    'preview' => 'aspect-[16/10]',
])

<div class="admin-card space-y-4">
    <h2 class="text-sm font-black uppercase tracking-wider">{{ $label }}</h2>

    @if ($current)
        <div>
            <img src="{{ $current }}" alt="Current {{ Str::lower($label) }}"
                 class="{{ $preview }} w-full rounded-lg border border-rule object-cover"
                 onerror="this.closest('div').innerHTML='<p class=&quot;admin-hint&quot;>The current image could not be loaded.</p>'">
        </div>

        <label class="flex items-center gap-2.5 text-sm">
            <input type="checkbox" name="remove_{{ $fileName }}" value="1"
                   class="size-4 rounded border-rule text-brand focus:ring-brand/30">
            <span class="font-semibold text-red-600">Remove this image</span>
        </label>
    @endif

    <div>
        <label for="{{ $fileName }}" class="admin-label">Upload a file</label>
        <input id="{{ $fileName }}" type="file" name="{{ $fileName }}" accept="image/*"
               class="w-full text-sm text-ink/60 file:mr-3 file:rounded-md file:border-0 file:bg-brand file:px-3.5
                      file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-brand-dark">
        @error($fileName)<p class="admin-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="{{ $urlName }}" class="admin-label">…or use an image URL</label>
        <input id="{{ $urlName }}" type="url" name="{{ $urlName }}" value="{{ $current }}"
               placeholder="https://example.com/photo.jpg"
               @class(['admin-input', 'admin-input-invalid' => $errors->has($urlName)])>
        @error($urlName)<p class="admin-error">{{ $message }}</p>@enderror
    </div>

    @if ($hint)
        <p class="admin-hint">{{ $hint }}</p>
    @endif
</div>
