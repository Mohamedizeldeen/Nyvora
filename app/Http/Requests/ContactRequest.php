<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    /**
     * The forms are open to every visitor.
     */
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
            'topic' => ['required', 'string', Rule::in(array_keys(ContactMessage::TOPICS))],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],

            // Honeypot: a field hidden from people but filled in by naive bots.
            // Must be absent or empty. No captcha, no third party, no cookies.
            'website' => ['prohibited'],
        ];
    }

    /**
     * Normalise before validating.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'body' => trim((string) $this->input('body')),
        ]);

        // An empty honeypot should look absent rather than fail "prohibited".
        if ($this->input('website') === '') {
            $this->request->remove('website');
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'topic.required' => 'Choose what your message is about.',
            'topic.in' => 'Choose what your message is about.',
            'name.required' => 'Please tell us your name.',
            'email.required' => 'We need an email address to reply to.',
            'email.email' => 'That does not look like a valid email address.',
            'body.required' => 'Please write your message.',
            'body.min' => 'Please add a little more detail.',
            // If a bot trips the honeypot, say nothing useful.
            'website.prohibited' => 'That message could not be sent.',
        ];
    }
}
