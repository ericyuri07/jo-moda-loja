<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductVariantRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{product_id?: string, size?: string, color?: string}  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator;
}
