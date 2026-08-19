{{--
    The cookies this site actually sets, read from config so the documentation
    cannot drift from the application.

    Shared by the privacy policy and the cookie policy — one table, one truth.
    The analytics rows appear only when a measurement ID is configured.
--}}
<div {{ $attributes->class('overflow-x-auto') }}>
    <table class="w-full min-w-[36rem] border-collapse text-left text-sm">
        <thead>
            <tr class="border-b-2 border-rule text-[11px] font-bold uppercase tracking-wider text-ink/45">
                <th scope="col" class="py-3 pr-4">Cookie</th>
                <th scope="col" class="py-3 pr-4">Type</th>
                <th scope="col" class="py-3 pr-4">Purpose</th>
                <th scope="col" class="py-3">Expires</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-rule">
            <tr>
                <td class="py-3 pr-4 font-mono text-xs">{{ config('session.cookie') }}</td>
                <td class="py-3 pr-4 text-ink/70">Strictly necessary</td>
                <td class="py-3 pr-4 text-ink/70">
                    Keeps your session, so things like the newsletter confirmation message
                    survive a page reload.
                </td>
                <td class="py-3 whitespace-nowrap text-ink/70">{{ config('session.lifetime') }} minutes</td>
            </tr>
            <tr>
                <td class="py-3 pr-4 font-mono text-xs">XSRF-TOKEN</td>
                <td class="py-3 pr-4 text-ink/70">Strictly necessary</td>
                <td class="py-3 pr-4 text-ink/70">
                    Security. Proves a form was submitted from our site and not forged by another one.
                </td>
                <td class="py-3 whitespace-nowrap text-ink/70">{{ config('session.lifetime') }} minutes</td>
            </tr>
            @if (analytics_id())
                <tr>
                    <td class="py-3 pr-4 font-mono text-xs">_ga</td>
                    <td class="py-3 pr-4 text-ink/70">Analytics &mdash; needs consent</td>
                    <td class="py-3 pr-4 text-ink/70">
                        Google Analytics. Distinguishes one browser from another so a returning
                        reader is not counted twice. Set only if you accept.
                    </td>
                    <td class="py-3 whitespace-nowrap text-ink/70">2 years</td>
                </tr>
                <tr>
                    <td class="py-3 pr-4 font-mono text-xs">_ga_{{ Str::after(analytics_id(), 'G-') }}</td>
                    <td class="py-3 pr-4 text-ink/70">Analytics &mdash; needs consent</td>
                    <td class="py-3 pr-4 text-ink/70">
                        Google Analytics. Keeps the state of your visit. Set only if you accept.
                    </td>
                    <td class="py-3 whitespace-nowrap text-ink/70">2 years</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
