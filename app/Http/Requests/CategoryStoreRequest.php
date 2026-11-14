<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Gera o slug a partir do nome quando ele não é enviado,
     * para que a regra "unique" consiga validá-lo (422 em vez de 500).
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:categories,slug',
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