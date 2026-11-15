<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Escolha uma foto.',
            'image.image' => 'O arquivo precisa ser uma imagem.',
            'image.mimes' => 'Use uma foto JPG, PNG ou WEBP.',
            'image.max' => 'A foto deve ter no máximo 2 MB.',
            'image.uploaded' => 'Não foi possível enviar a foto (máximo 2 MB).',
        ];
    }
}
