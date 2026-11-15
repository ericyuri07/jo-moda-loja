<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(Request $request)
    {
        return response()->json($this->service->list($request->only(['role', 'search'])));
    }

    public function store(UserStoreRequest $request)
    {
        return response()->json($this->service->create($request->validated()), 201);
    }

    public function show(User $user)
    {
        return response()->json($this->service->find($user));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        return response()->json(
            $this->service->update($user, $request->validated(), $request->user())
        );
    }

    public function destroy(Request $request, User $user)
    {
        $this->service->delete($user, $request->user());

        return response()->json(['message' => 'Usuário excluído']);
    }
}
