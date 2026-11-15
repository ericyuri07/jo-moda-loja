<?php

namespace App\Http\Requests;

/**
 * Mesmos campos do cadastro normal, mais o código de administrador.
 */
class RegisterAdminRequest extends RegisterRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'admin_code' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'admin_code.required' => 'O código de acesso de administrador é obrigatório.',
        ];
    }
}
