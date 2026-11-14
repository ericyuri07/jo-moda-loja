<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function list(array $filters = [], bool $onlyActive = true)
    {
        return Product::with(['category', 'images', 'variants'])
            ->when($onlyActive, fn($q) => $q->where('active', true))
            ->when($filters['category_id'] ?? null, fn($q, $v) => $q->where('category_id', $v))
            ->when($filters['search'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when(isset($filters['featured']), fn($q) => $q->where(
                'featured',
                filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN)
            ))
            ->latest()
            ->paginate();
    }

    public function find(Product $product): Product
    {
        return $product->load(['category', 'images', 'variants']);
    }

    public function create(array $data): Product
    {
        $this->ensurePromotionalPriceIsLowerThanPrice(
            $data['price'],
            $data['promotional_price'] ?? null
        );

        // refresh() traz os valores padrão do banco (ex: active = true)
        return $this->find(Product::create($data)->refresh());
    }

    public function update(Product $product, array $data): Product
    {
        // se o campo não veio na requisição, vale o que já está salvo
        $price = array_key_exists('price', $data) ? $data['price'] : $product->price;
        $promotionalPrice = array_key_exists('promotional_price', $data)
            ? $data['promotional_price']
            : $product->promotional_price;

        $this->ensurePromotionalPriceIsLowerThanPrice($price, $promotionalPrice);

        $product->update($data);

        return $this->find($product->refresh());
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * Regra de negócio: o preço promocional deve ser menor que o preço base.
     */
    private function ensurePromotionalPriceIsLowerThanPrice(mixed $price, mixed $promotionalPrice): void
    {
        if ($promotionalPrice === null) {
            return;
        }

        if (round((float) $promotionalPrice, 2) >= round((float) $price, 2)) {
            throw ValidationException::withMessages([
                'promotional_price' => ['O preço promocional deve ser menor que o preço base.'],
            ]);
        }
    }
}