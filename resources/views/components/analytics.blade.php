{{--
    Google Analytics 4, loaded through Google Consent Mode v2.

    Consent Mode is why the tag can be present on the page before the reader has
    chosen: with the defaults below, gtag holds back cookies and identifiers
    entirely, and only starts storing anything after an explicit "update" from
    the consent banner.

    Nothing renders at all when no measurement ID is configured.
--}}
@php($measurementId = analytics_id())

@if ($measurementId)
    {{-- Google tag (gtag.js) --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        // Deny everything until the reader says otherwise. This must run before
        // the config call below, or the first pageview is recorded with cookies.
        gtag('consent', 'default', {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'denied',
            functionality_storage: 'granted',
            security_storage: 'granted',
            wait_for_update: 500
        });

        // Re-apply a choice already made on an earlier visit, before the first hit.
        try {
            var stored = localStorage.getItem('nyvora-consent');
            if (stored === 'granted' || stored === 'denied') {
                var state = stored === 'granted' ? 'granted' : 'denied';
                gtag('consent', 'update', {
                    ad_storage: state,
                    ad_user_data: state,
                    ad_personalization: state,
                    analytics_storage: state
                });
            }
        } catch (e) {
            // Storage blocked — the denied defaults above stand.
        }

        gtag('js', new Date());
        gtag('config', '{{ $measurementId }}');
    </script>
@endif
