<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $service)
    {
    }

    public function index(Request $request)
    {
        // filtros opcionais: ?category_id=...&search=...&featured=true
        // admin vê também os inativos; visitante/cliente só os ativos
        return $this->service->list(
            $request->only(['category_id', 'search', 'featured']),
            onlyActive: !$this->isAdmin($request)
        );
    }

    public function store(ProductStoreRequest $request)
    {
        $data = $request->validated();

        return response($this->service->create($data), 201);
    }

    public function show(Request $request, Product $product)
    {
        // produto inativo não existe para quem não é admin
        abort_if(!$product->active && !$this->isAdmin($request), 404, 'Produto não encontrado.');

        return $this->service->find($product);
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $data = $request->validated();

        return $this->service->update($product, $data);
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