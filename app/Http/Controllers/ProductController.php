<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index(Request $request)
    {
        return response()->json($this->service->list(
            $request->only(['category_id', 'search', 'featured']),
            onlyActive: ! $this->isAdmin($request)
        ));
    }

    public function store(ProductStoreRequest $request)
    {
        return response()->json($this->service->create($request->validated()), 201);
    }

    public function show(Request $request, Product $product)
    {
        $isAdmin = $this->isAdmin($request);

        abort_if(! $product->active && ! $isAdmin, 404, 'Produto não encontrado.');

        return response()->json($this->service->find($product, onlyActive: ! $isAdmin));
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        return response()->json(
            $this->service->update($product, $request->validated())
        );
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);

        return response()->json(['message' => 'Produto excluído']);
    }

    /**
     * Rotas públicas não passam pelo auth:sanctum, então o usuário
     * é lido direto do guard sanctum (se um token válido foi enviado).
     */
    private function isAdmin(Request $request): bool
    {
        return (bool) $request->user('sanctum')?->isAdmin();
    }
}
