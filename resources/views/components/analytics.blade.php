{{--
    Google Analytics 4, loaded through Google Consent Mode v2.

    Consent Mode is why the tag can sit on the page before the reader has
    chosen: with everything defaulted to "denied", gtag holds back cookies and
    identifiers entirely and only starts storing after an explicit "update".

    Which switch sends that update depends on the consent manager:

      built_in  Our own banner asks everyone, everywhere. Denied by default
                worldwide, so nothing is stored until someone accepts.

      google    Google's certified CMP asks, but only shows its message in the
                regions that legally require it. Defaulting to denied worldwide
                would then leave every other visitor permanently denied with no
                way to accept — so the denial is scoped to those regions and
                everywhere else starts granted.

    Nothing renders at all when no measurement ID is configured.
--}}
@php
    $measurementId = analytics_id();

    // EEA (EU 27 + Iceland, Liechtenstein, Norway) plus the UK and Switzerland
    // — the regions where Google requires a certified CMP.
    $consentRegions = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
        'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK',
        'SI', 'ES', 'SE', 'IS', 'LI', 'NO', 'GB', 'CH',
    ];
@endphp

@if ($measurementId)
    {{-- Google tag (gtag.js) --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        @if (uses_google_cmp())
            // Denied where consent is required — Google's CMP asks there and
            // sends the update itself.
            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                wait_for_update: 500,
                region: @json($consentRegions)
            });

            // Everywhere else the CMP never appears, so denying by default
            // would mean nothing is ever measured.
            gtag('consent', 'default', {
                ad_storage: 'granted',
                ad_user_data: 'granted',
                ad_personalization: 'granted',
                analytics_storage: 'granted'
            });
        @else
            // Our own banner asks everyone, so deny everywhere until it answers.
            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                functionality_storage: 'granted',
                security_storage: 'granted',
                wait_for_update: 500
            });

            // Re-apply a choice made on an earlier visit, before the first hit.
            try {
                var stored = localStorage.getItem('nyvora-consent');
                if (stored === 'granted' || stored === 'denied') {
                    gtag('consent', 'update', {
                        ad_storage: stored,
                        ad_user_data: stored,
                        ad_personalization: stored,
                        analytics_storage: stored
                    });
                }
            } catch (e) {
                // Storage blocked — the denied defaults above stand.
            }
        @endif

        gtag('js', new Date());
        gtag('config', '{{ $measurementId }}');
    </script>
@endif
