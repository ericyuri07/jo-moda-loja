<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(private ProductRepositoryInterface $repository) {}

    public function list(array $filters = [], bool $onlyActive = true): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $onlyActive);
    }

    public function find(Product $product, bool $onlyActive = true): Product
    {
        $this->repository->loadRelations($product);

        return $this->repository->loadSuggestions($product, $onlyActive);
    }

    public function create(array $data): Product
    {
        $this->ensurePromotionalPriceIsLowerThanPrice(
            $data['price'],
            $data['promotional_price'] ?? null
        );

        $suggestionIds = Arr::pull($data, 'suggestion_ids');

        return DB::transaction(fn (): Product => $this->saveSuggestions(
            $this->repository->create($data),
            $suggestionIds
        ));
    }

    public function update(Product $product, array $data): Product
    {
        $price = array_key_exists('price', $data) ? $data['price'] : $product->price;
        $promotionalPrice = array_key_exists('promotional_price', $data)
            ? $data['promotional_price']
            : $product->promotional_price;

        $this->ensurePromotionalPriceIsLowerThanPrice($price, $promotionalPrice);

        $suggestionIds = Arr::pull($data, 'suggestion_ids');

        return DB::transaction(fn (): Product => $this->saveSuggestions(
            $this->repository->update($product, $data),
            $suggestionIds
        ));
    }

    public function delete(Product $product): void
    {
        $this->repository->delete($product);
    }

    /**
     * Sem a lista (null), os sugeridos ficam como estão; vazia, são removidos.
     *
     * @param  list<string>|null  $suggestionIds
     */
    private function saveSuggestions(Product $product, ?array $suggestionIds): Product
    {
        if ($suggestionIds !== null) {
            $this->repository->syncSuggestions($product, $suggestionIds);
        }

        return $this->repository->loadSuggestions($product, onlyActive: false);
    }

    /**
     * Regra de negócio: o preço promocional deve ser menor que o preço base.
     */
    private function ensurePromotionalPriceIsLowerThanPrice(mixed $price, mixed $promotionalPrice): void
    {
        if ($promotionalPrice === null) {
            return;
        }

        if (round((float) $promotionalPrice, 2) >= round((float) $price, 2)) {
            throw ValidationException::withMessages([
                'promotional_price' => ['O preço promocional deve ser menor que o preço base.'],
            ]);
        }
    }
}
