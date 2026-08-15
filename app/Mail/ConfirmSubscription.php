<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * The double opt-in email: "click here to confirm your subscription".
 *
 * Queued, so a slow or unavailable Mailgun never holds up the visitor's
 * request. That means a queue worker has to be running in production:
 *   php artisan queue:work
 */
class ConfirmSubscription extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm your '.config('app.name').' subscription',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.subscription.confirm',
            with: [
                'confirmUrl' => route('newsletter.confirm', $this->subscriber),
                'siteName' => config('app.name'),
            ],
        );
    }

    /**
     * Headers that let mail clients offer a one-click unsubscribe, which is
     * what Gmail and Yahoo require of bulk senders.
     *
     * @return array<string, string>
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.route('newsletter.unsubscribe', $this->subscriber).'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
