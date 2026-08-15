<?php

namespace App\Services;

use App\Models\Subscriber;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Keeps a Mailgun mailing list in step with our `subscribers` table.
 *
 * This is optional: with no MAILGUN_LIST_ADDRESS configured every method is a
 * no-op and the site works exactly as before, storing subscribers locally only.
 * When it is configured, confirmed addresses are pushed to the list so a
 * newsletter can be sent straight from the Mailgun dashboard.
 *
 * Failures are logged, never thrown — a Mailgun outage must not break a
 * reader's confirmation click.
 */
class MailgunList
{
    public function __construct(
        private readonly ?string $listAddress = null,
        private readonly ?string $apiKey = null,
        private readonly string $endpoint = 'api.mailgun.net',
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            listAddress: config('services.mailgun.list_address'),
            apiKey: config('services.mailgun.secret'),
            endpoint: config('services.mailgun.endpoint', 'api.mailgun.net'),
        );
    }

    /**
     * Is list syncing switched on?
     */
    public function enabled(): bool
    {
        return filled($this->listAddress) && filled($this->apiKey);
    }

    /**
     * Add (or re-activate) a confirmed subscriber on the Mailgun list.
     */
    public function add(Subscriber $subscriber): bool
    {
        return $this->call(
            fn () => $this->request()->asForm()->post($this->membersUrl(), [
                'address' => $subscriber->email,
                'subscribed' => 'yes',
                // upsert lets a returning subscriber be re-added without a 400.
                'upsert' => 'yes',
            ]),
            'add',
            $subscriber->email,
        );
    }

    /**
     * Mark an address as unsubscribed on the Mailgun list.
     */
    public function remove(Subscriber $subscriber): bool
    {
        return $this->call(
            fn () => $this->request()->delete($this->membersUrl().'/'.urlencode($subscriber->email)),
            'remove',
            $subscriber->email,
        );
    }

    /**
     * Run a Mailgun call, swallowing and logging anything that goes wrong.
     *
     * @param  callable(): Response  $callback
     */
    private function call(callable $callback, string $action, string $email): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        try {
            $response = $callback();

            if ($response->failed()) {
                Log::warning('Mailgun list sync failed.', [
                    'action' => $action,
                    'email' => $email,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::warning('Mailgun list sync threw an exception.', [
                'action' => $action,
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function request()
    {
        return Http::withBasicAuth('api', (string) $this->apiKey)
            ->timeout(10)
            ->retry(2, 250);
    }

    private function membersUrl(): string
    {
        return "https://{$this->endpoint}/v3/lists/{$this->listAddress}/members";
    }
}
