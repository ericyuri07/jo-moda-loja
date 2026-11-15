<?php

namespace App\Providers;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ProductImageRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProductVariantRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Liga cada contrato de repositório à sua implementação Eloquent.
 * Os Services recebem a interface por injeção de dependência.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        CategoryRepositoryInterface::class => CategoryRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        ProductVariantRepositoryInterface::class => ProductVariantRepository::class,
        ProductImageRepositoryInterface::class => ProductImageRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
    ];
}
