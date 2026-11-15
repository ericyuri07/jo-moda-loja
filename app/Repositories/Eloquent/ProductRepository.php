<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected array $relations = ['category', 'images', 'variants'];

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], bool $onlyActive = true): LengthAwarePaginator
    {
        return $this->query()
            ->when($onlyActive, fn ($q) => $q->where('active', true))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when(isset($filters['featured']), fn ($q) => $q->where(
                'featured',
                filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN)
            ))
            ->latest()
            ->paginate();
    }
}
