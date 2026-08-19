{{--
    The message form. Rendered twice per page: inside the popup, and inline on
    the contact page (which is where the popup triggers link to when there is
    no JavaScript).

    Both instances post to the same route. `idPrefix` keeps their field ids
    distinct — two elements sharing an id would break the <label for> pairing
    and is invalid HTML.

    Usage: <x-contact-form id-prefix="modal" />
--}}
@props([
    'idPrefix' => 'contact',
    // The inline copy renders Laravel's validation errors; the popup shows
    // them through JS instead, so it opts out.
    'showErrors' => true,
])

<form method="POST" action="{{ route('contact.send') }}" data-contact-form class="space-y-4">
    @csrf

    {{-- Honeypot: hidden from people, tempting to naive bots. No captcha,
         no third party, no cookies. --}}
    <div class="hidden" aria-hidden="true">
        <label for="{{ $idPrefix }}-website">Website</label>
        <input id="{{ $idPrefix }}-website" type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    {{-- JS writes into this one; the inline copy also renders server errors. --}}
    <div data-contact-errors class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
         role="alert"></div>

    @if ($showErrors && $errors->any())
        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <label for="{{ $idPrefix }}-topic" class="admin-label">What is it about?</label>
        <select id="{{ $idPrefix }}-topic" name="topic" required data-contact-topic class="admin-input">
            @foreach (\App\Models\ContactMessage::TOPICS as $value => $topic)
                <option value="{{ $value }}"
                        data-blurb="{{ $topic['blurb'] }}"
                        @selected(old('topic', session('contact_topic', 'general')) === $value)>
                    {{ $topic['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}-name" class="admin-label">Your name</label>
            <input id="{{ $idPrefix }}-name" type="text" name="name" required maxlength="120"
                   value="{{ old('name') }}" autocomplete="name" data-contact-first
                   @class(['admin-input', 'admin-input-invalid' => $errors->has('name')])>
        </div>

        <div>
            <label for="{{ $idPrefix }}-email" class="admin-label">Your email</label>
            <input id="{{ $idPrefix }}-email" type="email" name="email" required maxlength="254"
                   value="{{ old('email') }}" autocomplete="email"
                   @class(['admin-input', 'admin-input-invalid' => $errors->has('email')])>
            <p class="admin-hint">So we can reply. We use it for nothing else.</p>
        </div>
    </div>

    <div>
        <label for="{{ $idPrefix }}-body" class="admin-label">Message</label>
        <textarea id="{{ $idPrefix }}-body" name="body" rows="6" required minlength="10" maxlength="5000"
                  @class(['admin-input resize-y', 'admin-input-invalid' => $errors->has('body')])>{{ old('body') }}</textarea>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
        <p class="max-w-xs text-xs leading-relaxed text-ink/45">
            We use what you send only to reply. See our
            <a href="{{ route('privacy-policy') }}" class="underline underline-offset-2 hover:text-brand">privacy policy</a>.
        </p>

        <button type="submit" data-contact-submit class="btn-primary shrink-0">Send message</button>
    </div>
</form>
