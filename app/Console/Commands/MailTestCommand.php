<?php

namespace App\Console\Commands;

use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Verifies the Mailgun setup end to end without waiting for a real signup.
 *
 * Sends synchronously (not via the queue) so any credential or domain problem
 * surfaces here in the terminal rather than in a failed job.
 */
#[Signature('nyvora:mail-test {email : Where to send the test message}')]
#[Description('Send a test newsletter confirmation email through the configured mailer')]
class MailTestCommand extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $this->components->info('Mailer: '.config('mail.default'));

        if (config('mail.default') === 'mailgun') {
            $domain = config('services.mailgun.domain');
            $secret = config('services.mailgun.secret');

            $this->components->twoColumnDetail('Mailgun domain', $domain ?: '<fg=red>not set</>');
            $this->components->twoColumnDetail('Mailgun endpoint', (string) config('services.mailgun.endpoint'));
            $this->components->twoColumnDetail('API key', $secret ? 'set ('.strlen((string) $secret).' chars)' : '<fg=red>not set</>');
            $this->components->twoColumnDetail('Mailing list', config('services.mailgun.list_address') ?: 'not set (sync disabled)');
            $this->components->twoColumnDetail('From address', (string) config('mail.from.address'));

            if (blank($domain)) {
                $this->components->error('MAILGUN_DOMAIN is empty — set it in .env to your verified Mailgun sending domain.');

                return self::FAILURE;
            }

            if (blank($secret)) {
                $this->components->error('MAILGUN_API_KEY is empty.');

                return self::FAILURE;
            }
        }

        // An unsaved model is enough to render the template and its links.
        $subscriber = new Subscriber([
            'email' => $email,
            'token' => Subscriber::newToken(),
        ]);

        try {
            Mail::to($email)->send(new ConfirmSubscription($subscriber));
        } catch (Throwable $exception) {
            $this->components->error('Sending failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Test email accepted for delivery to {$email}.");
        $this->components->warn('Note: the confirm link in this test points at an unsaved subscriber, so it will 404.');

        return self::SUCCESS;
    }
}
