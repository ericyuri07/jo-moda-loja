<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductImageStoreRequest;
use App\Http\Requests\ProductImageUpdateRequest;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(private ProductImageService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->service->list($request->only(['product_id']));
    }

    public function store(ProductImageStoreRequest $request)
    {
        $data = $request->validated();

        return response($this->service->create($data), 201);
    }

    public function show(ProductImage $productImage)
    {
        return $this->service->find($productImage);
    }

    public function update(ProductImageUpdateRequest $request, ProductImage $productImage)
    {
        $data = $request->validated();

        return $this->service->update($productImage, $data);
    }

    public function destroy(ProductImage $productImage)
    {
        $this->service->delete($productImage);

        return response()->json(['message' => 'Imagem excluída']);
    }
}
