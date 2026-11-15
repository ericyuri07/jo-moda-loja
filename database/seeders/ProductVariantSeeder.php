<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Uma variante (com estoque) para cada combinação tamanho x cor dos produtos
 * de exemplo. Variantes que já existem são mantidas, com o estoque atual.
 */
class ProductVariantSeeder extends Seeder
{
    private const SIZES = ['P', 'M', 'G'];

    private const COLORS = ['Preto', 'Branco', 'Rosa'];

    public function run(): void
    {
        Product::whereIn('slug', ProductSeeder::catalogSlugs())->each(function (Product $product) {
            foreach (self::SIZES as $size) {
                foreach (self::COLORS as $color) {
                    $product->variants()->firstOrCreate(
                        ['size' => $size, 'color' => $color],
                        ['stock' => fake()->numberBetween(0, 20)]
                    );
                }
            }
        });
    }
}
