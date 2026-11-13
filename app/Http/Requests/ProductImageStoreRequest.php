<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductImageStoreRequest extends FormRequest
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
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'image_url' => ['required', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'O produto é obrigatório.',
            'product_id.uuid' => 'O produto informado é inválido.',
            'product_id.exists' => 'O produto informado não existe.',

            'image_url.required' => 'A URL da imagem é obrigatória.',
            'image_url.url' => 'A URL da imagem é inválida.',
            'image_url.max' => 'A URL da imagem deve ter no máximo :max caracteres.',
        ];
    }
}