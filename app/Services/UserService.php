<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function list(array $filters = [])
    {
        return User::query()
            ->when($filters['role'] ?? null, fn($q, $v) => $q->where('role', $v))
            ->when($filters['search'] ?? null, fn($q, $v) => $q->where(
                fn($q) => $q->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")
            ))
            ->orderBy('name')
            ->paginate();
    }

    public function find(User $user): User
    {
        return $user;
    }

    public function create(array $data): User
    {
        return User::create($data)->refresh();
    }

    public function update(User $user, array $data, User $authUser): User
    {
        if (
            $user->is($authUser)
            && isset($data['role'])
            && $data['role'] !== User::ROLE_ADMIN
        ) {
            throw ValidationException::withMessages([
                'role' => ['Você não pode remover o seu próprio acesso de administrador.'],
            ]);
        }

        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user, User $authUser): void
    {
        if ($user->is($authUser)) {
            throw ValidationException::withMessages([
                'user' => ['Você não pode excluir o seu próprio usuário.'],
            ]);
        }

        $user->tokens()->delete();
        $user->delete();
    }
}
