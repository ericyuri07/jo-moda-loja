<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Catálogo de exemplo da loja.
 *
 * Pode rodar mais de uma vez: produtos que já existem (mesmo slug) só têm as
 * fotos atualizadas; os que faltam são criados com variações e estoque.
 *
 * As fotos são fictícias (placehold.co): cartões nas cores da marca com o nome
 * do produto. O serviço devolve PNG e libera CORS, então funciona também no
 * Flutter Web.
 */
class ProductSeeder extends Seeder
{
    /** Fundo / texto (hex sem #) das fotos: principal, detalhe e look. */
    private const PHOTO_STYLES = [
        ['1E1B1B', 'D4A94A'],
        ['F1D27A', '1E1B1B'],
        ['4F4D4C', 'FFFFFF'],
    ];

    private const PHOTO_LABELS = ['', ' · detalhe', ' · look'];

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

                $product->update(['image_url' => $this->photo($name, 0)]);

                // uma variante para cada combinação tamanho x cor
                foreach ($sizes as $size) {
                    foreach ($colors as $color) {
                        $product->variants()->firstOrCreate(
                            ['size' => $size, 'color' => $color],
                            ['stock' => fake()->numberBetween(0, 20)]
                        );
                    }
                }

                // 2 fotos extras por produto (detalhe e look)
                $product->images()->delete();
                $product->images()->createMany([
                    ['image_url' => $this->photo($name, 1)],
                    ['image_url' => $this->photo($name, 2)],
                ]);
            }
        }
    }

    private function photo(string $name, int $style): string
    {
        [$background, $text] = self::PHOTO_STYLES[$style];
        $label = rawurlencode($name . self::PHOTO_LABELS[$style]);

        return "https://placehold.co/600x800/{$background}/{$text}/png"
            . "?font=playfair-display&text={$label}";
    }
}
