<?php


namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 49, 399);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(12),
            'price' => $price,
            // 30% dos produtos em promoção, sempre abaixo do preço base
            'promotional_price' => fake()->boolean(30)
                ? round($price * fake()->randomFloat(2, 0.6, 0.9), 2)
                : null,
            'category_id' => Category::factory(),
            'image_url' => 'https://picsum.photos/seed/' . Str::slug($name) . '/600/800',
            'featured' => fake()->boolean(20),
            'active' => true,
        ];
    }
}
