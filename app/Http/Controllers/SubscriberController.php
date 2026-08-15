<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeRequest;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;

class SubscriberController extends Controller
{
    /**
     * Record a newsletter signup from the sidebar form.
     */
    public function store(SubscribeRequest $request): RedirectResponse
    {
        // firstOrCreate keeps a repeat signup idempotent: the reader gets the
        // same confirmation instead of a confusing "email already taken" error.
        Subscriber::query()->firstOrCreate(
            ['email' => $request->validated('email')],
            ['subscribed_at' => now()],
        );

        return back()
            ->with('subscribed', 'Thanks — check your inbox to confirm your subscription.')
            // Anchor the redirect at the form so the reader lands on the message.
            ->withFragment('newsletter');
    }
}
