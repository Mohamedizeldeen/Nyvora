<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Services\MailgunList;
use Illuminate\Contracts\View\View;

class NewsletterController extends Controller
{
    public function __construct(private readonly MailgunList $mailgunList) {}

    /**
     * The reader clicked the confirmation link in the double opt-in email.
     *
     * The {subscriber} parameter is matched on the unguessable token, so an
     * unknown or tampered link 404s before reaching this method.
     */
    public function confirm(Subscriber $subscriber): View
    {
        $alreadyDone = $subscriber->isConfirmed();

        $subscriber->confirm();

        // Push the address to the Mailgun mailing list, if one is configured.
        // Never blocks the reader: failures are logged inside the service.
        if (! $alreadyDone) {
            $this->mailgunList->add($subscriber);
        }

        return view('newsletter.confirmed', [
            'subscriber' => $subscriber,
            'alreadyDone' => $alreadyDone,
        ]);
    }

    /**
     * One-click unsubscribe, also used by the List-Unsubscribe mail header.
     */
    public function unsubscribe(Subscriber $subscriber): View
    {
        $alreadyDone = $subscriber->hasUnsubscribed();

        $subscriber->unsubscribe();

        if (! $alreadyDone) {
            $this->mailgunList->remove($subscriber);
        }

        return view('newsletter.unsubscribed', [
            'subscriber' => $subscriber,
            'alreadyDone' => $alreadyDone,
        ]);
    }
}
