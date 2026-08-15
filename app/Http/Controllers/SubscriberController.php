<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeRequest;
use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class SubscriberController extends Controller
{
    /**
     * Handle a signup from the sidebar form.
     *
     * Double opt-in: the row is created as pending and Mailgun sends a
     * confirmation link. Nothing is added to the mailing list until the
     * reader clicks it.
     */
    public function store(SubscribeRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        // firstOrCreate keeps a repeat signup idempotent: the reader gets the
        // same message instead of a confusing "email already taken" error.
        $subscriber = Subscriber::query()->firstOrCreate(
            ['email' => $email],
            [
                'token' => Subscriber::newToken(),
                'subscribed_at' => now(),
            ],
        );

        // Already on the list — do not send another confirmation email.
        if ($subscriber->isConfirmed()) {
            return $this->done('You are already subscribed — nothing more to do.');
        }

        // Someone re-subscribing after opting out starts the flow from scratch.
        // confirmed_at is cleared as well: without that they would land back on
        // the list without clicking anything, which is exactly the consent we
        // promised not to assume.
        if ($subscriber->hasUnsubscribed()) {
            $subscriber->forceFill([
                'unsubscribed_at' => null,
                'confirmed_at' => null,
                'subscribed_at' => now(),
                // A new token retires the links from their previous subscription.
                'token' => Subscriber::newToken(),
            ])->save();
        }

        // Queued, so a slow Mailgun never holds up this request.
        Mail::to($subscriber->email)->queue(new ConfirmSubscription($subscriber));

        return $this->done('Almost there — check your inbox for the confirmation link.');
    }

    /**
     * Redirect back to the form with a message.
     */
    private function done(string $message): RedirectResponse
    {
        return back()
            ->with('subscribed', $message)
            // Anchor the redirect at the form so the reader lands on the message.
            ->withFragment('newsletter');
    }
}
