<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantStoreRequest extends FormRequest
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
            'size' => [
                'required',
                'string',
                'max:20',
                // espelha o unique(product_id, size, color) da migration
                Rule::unique('product_variants')->where(
                    fn($query) => $query
                        ->where('product_id', $this->input('product_id'))
                        ->where('color', $this->input('color'))
                ),
            ],
            'color' => ['required', 'string', 'max:50'],
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'O produto é obrigatório.',
            'product_id.uuid' => 'O produto informado é inválido.',
            'product_id.exists' => 'O produto informado não existe.',

            'size.required' => 'O tamanho é obrigatório.',
            'size.max' => 'O tamanho deve ter no máximo :max caracteres.',
            'size.unique' => 'Já existe uma variante com este tamanho e cor para o produto.',

            'color.required' => 'A cor é obrigatória.',
            'color.max' => 'A cor deve ter no máximo :max caracteres.',

            'stock.required' => 'A quantidade em estoque é obrigatória.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
            'stock.max' => 'O estoque informado é maior que o permitido.',
        ];
    }
}