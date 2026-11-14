<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }

    /**
     * Cadastro de administrador pelo app (botão "Entrar como ADM" do login).
     * Mesmos campos do cadastro normal, mas o usuário já nasce como admin.
     */
    public function registerAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'admin_code' => 'required|string',
        ], [
            'admin_code.required' => 'O código de acesso de administrador é obrigatório.',
        ]);

        $expectedCode = (string) config('auth.admin_register_code');

        if ($expectedCode === '') {
            abort(403, 'O cadastro de administradores está desativado.');
        }

        if (!hash_equals($expectedCode, $data['admin_code'])) {
            throw ValidationException::withMessages([
                'admin_code' => ['Código de acesso de administrador inválido.'],
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_ADMIN,
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['E-mail ou senha inválidos.'],
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}