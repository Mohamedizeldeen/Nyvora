<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Services\MailgunList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function __construct(private readonly MailgunList $mailgunList) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        return view('admin.subscribers.index', [
            'subscribers' => Subscriber::query()
                ->when($search !== '', fn ($query) => $query->where('email', 'like', '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%'))
                ->when($status === 'confirmed', fn ($query) => $query->active())
                ->when($status === 'pending', fn ($query) => $query->pending())
                ->when($status === 'unsubscribed', fn ($query) => $query->whereNotNull('unsubscribed_at'))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'search' => $search,
            'status' => $status,
            'counts' => [
                // Only confirmed addresses are a real mailing list.
                'confirmed' => Subscriber::query()->active()->count(),
                'pending' => Subscriber::query()->pending()->count(),
                'unsubscribed' => Subscriber::query()->whereNotNull('unsubscribed_at')->count(),
            ],
            'listSyncEnabled' => $this->mailgunList->enabled(),
        ]);
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        // Take them off the Mailgun list too, or they would keep receiving mail
        // sent from the Mailgun dashboard.
        $this->mailgunList->remove($subscriber);

        $subscriber->delete();

        return back()->with('status', 'Subscriber removed.');
    }

    /**
     * Stream the confirmed list as CSV, ready to import elsewhere.
     *
     * Streaming keeps memory flat no matter how long the list grows.
     */
    public function export(Request $request): StreamedResponse
    {
        // Default to the confirmed list — exporting unconfirmed addresses and
        // mailing them is exactly what double opt-in exists to prevent.
        $includeAll = $request->boolean('all');
        $filename = 'subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($includeAll) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'status', 'subscribed_at', 'confirmed_at']);

            Subscriber::query()
                ->unless($includeAll, fn ($query) => $query->active())
                ->orderBy('id')
                ->chunk(500, function ($subscribers) use ($handle) {
                    foreach ($subscribers as $subscriber) {
                        fputcsv($handle, [
                            $subscriber->email,
                            $subscriber->status(),
                            $subscriber->subscribed_at?->toDateTimeString(),
                            $subscriber->confirmed_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
