<?php

namespace Database\Seeders\Concerns;

/**
 * Fotos fictícias (placehold.co) dos produtos de exemplo: cartões nas cores
 * da marca com o nome do produto. O serviço devolve PNG e libera CORS, então
 * funciona também no Flutter Web.
 */
trait GeneratesProductPhotos
{
    /** Estilos de foto: [fundo, texto] (hex sem #) e o sufixo do texto. */
    private static array $photoStyles = [
        'main' => ['1E1B1B', 'D4A94A', ''],
        'detail' => ['F1D27A', '1E1B1B', ' · detalhe'],
        'look' => ['4F4D4C', 'FFFFFF', ' · look'],
    ];

    protected function productPhoto(string $productName, string $style = 'main'): string
    {
        [$background, $text, $suffix] = self::$photoStyles[$style];
        $label = rawurlencode($productName.$suffix);

        return "https://placehold.co/600x800/{$background}/{$text}/png"
            ."?font=playfair-display&text={$label}";
    }
}
