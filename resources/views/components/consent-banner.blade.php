{{--
    Cookie consent banner.

    Only rendered when something on the page actually needs consent — analytics
    or advertising. With neither configured the site sets nothing but strictly
    necessary cookies, and a banner asking permission for nothing is just noise.

    The choice is kept in localStorage, not a cookie, so refusing does not
    itself store a cookie.
--}}
@if (tracking_needs_consent())
    <div data-consent-banner
         hidden
         role="dialog"
         aria-live="polite"
         aria-labelledby="consent-heading"
         class="fixed inset-x-0 bottom-0 z-[60] border-t border-ink-line bg-ink text-white shadow-2xl">
        <div class="mx-auto flex max-w-5xl flex-col gap-4 px-4 py-5 sm:px-6 md:flex-row md:items-center">
            <div class="flex-1">
                <h2 id="consent-heading" class="text-sm font-black uppercase tracking-wider">Cookies</h2>
                <p class="mt-1.5 text-sm leading-relaxed text-white/70">
                    We would like to use analytics{{ filled(setting('adsense_client_id')) ? ' and advertising' : '' }}
                    cookies to understand which stories are read. They are not required to read the
                    site, and nothing is stored until you choose.
                    <a href="{{ route('cookie-policy') }}" class="underline underline-offset-2 hover:text-brand-light">
                        What we set and why
                    </a>.
                </p>
            </div>

            <div class="flex shrink-0 gap-2">
                <button type="button" data-consent-reject
                        class="rounded-lg border border-ink-line px-4 py-2.5 text-sm font-bold text-white/80 transition-colors hover:bg-white/10 hover:text-white">
                    Reject
                </button>
                <button type="button" data-consent-accept
                        class="rounded-lg bg-brand px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-brand-dark">
                    Accept
                </button>
            </div>
        </div>
    </div>
@endif
