@extends('layouts.admin')

@section('title', 'Settings')

@section('actions')
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn-ghost">View site</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-3xl space-y-6">
    @csrf
    @method('PUT')

    {{-- ============ Identity ============ --}}
    <section class="admin-card space-y-4">
        <div>
            <h2 class="text-sm font-black uppercase tracking-wider">Site identity</h2>
            <p class="admin-hint !mt-1">
                The site name itself comes from <code>APP_NAME</code> in <code>.env</code>, and the
                brand colours live in <code>resources/css/app.css</code>.
            </p>
        </div>

        <div>
            <label for="site_tagline" class="admin-label">Tagline</label>
            <input id="site_tagline" type="text" name="site_tagline" required maxlength="120"
                   value="{{ old('site_tagline', $settings['site_tagline']) }}"
                   @class(['admin-input', 'admin-input-invalid' => $errors->has('site_tagline')])>
            <p class="admin-hint">Shown under the wordmark in the hero, and in the browser tab.</p>
            @error('site_tagline')<p class="admin-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="footer_description" class="admin-label">Footer blurb</label>
            <textarea id="footer_description" name="footer_description" rows="3" required maxlength="400"
                      @class(['admin-input resize-y', 'admin-input-invalid' => $errors->has('footer_description')])>{{ old('footer_description', $settings['footer_description']) }}</textarea>
            @error('footer_description')<p class="admin-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="articles_per_page" class="admin-label">Stories per page</label>
            <input id="articles_per_page" type="number" name="articles_per_page" required min="3" max="50"
                   value="{{ old('articles_per_page', $settings['articles_per_page']) }}"
                   @class(['admin-input max-w-32', 'admin-input-invalid' => $errors->has('articles_per_page')])>
            <p class="admin-hint">Applies to the homepage feed and the section archives.</p>
            @error('articles_per_page')<p class="admin-error">{{ $message }}</p>@enderror
        </div>
    </section>

    {{-- ============ Promo strip ============ --}}
    <section class="admin-card space-y-4">
        <div>
            <h2 class="text-sm font-black uppercase tracking-wider">Announcement strip</h2>
            <p class="admin-hint !mt-1">The coloured bar under the homepage hero.</p>
        </div>

        <label class="flex items-center gap-2.5 text-sm">
            <input type="checkbox" name="promo_enabled" value="1"
                   @checked(old('promo_enabled', $settings['promo_enabled']) === '1' || old('promo_enabled') === '1')
                   class="size-4 rounded border-rule text-brand focus:ring-brand/30">
            <span class="font-semibold">Show the strip</span>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="promo_eyebrow" class="admin-label">Badge text</label>
                <input id="promo_eyebrow" type="text" name="promo_eyebrow" maxlength="40"
                       value="{{ old('promo_eyebrow', $settings['promo_eyebrow']) }}"
                       placeholder="Nyvora Live"
                       @class(['admin-input', 'admin-input-invalid' => $errors->has('promo_eyebrow')])>
                @error('promo_eyebrow')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="promo_tone" class="admin-label">Colour</label>
                <select id="promo_tone" name="promo_tone" class="admin-input">
                    @foreach (['accent' => 'Amber', 'brand' => 'Brand violet', 'ink' => 'Near-black'] as $value => $text)
                        <option value="{{ $value }}" @selected(old('promo_tone', $settings['promo_tone']) === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="promo_text" class="admin-label">Message</label>
            <input id="promo_text" type="text" name="promo_text" maxlength="200"
                   value="{{ old('promo_text', $settings['promo_text']) }}"
                   placeholder="Berlin, 14 November — a day of talks on what actually ships next year."
                   @class(['admin-input', 'admin-input-invalid' => $errors->has('promo_text')])>
            @error('promo_text')<p class="admin-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="promo_cta_label" class="admin-label">Button label</label>
                <input id="promo_cta_label" type="text" name="promo_cta_label" maxlength="30"
                       value="{{ old('promo_cta_label', $settings['promo_cta_label']) }}"
                       placeholder="Get tickets"
                       @class(['admin-input', 'admin-input-invalid' => $errors->has('promo_cta_label')])>
                <p class="admin-hint">Leave blank for a strip with no button.</p>
                @error('promo_cta_label')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="promo_cta_url" class="admin-label">Button link</label>
                <input id="promo_cta_url" type="url" name="promo_cta_url" maxlength="2048"
                       value="{{ old('promo_cta_url', $settings['promo_cta_url']) }}"
                       placeholder="https://example.com/tickets"
                       @class(['admin-input', 'admin-input-invalid' => $errors->has('promo_cta_url')])>
                @error('promo_cta_url')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- ============ Search engines ============ --}}
    <section class="admin-card space-y-4">
        <div>
            <h2 class="text-sm font-black uppercase tracking-wider">Search engines</h2>
            <p class="admin-hint !mt-1">
                Controls <code>robots.txt</code> and the page-level robots tag.
            </p>
        </div>

        @php($envBlocks = ! config('seo.indexable'))

        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" name="search_indexable" value="1"
                   @checked(old('search_indexable', $settings['search_indexable']) === '1' || old('search_indexable') === '1')
                   @disabled($envBlocks)
                   class="mt-0.5 size-4 rounded border-rule text-brand focus:ring-brand/30 disabled:opacity-40">
            <span>
                <span class="font-semibold">Allow search engines to index this site</span>
                <span class="block text-xs text-ink/45">
                    Turn this off only for a staging copy. While it is off, every page carries
                    <code>noindex</code> and <code>robots.txt</code> disallows everything.
                </span>
            </span>
        </label>

        @if ($envBlocks)
            {{-- The env switch wins, so make it obvious why the toggle is inert. --}}
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <strong class="font-bold">Blocked by the environment.</strong>
                <code>SITE_INDEXABLE</code> is set to false in <code>.env</code>, so this site is not
                indexable no matter what this toggle says. Remove it to allow indexing.
            </p>
        @elseif (! setting_bool('search_indexable'))
            <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <strong class="font-bold">This site is hidden from search engines.</strong>
                No page will appear in Google results until this is switched back on.
            </p>
        @endif
    </section>

    {{-- ============ AdSense ============ --}}
    <section class="admin-card space-y-4">
        <div>
            <h2 class="text-sm font-black uppercase tracking-wider">Google AdSense</h2>
            <p class="admin-hint !mt-1">
                Entering your publisher ID adds the AdSense loader script to every public page. The
                individual ad units still need pasting into
                <code>resources/views/components/ad-slot.blade.php</code>.
            </p>
        </div>

        <div>
            <label for="adsense_client_id" class="admin-label">Publisher ID</label>
            <input id="adsense_client_id" type="text" name="adsense_client_id" maxlength="40"
                   value="{{ old('adsense_client_id', $settings['adsense_client_id']) }}"
                   placeholder="ca-pub-1234567890123456"
                   @class(['admin-input font-mono', 'admin-input-invalid' => $errors->has('adsense_client_id')])>
            <p class="admin-hint">Leave blank until your account is approved — no script is loaded while it is empty.</p>
            @error('adsense_client_id')<p class="admin-error">{{ $message }}</p>@enderror
        </div>
    </section>

    <div class="flex items-center gap-3">
        <button type="submit" class="btn-primary">Save settings</button>
    </div>
</form>
@endsection
