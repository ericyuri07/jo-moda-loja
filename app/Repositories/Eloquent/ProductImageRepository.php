<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductImageRepository extends BaseRepository implements ProductImageRepositoryInterface
{
    protected array $relations = ['product'];

    public function __construct(ProductImage $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->latest()
            ->paginate();
    }
}
