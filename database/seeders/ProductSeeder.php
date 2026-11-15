<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\Concerns\GeneratesProductPhotos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Catálogo de exemplo da loja (produtos e foto principal).
 *
 * Pode rodar mais de uma vez: produtos que já existem (mesmo slug) só têm a
 * foto principal atualizada. Variantes e fotos extras ficam nos seeders
 * ProductVariantSeeder e ProductImageSeeder.
 */
class ProductSeeder extends Seeder
{
    use GeneratesProductPhotos;

    public const CATALOG = [
        'Vestidos' => ['Vestido Midi Floral', 'Vestido Longo Preto', 'Vestido Curto Listrado'],
        'Blusas' => ['Blusa de Linho', 'Cropped Canelado', 'Camisa Social Feminina'],
        'Calças' => ['Calça Jeans Reta', 'Calça Alfaiataria'],
        'Saias' => ['Saia Plissada', 'Saia Jeans'],
        'Acessórios' => ['Bolsa Tiracolo', 'Cinto Fino'],
        'Calçados' => ['Sandália Rasteira', 'Tênis Branco'],
    ];

    /** Slugs dos produtos de exemplo (usados pelos seeders de variantes e fotos). */
    public static function catalogSlugs(): array
    {
        return array_map(
            fn (string $name) => Str::slug($name),
            array_merge(...array_values(self::CATALOG))
        );
    }

    public function run(): void
    {
        foreach (self::CATALOG as $categoryName => $products) {
            $category = Category::where('slug', Str::slug($categoryName))->firstOrFail();

            foreach ($products as $name) {
                $slug = Str::slug($name);

                $product = Product::firstOrCreate(
                    ['slug' => $slug],
                    Product::factory()->make([
                        'name' => $name,
                        'slug' => $slug,
                        'category_id' => $category->id,
                    ])->toArray()
                );

                $product->update(['image_url' => $this->productPhoto($name)]);
            }
        }
    }
}
