<?php

namespace App\Http\Requests\Admin;

use App\Models\Author;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'string', 'max:140', 'alpha_dash',
                Rule::unique('authors', 'slug')->ignore($this->route('author')),
            ],
            'bio' => ['nullable', 'string', 'max:600'],

            // The avatar can be an upload or a pasted URL.
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'avatar_url' => ['nullable', 'string', 'max:2048', 'url'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Fill in the slug from the name when the admin leaves it blank.
     */
    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug'))) {
            $this->merge([
                'slug' => Author::uniqueSlug((string) $this->input('name'), $this->route('author')),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.max' => 'The avatar must be 2 MB or smaller.',
            'slug.unique' => 'Another author already uses that URL slug.',
        ];
    }
}
