<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{category_id?: string, search?: string, featured?: mixed}  $filters
     */
    public function paginate(array $filters = [], bool $onlyActive = true): LengthAwarePaginator;

    /**
     * Substitui os produtos sugeridos, na ordem recebida.
     *
     * @param  list<string>  $suggestionIds
     */
    public function syncSuggestions(Product $product, array $suggestionIds): void;

    /** Carrega os produtos sugeridos; com $onlyActive, esconde os inativos. */
    public function loadSuggestions(Product $product, bool $onlyActive = true): Product;
}
