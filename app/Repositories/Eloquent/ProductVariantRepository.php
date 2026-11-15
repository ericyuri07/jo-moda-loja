<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductVariant;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductVariantRepository extends BaseRepository implements ProductVariantRepositoryInterface
{
    protected array $relations = ['product'];

    public function __construct(ProductVariant $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->when($filters['size'] ?? null, fn ($q, $v) => $q->where('size', $v))
            ->when($filters['color'] ?? null, fn ($q, $v) => $q->where('color', $v))
            ->orderBy('product_id')
            ->orderBy('size')
            ->orderBy('color')
            ->paginate();
    }
}
