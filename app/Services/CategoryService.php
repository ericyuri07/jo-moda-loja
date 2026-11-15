<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $repository) {}

    public function list(): Collection
    {
        return $this->repository->allOrderedByName();
    }

    public function find(Category $category): Category
    {
        return $this->repository->loadRelations($category);
    }

    /** O slug já chega gerado/validado pelo FormRequest. */
    public function create(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->repository->update($category, $data);
    }

    /**
     * Regra de negócio: categoria com produtos não pode ser excluída.
     */
    public function delete(Category $category): void
    {
        if ($this->repository->hasProducts($category)) {
            throw ValidationException::withMessages([
                'category' => ['Não é possível excluir uma categoria que possui produtos.'],
            ]);
        }

        $this->repository->delete($category);
    }
}
