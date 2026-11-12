<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Vestidos' => ['Vestido Midi Floral', 'Vestido Longo Preto', 'Vestido Curto Listrado'],
            'Blusas' => ['Blusa de Linho', 'Cropped Canelado', 'Camisa Social Feminina'],
            'Calças' => ['Calça Jeans Reta', 'Calça Alfaiataria'],
            'Saias' => ['Saia Plissada', 'Saia Jeans'],
            'Acessórios' => ['Bolsa Tiracolo', 'Cinto Fino'],
            'Calçados' => ['Sandália Rasteira', 'Tênis Branco'],
        ];

        $sizes = ['P', 'M', 'G'];
        $colors = ['Preto', 'Branco', 'Rosa'];

        foreach ($catalog as $categoryName => $products) {
            $category = Category::where('slug', Str::slug($categoryName))->first();

            foreach ($products as $name) {
                $product = Product::factory()->create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'category_id' => $category->id,
                ]);

                // uma variante para cada combinação tamanho x cor
                foreach ($sizes as $size) {
                    foreach ($colors as $color) {
                        $product->variants()->create([
                            'size' => $size,
                            'color' => $color,
                            'stock' => fake()->numberBetween(0, 20),
                        ]);
                    }
                }

                // 2 imagens de apoio por produto
                $product->images()->createMany([
                    ['image_url' => "https://picsum.photos/seed/{$product->slug}-1/600/800"],
                    ['image_url' => "https://picsum.photos/seed/{$product->slug}-2/600/800"],
                ]);
            }
        }
    }
}