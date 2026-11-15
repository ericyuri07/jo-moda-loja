<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{role?: string, search?: string}  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    /** Mesmo create() da base, mas com retorno tipado como User. */
    public function create(array $data): User;

    public function findByEmail(string $email): ?User;

    /** Gera um token de acesso (Sanctum) e devolve o texto puro. */
    public function createToken(User $user): string;

    public function revokeCurrentToken(User $user): void;

    public function revokeAllTokens(User $user): void;
}
