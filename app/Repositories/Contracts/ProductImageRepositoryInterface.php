<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductImageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{product_id?: string}  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator;
}
