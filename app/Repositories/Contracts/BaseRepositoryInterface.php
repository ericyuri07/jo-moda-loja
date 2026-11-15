<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Operações de persistência comuns a todos os recursos.
 *
 * Os Services dependem destes contratos (e não do Eloquent diretamente),
 * então a forma de acessar o banco pode mudar sem mexer na regra de negócio.
 */
interface BaseRepositoryInterface
{
    public function find(string $id): ?Model;

    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): void;

    /** Carrega no model os relacionamentos padrão do recurso. */
    public function loadRelations(Model $model): Model;
}
