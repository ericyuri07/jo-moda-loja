<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use App\Services\UploadService;

class UploadController extends Controller
{
    public function __construct(private UploadService $service) {}

    /** POST /api/uploads (admin) — devolve a URL pública da foto. */
    public function store(UploadImageRequest $request)
    {
        return response()->json([
            'url' => $this->service->store($request->file('image')),
        ], 201);
    }

    /** GET /api/uploads/{filename} — público, para a loja exibir a foto. */
    public function show(string $filename)
    {
        return $this->service->response($filename);
    }
}
