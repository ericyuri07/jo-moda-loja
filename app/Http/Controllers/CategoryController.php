<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {
    }

    public function index()
    {
        return response()->json($this->categoryService->list());
    }

    public function store(CategoryStoreRequest $request)
    {
        $category = $this->categoryService->create($request->validated());

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        return response()->json(
            $this->categoryService->update($category, $request->validated())
        );
    }

    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return response()->json(['message' => 'Categoria excluída com sucesso.']);
    }
}