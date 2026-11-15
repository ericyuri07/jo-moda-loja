<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Armazenamento das fotos enviadas pelo app.
 *
 * O arquivo fica em storage/app/public/products e é entregue por
 * GET /api/uploads/{arquivo}. Passar pela API (em vez de /storage) garante os
 * cabeçalhos de CORS, que o Flutter Web exige para exibir a imagem.
 */
class UploadService
{
    private const DISK = 'public';

    private const FOLDER = 'products';

    /** Salva a foto e devolve a URL pública dela. */
    public function store(UploadedFile $file): string
    {
        $path = $file->store(self::FOLDER, self::DISK);

        return url('api/uploads/'.basename($path));
    }

    public function response(string $filename): StreamedResponse
    {
        $path = self::FOLDER.'/'.basename($filename);
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($path)) {
            throw new NotFoundHttpException('Foto não encontrada.');
        }

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
