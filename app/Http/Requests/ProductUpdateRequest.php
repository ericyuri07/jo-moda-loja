<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($this->route('product')),
            ],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'promotional_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'category_id' => ['sometimes', 'required', 'uuid', 'exists:categories,id'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'featured' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'name.max' => 'O nome do produto deve ter no máximo :max caracteres.',

            'slug.required' => 'O slug do produto é obrigatório.',
            'slug.max' => 'O slug deve ter no máximo :max caracteres.',
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens (ex: vestido-midi-floral).',
            'slug.unique' => 'Já existe um produto com este slug.',

            'price.required' => 'O preço do produto é obrigatório.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.decimal' => 'O preço deve ter no máximo :decimal casas decimais.',
            'price.min' => 'O preço não pode ser negativo.',
            'price.max' => 'O preço informado é maior que o permitido.',

            'promotional_price.numeric' => 'O preço promocional deve ser um número.',
            'promotional_price.decimal' => 'O preço promocional deve ter no máximo :decimal casas decimais.',
            'promotional_price.min' => 'O preço promocional não pode ser negativo.',
            'promotional_price.max' => 'O preço promocional informado é maior que o permitido.',

            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.uuid' => 'A categoria informada é inválida.',
            'category_id.exists' => 'A categoria informada não existe.',

            'image_url.url' => 'A URL da imagem é inválida.',
            'image_url.max' => 'A URL da imagem deve ter no máximo :max caracteres.',

            'featured.boolean' => 'O campo destaque deve ser verdadeiro ou falso.',
            'active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }
}