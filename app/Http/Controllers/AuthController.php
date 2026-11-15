<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $service) {}

    public function register(RegisterRequest $request)
    {
        return response()->json($this->service->register($request->validated()), 201);
    }

    public function registerAdmin(RegisterAdminRequest $request)
    {
        return response()->json($this->service->registerAdmin($request->validated()), 201);
    }

    public function login(LoginRequest $request)
    {
        return response()->json(
            $this->service->login($request->validated('email'), $request->validated('password'))
        );
    }

    public function logout(Request $request)
    {
        $this->service->logout($request->user());

        return response()->json(['message' => 'Logout realizado.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
