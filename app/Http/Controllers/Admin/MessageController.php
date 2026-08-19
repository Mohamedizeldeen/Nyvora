<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The newsroom inbox.
 *
 * Messages are not emailed anywhere, so this is the only place they are read.
 */
class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $topic = (string) $request->query('topic', '');
        $unreadOnly = $request->boolean('unread');

        return view('admin.messages.index', [
            'messages' => ContactMessage::query()
                ->when($topic !== '', fn ($query) => $query->where('topic', $topic))
                ->when($unreadOnly, fn ($query) => $query->unread())
                ->latestFirst()
                ->paginate(20)
                ->withQueryString(),
            'topic' => $topic,
            'unreadOnly' => $unreadOnly,
            'unreadCount' => ContactMessage::query()->unread()->count(),
            'total' => ContactMessage::query()->count(),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        // Opening it is what marks it read.
        $message->markRead();

        return view('admin.messages.show', ['message' => $message]);
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()
            ->route('admin.messages.index')
            ->with('status', 'Message deleted.');
    }
}
