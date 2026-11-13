<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $category = $this->categoryService->create($request->all());

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        return response()->json(
            $this->categoryService->update($category, $request->all())
        );
    }

    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);

        return response()->json(['message' => 'Categoria excluída com sucesso.']);
    }
}