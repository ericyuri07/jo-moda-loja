<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    private const TOKEN_NAME = 'api-token';

    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['role'] ?? null, fn ($q, $v) => $q->where('role', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")
            ))
            ->orderBy('name')
            ->paginate();
    }

    public function create(array $data): User
    {
        /** @var User */
        return parent::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function createToken(User $user): string
    {
        return $user->createToken(self::TOKEN_NAME)->plainTextToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
