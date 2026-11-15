<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{category_id?: string, search?: string, featured?: mixed}  $filters
     */
    public function paginate(array $filters = [], bool $onlyActive = true): LengthAwarePaginator;
}
