<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    /**
     * The admin middleware already gated this route, and the rules below only
     * ever touch the signed-in user's own row.
     */
    /**
     * Both forms live on the same page. Without separate bags, a wrong password
     * in one form would render its error under the other one too.
     */
    protected $errorBag = 'profile';

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
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required', 'string', 'email:rfc', 'max:254',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            // Changing the address that can reset the account is a sensitive
            // action, so it has to be confirmed with the current password.
            // Without this, anyone who finds an unlocked laptop owns the site.
            'current_password' => ['required', 'string', 'current_password'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'That is not your current password.',
            'current_password.required' => 'Enter your current password to confirm the change.',
            'email.unique' => 'Another account already uses that email address.',
        ];
    }
}
