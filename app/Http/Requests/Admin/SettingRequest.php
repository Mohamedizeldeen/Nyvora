<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_tagline' => ['required', 'string', 'max:120'],
            'footer_description' => ['required', 'string', 'max:400'],
            'articles_per_page' => ['required', 'integer', 'min:3', 'max:50'],

            'promo_enabled' => ['nullable', 'boolean'],
            'promo_eyebrow' => ['nullable', 'string', 'max:40'],
            'promo_text' => ['nullable', 'string', 'max:200', 'required_if:promo_enabled,1'],
            'promo_cta_label' => ['nullable', 'string', 'max:30'],
            'promo_cta_url' => ['nullable', 'string', 'max:2048', 'url', 'required_with:promo_cta_label'],
            'promo_tone' => ['required', 'in:accent,brand,ink'],

            // Google's publisher id, e.g. ca-pub-1234567890123456.
            'adsense_client_id' => ['nullable', 'string', 'max:40', 'regex:/^ca-pub-\d{10,20}$/'],
            // Already reduced to a bare id in prepareForValidation().
            'adsense_slot_sidebar' => ['nullable', 'string', 'regex:/^\d{6,20}$/'],
            'adsense_slot_leaderboard' => ['nullable', 'string', 'regex:/^\d{6,20}$/'],
            'adsense_slot_in_feed' => ['nullable', 'string', 'regex:/^\d{6,20}$/'],
            'adsense_auto_ads' => ['nullable', 'boolean'],
            'analytics_measurement_id' => ['nullable', 'string', 'max:20', 'regex:/^G-[A-Z0-9]{6,14}$/'],

            'search_indexable' => ['nullable', 'boolean'],
            'newsletter_enabled' => ['nullable', 'boolean'],
            'comments_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Checkboxes are absent from the payload when unticked.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'promo_enabled' => $this->boolean('promo_enabled') ? '1' : '0',
            'search_indexable' => $this->boolean('search_indexable') ? '1' : '0',
            'newsletter_enabled' => $this->boolean('newsletter_enabled') ? '1' : '0',
            'comments_enabled' => $this->boolean('comments_enabled') ? '1' : '0',
            'adsense_auto_ads' => $this->boolean('adsense_auto_ads') ? '1' : '0',
            // Accept a pasted <ins> snippet and keep only the slot id.
            'adsense_slot_sidebar' => Setting::extractAdSlotId($this->input('adsense_slot_sidebar')),
            'adsense_slot_leaderboard' => Setting::extractAdSlotId($this->input('adsense_slot_leaderboard')),
            'adsense_slot_in_feed' => Setting::extractAdSlotId($this->input('adsense_slot_in_feed')),
            // A publisher id is often pasted with stray whitespace.
            'adsense_client_id' => trim((string) $this->input('adsense_client_id')),
            'analytics_measurement_id' => mb_strtoupper(trim((string) $this->input('analytics_measurement_id'))),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'promo_text.required_if' => 'Add the announcement text, or switch the strip off.',
            'promo_cta_url.required_with' => 'A button label needs a link to point at.',
            'adsense_client_id.regex' => 'Publisher IDs look like ca-pub-1234567890123456.',
            'analytics_measurement_id.regex' => 'Measurement IDs look like G-L86F9KBYKG.',
            'adsense_slot_sidebar.regex' => 'Paste the ad unit code from AdSense, or its slot ID.',
            'adsense_slot_leaderboard.regex' => 'Paste the ad unit code from AdSense, or its slot ID.',
            'adsense_slot_in_feed.regex' => 'Paste the ad unit code from AdSense, or its slot ID.',
        ];
    }
}
