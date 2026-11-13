<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductImageUpdateRequest extends FormRequest
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
            // product_id não é editável: a imagem não muda de produto
            'image_url' => ['required', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'image_url.required' => 'A URL da imagem é obrigatória.',
            'image_url.url' => 'A URL da imagem é inválida.',
            'image_url.max' => 'A URL da imagem deve ter no máximo :max caracteres.',
        ];
    }
}