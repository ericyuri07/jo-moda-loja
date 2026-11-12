<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,   // 1º: produtos dependem das categorias
            UserSeeder::class,
            ProductSeeder::class,    // depende de Category
        ]);
    }
}