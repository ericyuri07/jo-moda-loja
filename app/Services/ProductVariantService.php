<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductVariantService
{
    public function __construct(private ProductVariantRepositoryInterface $repository) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function find(ProductVariant $variant): ProductVariant
    {
        return $this->repository->loadRelations($variant);
    }

    public function create(array $data): ProductVariant
    {
        return $this->repository->create($data);
    }

    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        return $this->repository->update($variant, $data);
    }

    public function delete(ProductVariant $variant): void
    {
        $this->repository->delete($variant);
    }
}
