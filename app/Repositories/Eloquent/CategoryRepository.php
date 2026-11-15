<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function allOrderedByName(): Collection
    {
        return $this->query()->orderBy('name')->get();
    }

    public function hasProducts(Category $category): bool
    {
        return $category->products()->exists();
    }
}
