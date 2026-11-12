<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

/**
 * Recorta (elimina hojas y/o delimita un área) un PDF ya digitalizado.
 *
 * Se usa FPDI, la misma librería que GacetaPdfService, para mantener el
 * contenido vectorial del documento (no lo convierte a imagen).
 */
class RecortePdfService
{
    /**
     * Tamaño mínimo (proporción de la hoja) que debe conservar un recorte.
     */
    private const RECORTE_MINIMO = 0.05;

    /**
     * Genera un PDF nuevo a partir de las páginas indicadas.
     *
     * @param  array<int, array{pagina: int, recorte?: array{x: float, y: float, w: float, h: float}}>  $paginas
     * @return int Cantidad de páginas escritas en el PDF resultante.
     */
    public function recortar(string $rutaOrigen, array $paginas, string $rutaDestino): int
    {
        if (!is_file($rutaOrigen)) {
            throw new \RuntimeException('El archivo PDF de origen no existe.');
        }

        // Se ordenan por número de página para respetar el orden original.
        usort($paginas, fn($a, $b) => ((int) ($a['pagina'] ?? 0)) <=> ((int) ($b['pagina'] ?? 0)));

        $fpdi = new Fpdi();
        $totalPaginas = $fpdi->setSourceFile($rutaOrigen);
        $paginasEscritas = 0;
        $paginasUsadas = [];

        foreach ($paginas as $item) {
            $numero = (int) ($item['pagina'] ?? 0);

            if ($numero < 1 || $numero > $totalPaginas || isset($paginasUsadas[$numero])) {
                continue;
            }

            $paginasUsadas[$numero] = true;

            $templateId = $fpdi->importPage($numero);
            $tamano = $fpdi->getTemplateSize($templateId);
            $anchoOriginal = (float) $tamano['width'];
            $altoOriginal = (float) $tamano['height'];

            $recorte = $item['recorte'] ?? null;

            if (is_array($recorte) && $this->recorteValido($recorte)) {
                $x = max(0.0, min(1.0, (float) $recorte['x']));
                $y = max(0.0, min(1.0, (float) $recorte['y']));
                $w = max(0.0, min(1.0 - $x, (float) $recorte['w']));
                $h = max(0.0, min(1.0 - $y, (float) $recorte['h']));

                if ($w >= self::RECORTE_MINIMO && $h >= self::RECORTE_MINIMO) {
                    $anchoRecorte = $anchoOriginal * $w;
                    $altoRecorte = $altoOriginal * $h;

                    $fpdi->AddPage(
                        $anchoRecorte > $altoRecorte ? 'L' : 'P',
                        [$anchoRecorte, $altoRecorte]
                    );

                    // El template se dibuja desplazado hacia arriba/izquierda; FPDF
                    // recorta automáticamente todo lo que queda fuera de la página.
                    $fpdi->useTemplate(
                        $templateId,
                        -$anchoOriginal * $x,
                        -$altoOriginal * $y,
                        $anchoOriginal,
                        $altoOriginal
                    );

                    $paginasEscritas++;
                    continue;
                }
            }

            // Sin recorte: se conserva la hoja completa.
            $fpdi->AddPage(
                $anchoOriginal > $altoOriginal ? 'L' : 'P',
                [$anchoOriginal, $altoOriginal]
            );
            $fpdi->useTemplate($templateId);
            $paginasEscritas++;
        }

        if ($paginasEscritas === 0) {
            throw new \RuntimeException('Debe conservar al menos una hoja del PDF.');
        }

        $fpdi->Output($rutaDestino, 'F');

        return $paginasEscritas;
    }

    /**
     * Valida que el recorte tenga coordenadas numéricas y un tamaño útil.
     */
    private function recorteValido(array $recorte): bool
    {
        foreach (['x', 'y', 'w', 'h'] as $clave) {
            if (!array_key_exists($clave, $recorte) || !is_numeric($recorte[$clave])) {
                return false;
            }
        }

        return (float) $recorte['w'] > 0 && (float) $recorte['h'] > 0;
    }
}
