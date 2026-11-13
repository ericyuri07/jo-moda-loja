<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantUpdateRequest extends FormRequest
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
        $variant = $this->route('product_variant');

        return [
            // product_id não é editável: variante não muda de produto
            'size' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('product_variants')
                    ->ignore($variant)
                    ->where(
                        fn($query) => $query
                            ->where('product_id', $variant->product_id)
                            ->where('color', $this->input('color', $variant->color))
                    ),
            ],
            'color' => ['sometimes', 'required', 'string', 'max:50'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    public function messages(): array
    {
        return [
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