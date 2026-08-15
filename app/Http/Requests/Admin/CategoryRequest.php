<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:60'],
            'slug' => [
                'required', 'string', 'max:60', 'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($this->route('category')),
            ],
            // Templates drop this straight into a style attribute, so only a
            // plain 6-digit hex is accepted.
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = Str::slug((string) $this->input('slug'));

        $this->merge([
            'slug' => $slug !== '' ? $slug : Str::slug((string) $this->input('name')),
            'color' => Str::upper(trim((string) $this->input('color'))),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'color.regex' => 'Pick a colour, or enter a hex value like #5B2BEF.',
            'slug.unique' => 'Another category already uses that URL slug.',
        ];
    }
}
