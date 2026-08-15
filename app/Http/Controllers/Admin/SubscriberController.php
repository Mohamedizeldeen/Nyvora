<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('admin.subscribers.index', [
            'subscribers' => Subscriber::query()
                ->when($search !== '', fn ($query) => $query->where('email', 'like', '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%'))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'search' => $search,
            'total' => Subscriber::query()->count(),
        ]);
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('status', 'Subscriber removed.');
    }

    /**
     * Stream the list as CSV so it can be imported into an email platform.
     *
     * Streaming keeps memory flat no matter how long the list grows.
     */
    public function export(): StreamedResponse
    {
        $filename = 'subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'subscribed_at']);

            Subscriber::query()
                ->orderBy('id')
                ->chunk(500, function ($subscribers) use ($handle) {
                    foreach ($subscribers as $subscriber) {
                        fputcsv($handle, [
                            $subscriber->email,
                            $subscriber->subscribed_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
