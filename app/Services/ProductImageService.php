<?php

namespace App\Services;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductImageService
{
    public function __construct(private ProductImageRepositoryInterface $repository) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function find(ProductImage $image): ProductImage
    {
        return $this->repository->loadRelations($image);
    }

    public function create(array $data): ProductImage
    {
        return $this->repository->create($data);
    }

    public function update(ProductImage $image, array $data): ProductImage
    {
        return $this->repository->update($image, $data);
    }

    public function delete(ProductImage $image): void
    {
        $this->repository->delete($image);
    }
}
