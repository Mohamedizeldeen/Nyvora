<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    /**
     * Site-wide settings: tagline, promo strip, feed size, AdSense id.
     */
    public function edit(): View
    {
        return view('admin.settings', [
            'settings' => Setting::all_settings(),
        ]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        // Only the keys the form knows about are written, so a crafted payload
        // cannot invent new settings.
        Setting::put($request->safe()->only(array_keys(Setting::DEFAULTS)));

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Settings saved.');
    }
}
