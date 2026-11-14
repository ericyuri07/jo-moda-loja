<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && !$this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($this->route('category')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.max' => 'O nome da categoria deve ter no máximo :max caracteres.',
            'slug.required' => 'O slug da categoria é obrigatório.',
            'slug.max' => 'O slug deve ter no máximo :max caracteres.',
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens (ex: vestidos-de-festa).',
            'slug.unique' => 'Já existe uma categoria com este slug.',
        ];
    }
}