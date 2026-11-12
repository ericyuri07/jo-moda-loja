<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jomodas.com'],
            [
                'name' => 'Administradora Jô Modas',
                'password' => 'admin123',
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@jomodas.com'],
            [
                'name' => 'Cliente Teste',
                'password' => 'cliente123',
                'role' => User::ROLE_CUSTOMER,
                'address' => 'Rua das Flores, 123 - Guarapuava/PR',
            ]
        );
    }
}