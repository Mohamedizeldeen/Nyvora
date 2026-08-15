<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    /**
     * The admin middleware already gated this route.
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                // On update, the article keeps its own slug.
                Rule::unique('articles', 'slug')->ignore($this->route('article')),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'author_id' => ['required', 'integer', Rule::exists('authors', 'id')],

            // The thumbnail can be an upload or a pasted URL — either, or neither.
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'thumbnail_url' => ['nullable', 'string', 'max:2048', 'url'],
            'remove_thumbnail' => ['nullable', 'boolean'],

            'is_featured' => ['nullable', 'boolean'],
            'views_count' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Fill in the slug from the title when the admin leaves it blank.
     */
    protected function prepareForValidation(): void
    {
        $slug = Str::slug((string) $this->input('slug'));

        if ($slug === '') {
            $slug = Str::slug((string) $this->input('title'));
        }

        $this->merge(['slug' => $slug]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'Another article already uses that URL slug.',
            'thumbnail.max' => 'The image must be 4 MB or smaller.',
            'thumbnail_url.url' => 'Enter a full image URL, starting with https://',
        ];
    }
}
