<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductVariantStoreRequest;
use App\Http\Requests\ProductVariantUpdateRequest;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function __construct(private ProductVariantService $service) {}

    public function index(Request $request)
    {
        return response()->json($this->service->list($request->only(['product_id', 'size', 'color'])));
    }

    public function store(ProductVariantStoreRequest $request)
    {
        return response()->json($this->service->create($request->validated()), 201);
    }

    public function show(ProductVariant $productVariant)
    {
        return response()->json($this->service->find($productVariant));
    }

    public function update(ProductVariantUpdateRequest $request, ProductVariant $productVariant)
    {
        return response()->json(
            $this->service->update($productVariant, $request->validated())
        );
    }

    public function destroy(ProductVariant $productVariant)
    {
        $this->service->delete($productVariant);

        return response()->json(['message' => 'Variante excluída']);
    }
}
