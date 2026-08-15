<?php

namespace App\Http\Requests\Admin;

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

            'search_indexable' => ['nullable', 'boolean'],
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
        ];
    }
}
