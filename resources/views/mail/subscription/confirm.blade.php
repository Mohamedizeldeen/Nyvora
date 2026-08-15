{{--
    Double opt-in confirmation email, delivered by Mailgun.

    Kept plain and short on purpose: confirmation emails that look like
    marketing are the ones that get filtered.
--}}
<x-mail::message>
# One more step

Someone — hopefully you — asked to receive **The Daily Brief** from {{ $siteName }}:
one email each morning with the stories that actually matter.

Confirm the address to start receiving it.

<x-mail::button :url="$confirmUrl" color="primary">
Confirm my subscription
</x-mail::button>

If the button does not work, paste this link into your browser:

{{ $confirmUrl }}

If you did not sign up, ignore this email — nothing will be sent and the
address will not be added to any list.

Thanks,<br>
{{ $siteName }}
</x-mail::message>
