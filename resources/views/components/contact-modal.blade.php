{{--
    The contact popup. Rendered once per page by the layout; every contact
    button on the site opens this same dialog and preselects a topic.

    Built on <dialog>, so the browser handles the backdrop, focus trapping,
    Esc-to-close and inertness of the page behind it.

    Without JavaScript the dialog never opens — the triggers are ordinary links
    to /contact#contact-form, where the same form is rendered inline.
--}}
@php($sent = session('contact_sent'))

<dialog id="contact-dialog"
        data-contact-dialog
        data-contact-auto-open="{{ $sent ? 1 : 0 }}"
        aria-labelledby="contact-dialog-title"
        {{-- m-auto restores the centring the UA stylesheet gives a modal dialog:
     Tailwind preflight resets margin to 0, which pins it to the top-left. --}}
        class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-rule bg-white p-0
               backdrop:bg-ink/60 backdrop:backdrop-blur-sm">

    {{-- Thank-you state. Swapped in by JS after a successful send, and shown
         straight away when the no-JS path redirects back with a flash. --}}
    <div data-contact-success @class(['p-8 text-center', 'hidden' => ! $sent])>
        <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-brand/10 text-brand">
            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </span>

        <h2 class="mt-5 text-2xl font-black tracking-tight text-ink">Message sent</h2>
        <p data-contact-success-text class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-ink/60">
            {{ $sent ?: 'Thank you — your message has reached the newsroom.' }}
        </p>

        <button type="button" data-contact-close class="btn-primary mt-6">Close</button>
    </div>

    {{-- Form state --}}
    <div data-contact-form-wrap @class(['hidden' => (bool) $sent])>
        <div class="flex items-start justify-between gap-4 border-b border-rule px-6 py-5">
            <div>
                <h2 id="contact-dialog-title" class="text-xl font-black tracking-tight text-ink">Send us a message</h2>
                <p data-contact-blurb class="mt-1 text-sm leading-relaxed text-ink/55">
                    We read everything, and reply to most messages within two business days.
                </p>
            </div>

            <button type="button" data-contact-close
                    class="-mr-1 shrink-0 rounded-md p-1.5 text-ink/40 transition-colors hover:bg-paper-soft hover:text-ink">
                <span class="sr-only">Close</span>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-6 py-5">
            <x-contact-form id-prefix="modal" :show-errors="false" />
        </div>
    </div>
</dialog>
