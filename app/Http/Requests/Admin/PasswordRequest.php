<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PasswordRequest extends FormRequest
{
    /**
     * Both forms live on the same page. Without separate bags, a wrong password
     * in one form would render its error under the other one too.
     */
    protected $errorBag = 'password';

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
            'current_password' => ['required', 'string', 'current_password'],
            'password' => [
                'required',
                'string',
                'confirmed',
                // 12 characters and not in a known breach list. This account can
                // publish to the whole site, so it is worth more than 8 characters.
                Password::min(12)->uncompromised(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'That is not your current password.',
            'current_password.required' => 'Enter your current password to confirm the change.',
            'password.confirmed' => 'The two new passwords do not match.',
        ];
    }
}
