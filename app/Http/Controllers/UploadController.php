<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Upload das fotos de produto feito pelo app.
 *
 * O arquivo fica em storage/app/public/products e é entregue por
 * GET /api/uploads/{arquivo}. Passar pela API (em vez de /storage) garante os
 * cabeçalhos de CORS, que o Flutter Web exige para exibir a imagem.
 */
class UploadController extends Controller
{
    private const FOLDER = 'products';

    /** POST /api/uploads (admin) — devolve a URL pública da foto. */
    public function store(Request $request)
    {
        $request->validate([
            // 2 MB: mesmo limite do upload_max_filesize do PHP
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'image.required' => 'Escolha uma foto.',
            'image.image' => 'O arquivo precisa ser uma imagem.',
            'image.mimes' => 'Use uma foto JPG, PNG ou WEBP.',
            'image.max' => 'A foto deve ter no máximo 2 MB.',
            'image.uploaded' => 'Não foi possível enviar a foto (máximo 2 MB).',
        ]);

        $path = $request->file('image')->store(self::FOLDER, 'public');

        return response()->json([
            'url' => url('api/uploads/' . basename($path)),
        ], 201);
    }

    /** GET /api/uploads/{filename} — público, para a loja exibir a foto. */
    public function show(string $filename)
    {
        $path = self::FOLDER . '/' . $filename;

        abort_unless(Storage::disk('public')->exists($path), 404, 'Foto não encontrada.');

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
