<?php

namespace App\Services;

use App\Models\ProductImage;

class ProductImageService
{
    public function list(array $filters = [])
    {
        return ProductImage::with('product')
            ->when($filters['product_id'] ?? null, fn($q, $v) => $q->where('product_id', $v))
            ->latest()
            ->paginate();
    }

    public function find(ProductImage $image): ProductImage
    {
        return $image->load('product');
    }

    public function create(array $data): ProductImage
    {
        return $this->find(ProductImage::create($data)->refresh());
    }

    public function update(ProductImage $image, array $data): ProductImage
    {
        $image->update($data);

        return $this->find($image->refresh());
    }

    public function delete(ProductImage $image): void
    {
        $image->delete();
    }
}
