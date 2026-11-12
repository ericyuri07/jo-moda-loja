<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'size' => fake()->randomElement(['P', 'M', 'G', 'GG']),
            'color' => fake()->randomElement(['Preto', 'Branco', 'Azul', 'Vermelho', 'Rosa']),
            'stock' => fake()->numberBetween(0, 30),
        ];
    }
}
