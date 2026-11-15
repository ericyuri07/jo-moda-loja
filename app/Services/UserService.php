<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private UserRepositoryInterface $repository) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function find(User $user): User
    {
        return $this->repository->loadRelations($user);
    }

    public function create(array $data): User
    {
        return $this->repository->create($data);
    }

    /**
     * Regra de negócio: o admin não pode rebaixar o próprio perfil.
     */
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

        return $this->repository->update($user, $data);
    }

    /**
     * Regra de negócio: o admin não pode excluir a si mesmo.
     */
    public function delete(User $user, User $authUser): void
    {
        if ($user->is($authUser)) {
            throw ValidationException::withMessages([
                'user' => ['Você não pode excluir o seu próprio usuário.'],
            ]);
        }

        $this->repository->revokeAllTokens($user);
        $this->repository->delete($user);
    }
}
