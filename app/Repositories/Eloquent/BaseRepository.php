<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Implementação Eloquent das operações comuns (CRUD).
 *
 * Cada repositório concreto só informa o seu model e os relacionamentos
 * padrão; as consultas específicas ficam nele mesmo.
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /** Relacionamentos carregados junto com o recurso (eager loading). */
    protected array $relations = [];

    public function __construct(protected Model $model) {}

    public function find(string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function create(array $data): Model
    {
        return $this->loadRelations($this->model->newQuery()->create($data)->refresh());
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $this->loadRelations($model->refresh());
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    public function loadRelations(Model $model): Model
    {
        return $model->load($this->relations);
    }

    /** Query base já com os relacionamentos padrão. */
    protected function query(): Builder
    {
        return $this->model->newQuery()->with($this->relations);
    }
}
