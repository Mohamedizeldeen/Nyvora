<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PasswordRequest;
use App\Http\Requests\Admin\ProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The signed-in administrator's own account.
 *
 * Both changes require the current password: a session left open on an
 * unattended machine should not be enough to take the account over by
 * swapping the email address or the password.
 */
class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.account', ['user' => $request->user()]);
    }

    public function updateProfile(ProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only(['name', 'email']));

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Your details have been saved.');
    }

    public function updatePassword(PasswordRequest $request): RedirectResponse
    {
        $password = $request->validated('password');

        $request->user()->update(['password' => $password]);

        // Sign out every other session. Whoever prompted this change — a shared
        // laptop, a leaked password — should not still be signed in elsewhere.
        Auth::logoutOtherDevices($password);

        // New session id for this one, so a stolen pre-change cookie is useless.
        $request->session()->regenerate();

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Password changed. Any other device signed in as you has been signed out.');
    }
}
