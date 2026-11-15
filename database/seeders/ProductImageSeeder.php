<?php

namespace Database\Seeders;

use App\Models\Product;
use Database\Seeders\Concerns\GeneratesProductPhotos;
use Illuminate\Database\Seeder;

/**
 * Duas fotos extras (detalhe e look) para cada produto de exemplo, além da
 * foto principal. Pode rodar mais de uma vez sem duplicar e sem mexer nas
 * fotos cadastradas pelo app.
 */
class ProductImageSeeder extends Seeder
{
    use GeneratesProductPhotos;

    public function run(): void
    {
        Product::whereIn('slug', ProductSeeder::catalogSlugs())->each(function (Product $product) {
            foreach (['detail', 'look'] as $style) {
                $product->images()->firstOrCreate([
                    'image_url' => $this->productPhoto($product->name, $style),
                ]);
            }
        });
    }
}
