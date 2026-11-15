<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthService
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * Cadastro público: o usuário sempre nasce como cliente.
     *
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        return $this->createUserWithToken($data, User::ROLE_CUSTOMER);
    }

    /**
     * Cadastro de administrador pelo app (botão "Entrar como ADM").
     * Exige o código ADMIN_REGISTER_CODE configurado no .env.
     *
     * @return array{user: User, token: string}
     */
    public function registerAdmin(array $data): array
    {
        $expectedCode = (string) config('auth.admin_register_code');

        if ($expectedCode === '') {
            throw new AccessDeniedHttpException('O cadastro de administradores está desativado.');
        }

        if (! hash_equals($expectedCode, $data['admin_code'])) {
            throw ValidationException::withMessages([
                'admin_code' => ['Código de acesso de administrador inválido.'],
            ]);
        }

        return $this->createUserWithToken($data, User::ROLE_ADMIN);
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['E-mail ou senha inválidos.'],
            ]);
        }

        return ['user' => $user, 'token' => $this->users->createToken($user)];
    }

    public function logout(User $user): void
    {
        $this->users->revokeCurrentToken($user);
    }

    /**
     * @return array{user: User, token: string}
     */
    private function createUserWithToken(array $data, string $role): array
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
        ]);

        return ['user' => $user, 'token' => $this->users->createToken($user)];
    }
}
