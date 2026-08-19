<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    /**
     * Store a message sent from any of the site's forms.
     *
     * Nothing is emailed: the publication has no mailbox, so messages are kept
     * here and read in the newsroom dashboard.
     *
     * The forms live in a modal, so the JavaScript path posts with fetch() and
     * gets JSON back to swap in the thank-you without a reload. Without
     * JavaScript the same route redirects back with a flash message, and the
     * modal reopens showing it — so the form works either way.
     */
    public function store(ContactRequest $request): JsonResponse|RedirectResponse
    {
        $message = ContactMessage::query()->create($request->safe()->only([
            'topic', 'name', 'email', 'body',
        ]));

        $thanks = 'Thank you — your message has reached the newsroom. We read everything, and we reply to most messages within two business days.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $thanks,
                'topic' => $message->topicLabel(),
            ]);
        }

        return back()
            ->with('contact_sent', $thanks)
            ->with('contact_topic', $message->topic);
    }
}
