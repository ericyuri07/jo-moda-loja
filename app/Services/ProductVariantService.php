<?php

namespace App\Services;

use App\Models\ProductVariant;

class ProductVariantService
{
    public function list(array $filters = [])
    {
        return ProductVariant::with('product')
            ->when($filters['product_id'] ?? null, fn($q, $v) => $q->where('product_id', $v))
            ->when($filters['size'] ?? null, fn($q, $v) => $q->where('size', $v))
            ->when($filters['color'] ?? null, fn($q, $v) => $q->where('color', $v))
            ->orderBy('product_id')
            ->orderBy('size')
            ->orderBy('color')
            ->paginate();
    }

    public function find(ProductVariant $variant): ProductVariant
    {
        return $variant->load('product');
    }

    public function create(array $data): ProductVariant
    {
        return $this->find(ProductVariant::create($data)->refresh());
    }

    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);

        return $this->find($variant->refresh());
    }

    public function delete(ProductVariant $variant): void
    {
        $variant->delete();
    }
}
