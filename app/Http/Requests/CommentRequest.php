<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Anyone reading the article may comment. Whether the comment is ever
     * shown is a separate decision, made by a human in the dashboard.
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
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'body' => ['required', 'string', 'min:3', 'max:2000'],

            // Honeypot — hidden from people, filled in by naive bots.
            'website' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'body' => trim((string) $this->input('body')),
        ]);

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
            'name.required' => 'Please add the name you want shown with your comment.',
            'name.max' => 'That name is too long.',
            'body.required' => 'Please write your comment.',
            'body.min' => 'That comment is a little too short.',
            'body.max' => 'Comments are limited to 2,000 characters.',
            'website.prohibited' => 'That comment could not be posted.',
        ];
    }
}
