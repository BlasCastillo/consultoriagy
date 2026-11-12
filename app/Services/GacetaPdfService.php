<?php

namespace App\Services;

use App\Models\Gaceta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class GacetaPdfService
{
    /**
     * Alto (mm) de la banda superior reservada para el encabezado de página.
     */
    private const ALTO_BANDA_ENCABEZADO = 12.0;

    /**
     * Une los PDFs de todos los actos (en el orden del sumario) en un solo archivo,
     * estampando el título institucional en la primera página de cada acto.
     *
     * La página original se escala para reservar una banda superior donde se dibuja
     * el título, de modo que nunca se cubra el contenido del acto administrativo.
     */
    public function mergeActPdfs(Gaceta $gaceta): string
    {
        $gaceta->load(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos']);

        $items = $gaceta->solicitudes->flatMap(function ($solicitud) {
            return $solicitud->actos
                ->filter(fn($acto) => !empty($acto->archivo_pdf))
                ->map(fn($acto) => [
                    'acto' => $acto,
                    'institucion' => $solicitud->institucion,
                ]);
        })->values();

        if ($items->isEmpty()) {
            throw new \Exception('No hay PDFs de actos para consolidar.');
        }

        $fpdi = new Fpdi();
        $paginasAgregadas = 0;

        foreach ($items as $item) {
            $acto = $item['acto'];
            $institucion = $item['institucion'];
            $absolutePath = Storage::disk('public')->path($acto->archivo_pdf);

            if (!file_exists($absolutePath)) {
                continue;
            }

            $titulo = $institucion ? $institucion->tituloActo($gaceta->gobernador) : [];

            $pageCount = $fpdi->setSourceFile($absolutePath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $fpdi->importPage($i);
                $esPrimera = ($i === 1 && !empty($titulo));

                // Reserva espacio arriba y escala el contenido del acto para que
                // el título institucional no tape ninguna parte del documento.
                $this->agregarPaginaEscalada($fpdi, $tplId, $esPrimera ? $this->altoBandaTitulo($titulo) : 0.0);

                if ($esPrimera) {
                    $this->estamparTituloActo($fpdi, $titulo);
                }

                $paginasAgregadas++;
            }
        }

        if ($paginasAgregadas === 0) {
            throw new \Exception('Los PDFs de los actos no pudieron leerse.');
        }

        $relativePath = 'gacetas/revision/gaceta_' . $gaceta->id . '_' . time() . '.pdf';
        $absoluteOutput = Storage::disk('public')->path($relativePath);

        if (!is_dir(dirname($absoluteOutput))) {
            mkdir(dirname($absoluteOutput), 0775, true);
        }

        $fpdi->Output($absoluteOutput, 'F');

        return $relativePath;
    }

    /**
     * Genera un PDF fusionado temporal para previsualización (sumario + escaneo físico).
     */
    public function generarPreviewTemporal(Gaceta $gaceta): string
    {
        $gaceta->load(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos']);

        $pdfSumarioPath = storage_path('app/SGCJ/temp_sumario_' . $gaceta->id . '.pdf');
        if (!is_dir(dirname($pdfSumarioPath))) {
            mkdir(dirname($pdfSumarioPath), 0775, true);
        }

        $pdf = Pdf::loadView('gacetas.pdf_sumario', compact('gaceta'))->setPaper('letter', 'portrait');
        $pdf->save($pdfSumarioPath);

        $uploadedPdfPath = storage_path('app/SGCJ/' . $gaceta->ruta_archivo);

        if (!file_exists($uploadedPdfPath)) {
            if (file_exists($pdfSumarioPath)) {
                unlink($pdfSumarioPath);
            }
            throw new \Exception('El archivo físico no se encuentra.');
        }

        $fpdi = new Fpdi();

        // Añadir sumario
        $pageCountSummary = $fpdi->setSourceFile($pdfSumarioPath);
        for ($i = 1; $i <= $pageCountSummary; $i++) {
            $tplId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tplId);
            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($tplId);
        }

        // Añadir físico
        $pageCountUploaded = $fpdi->setSourceFile($uploadedPdfPath);
        for ($i = 1; $i <= $pageCountUploaded; $i++) {
            $tplId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tplId);
            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($tplId);
        }

        $tempOutputPath = storage_path('app/SGCJ/preview_' . $gaceta->id . '.pdf');
        $fpdi->Output($tempOutputPath, 'F');

        if (file_exists($pdfSumarioPath)) {
            unlink($pdfSumarioPath);
        }

        return $tempOutputPath;
    }

    /**
     * Genera el PDF final de la Gaceta (sumario DomPDF + PDF de los actos)
     * y devuelve su ruta relativa dentro del disco público.
     */
    public function generarPdfFinal(Gaceta $gaceta): string
    {
        $storage = Storage::disk('public');

        // Las gacetas históricas cargadas desde el módulo de Archivo ya fueron
        // publicadas y su PDF incluye la portada original. Se devuelve tal cual
        // para no anteponer otro sumario ni estampar encabezados adicionales.
        if ($gaceta->es_retroactiva) {
            if ($gaceta->archivo_pdf_completo && $storage->exists($gaceta->archivo_pdf_completo)) {
                return $gaceta->archivo_pdf_completo;
            }

            if ($gaceta->pdf_final && $storage->exists($gaceta->pdf_final)) {
                return $gaceta->pdf_final;
            }
        }

        $gaceta->load(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos']);

        $sourceRelative = $gaceta->archivo_pdf_completo;

        if (!$sourceRelative || !Storage::disk('public')->exists($sourceRelative)) {
            $actos = $gaceta->solicitudes->flatMap(fn($solicitud) => $solicitud->actos);
            $faltantes = $actos->filter(fn($acto) => empty($acto->archivo_pdf));

            if ($actos->isNotEmpty() && $faltantes->isEmpty()) {
                $sourceRelative = $this->mergeActPdfs($gaceta);
            }
        }

        $sourceAbsolute = null;

        if ($sourceRelative && Storage::disk('public')->exists($sourceRelative)) {
            $sourceAbsolute = Storage::disk('public')->path($sourceRelative);
        } elseif ($gaceta->ruta_archivo) {
            $legacy = storage_path('app/SGCJ/' . $gaceta->ruta_archivo);
            if (file_exists($legacy)) {
                $sourceAbsolute = $legacy;
            }
        }

        if (!$sourceAbsolute) {
            throw new \Exception('No se encontró el PDF de los actos para generar la Gaceta.');
        }

        // Sumario en PDF (DomPDF) para incrustarlo como portada.
        $sumarioPath = storage_path('app/SGCJ/temp_sumario_' . $gaceta->id . '.pdf');
        if (!is_dir(dirname($sumarioPath))) {
            mkdir(dirname($sumarioPath), 0775, true);
        }

        Pdf::loadView('gacetas.pdf_sumario', compact('gaceta'))
            ->setPaper('letter', 'portrait')
            ->save($sumarioPath);

        $fpdi = new Fpdi();

        // Sumario: se copia tal cual (sin encabezado de página).
        $paginasSumario = $fpdi->setSourceFile($sumarioPath);

        for ($i = 1; $i <= $paginasSumario; $i++) {
            $tplId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tplId);
            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($tplId);
        }

        // Actos: cada página lleva el encabezado con su numeración real.
        $paginasActos = $fpdi->setSourceFile($sourceAbsolute);
        $paginaGlobal = $paginasSumario + 1;

        for ($i = 1; $i <= $paginasActos; $i++) {
            $tplId = $fpdi->importPage($i);

            // Reserva la banda del encabezado antes de escalar el acto, así el
            // "PAGINA N° X" y el nombre de la Gaceta no se superponen al contenido.
            $this->agregarPaginaEscalada($fpdi, $tplId, self::ALTO_BANDA_ENCABEZADO);
            $this->estamparEncabezadoPagina($fpdi, $paginaGlobal);

            $paginaGlobal++;
        }

        $relativePath = 'gacetas/publicadas/gaceta_' . $gaceta->id . '_'
            . $this->slugArchivo((string) $gaceta->numero) . '_' . $gaceta->anio . '.pdf';
        $absoluteOutput = Storage::disk('public')->path($relativePath);

        if (!is_dir(dirname($absoluteOutput))) {
            mkdir(dirname($absoluteOutput), 0775, true);
        }

        $fpdi->Output($absoluteOutput, 'F');

        if (file_exists($sumarioPath)) {
            unlink($sumarioPath);
        }

        return $relativePath;
    }

    /**
     * Indica si la Gaceta tiene algún PDF realmente disponible para la vista previa.
     * No genera archivos: solo comprueba el disco.
     */
    public function tienePdfDisponible(Gaceta $gaceta): bool
    {
        $storage = Storage::disk('public');

        if ($gaceta->pdf_final && $storage->exists($gaceta->pdf_final)) {
            return true;
        }

        if ($gaceta->archivo_pdf_completo && $storage->exists($gaceta->archivo_pdf_completo)) {
            return true;
        }

        if ($gaceta->ruta_archivo) {
            if (file_exists(public_path('gacetas_pdf/' . $gaceta->ruta_archivo))
                || file_exists(storage_path('app/SGCJ/' . $gaceta->ruta_archivo))) {
                return true;
            }
        }

        $gaceta->loadMissing('solicitudes.actos');
        $actos = $gaceta->solicitudes->flatMap(fn($solicitud) => $solicitud->actos);

        return $actos->isNotEmpty()
            && $actos->every(fn($acto) => !empty($acto->archivo_pdf) && $storage->exists($acto->archivo_pdf));
    }

    /**
     * Devuelve la ruta absoluta del mejor PDF disponible para la vista previa.
     *
     * Orden de preferencia: PDF consolidado de los actos → PDF final publicado →
     * unión de los PDFs de los actos → escaneo físico heredado (sumario + escaneo).
     * Devuelve null si no hay ningún archivo legible en el servidor.
     */
    public function obtenerRutaVistaPrevia(Gaceta $gaceta): ?string
    {
        $storage = Storage::disk('public');

        // El PDF consolidado de los actos va primero: la portada y el sumario
        // ya se muestran en HTML, así que usar pdf_final duplicaría esas páginas.
        if ($gaceta->archivo_pdf_completo && $storage->exists($gaceta->archivo_pdf_completo)) {
            return $storage->path($gaceta->archivo_pdf_completo);
        }

        if ($gaceta->pdf_final && $storage->exists($gaceta->pdf_final)) {
            return $storage->path($gaceta->pdf_final);
        }

        $gaceta->loadMissing(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos']);
        $actos = $gaceta->solicitudes->flatMap(fn($solicitud) => $solicitud->actos);

        $todosConPdf = $actos->isNotEmpty()
            && $actos->every(fn($acto) => !empty($acto->archivo_pdf) && $storage->exists($acto->archivo_pdf));

        if ($todosConPdf) {
            try {
                $relativePath = $this->mergeActPdfs($gaceta);
                $gaceta->forceFill(['archivo_pdf_completo' => $relativePath])->save();

                if ($storage->exists($relativePath)) {
                    return $storage->path($relativePath);
                }
            } catch (\Throwable $e) {
                // Se intenta el escaneo físico heredado antes de rendirse.
            }
        }

        if ($gaceta->ruta_archivo) {
            $legacyPublico = public_path('gacetas_pdf/' . $gaceta->ruta_archivo);

            if (is_file($legacyPublico)) {
                return $legacyPublico;
            }

            try {
                return $this->generarPreviewTemporal($gaceta);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Resuelve la ruta física en el servidor para descargar el PDF oficial de una Gaceta.
     */
    public function obtenerRutaDescarga(Gaceta $gaceta): ?string
    {
        $storage = Storage::disk('public');
        $rutaDescarga = null;

        // Gaceta histórica: el archivo subido desde Archivo ya es el documento
        // final (con su portada). Se descarga sin regenerarlo.
        if ($gaceta->es_retroactiva && $gaceta->archivo_pdf_completo && $storage->exists($gaceta->archivo_pdf_completo)) {
            return $storage->path($gaceta->archivo_pdf_completo);
        }

        if ($gaceta->pdf_final && $storage->exists($gaceta->pdf_final)) {
            $rutaDescarga = $storage->path($gaceta->pdf_final);
        } else {
            try {
                $gaceta->pdf_final = $this->generarPdfFinal($gaceta);
                $gaceta->save();
                $rutaDescarga = $storage->path($gaceta->pdf_final);
            } catch (\Throwable $e) {
                if ($gaceta->archivo_pdf_completo && $storage->exists($gaceta->archivo_pdf_completo)) {
                    $rutaDescarga = $storage->path($gaceta->archivo_pdf_completo);
                } elseif ($gaceta->ruta_archivo && file_exists(public_path('gacetas_pdf/' . $gaceta->ruta_archivo))) {
                    $rutaDescarga = public_path('gacetas_pdf/' . $gaceta->ruta_archivo);
                }
            }
        }

        return $rutaDescarga;
    }

    /**
     * Genera un slug seguro para nombres de archivo de gacetas.
     */
    public function slugArchivo(string $valor): string
    {
        $limpio = preg_replace('/[^A-Za-z0-9_-]+/', '_', $valor) ?? '';

        return trim($limpio, '_') ?: 'gaceta';
    }

    /**
     * Alto (mm) de la banda reservada para el título institucional.
     *
     * @param array<int, string> $lineas
     */
    private function altoBandaTitulo(array $lineas): float
    {
        return count($lineas) > 1 ? 24.0 : 18.0;
    }

    /**
     * Agrega una página copiando el template y, si se indica, reserva una banda
     * superior escalando el contenido proporcionalmente para no recortarlo.
     */
    private function agregarPaginaEscalada(Fpdi $pdf, int|string $tplId, float $bandaSuperior): void
    {
        $size = $pdf->getTemplateSize($tplId);
        $ancho = (float) $size['width'];
        $alto = (float) $size['height'];

        $pdf->AddPage($size['orientation'], [$ancho, $alto]);

        $anchoPagina = $pdf->GetPageWidth();
        $altoPagina = $pdf->GetPageHeight();

        if ($bandaSuperior <= 0) {
            $pdf->useTemplate($tplId, 0, 0, $ancho, $alto);
            return;
        }

        $altoDisponible = max(1.0, $altoPagina - $bandaSuperior);
        $escala = min($anchoPagina / max(1.0, $ancho), $altoDisponible / max(1.0, $alto));
        $anchoEscalado = $ancho * $escala;
        $altoEscalado = $alto * $escala;
        $x = ($anchoPagina - $anchoEscalado) / 2;

        $pdf->useTemplate($tplId, $x, $bandaSuperior, $anchoEscalado, $altoEscalado);
    }

    /**
     * Dibuja el título institucional en la parte superior de la página actual.
     *
     * @param array<int, string> $lineas
     */
    private function estamparTituloActo(Fpdi $pdf, array $lineas): void
    {
        $anchoPagina = $pdf->GetPageWidth();
        $altoBanda = $this->altoBandaTitulo($lineas);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, $anchoPagina, $altoBanda, 'F');

        $pdf->SetTextColor(0, 0, 0);

        // Centra verticalmente el bloque de texto dentro de la banda reservada.
        $altoTexto = isset($lineas[1]) ? 11.0 : 6.0;
        $y = max(3.0, ($altoBanda - $altoTexto) / 2);

        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY(15, $y);
        $pdf->Cell($anchoPagina - 30, 6, $this->textoPdf($lineas[0]), 0, 1, 'C');

        if (isset($lineas[1])) {
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetX(15);
            $pdf->Cell($anchoPagina - 30, 5, $this->textoPdf($lineas[1]), 0, 1, 'C');
        }
    }

    /**
     * Dibuja el encabezado de las páginas de actos en el PDF final:
     * "PAGINA N° X" a un lado y "GACETA OFICIAL DEL ESTADO YARACUY" centrado.
     */
    private function estamparEncabezadoPagina(Fpdi $pdf, int $numeroPagina): void
    {
        $anchoPagina = $pdf->GetPageWidth();

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, $anchoPagina, self::ALTO_BANDA_ENCABEZADO, 'F');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('Helvetica', 'B', 9);

        $pdf->SetXY(15, 5);
        $pdf->Cell($anchoPagina - 30, 4, $this->textoPdf('GACETA OFICIAL DEL ESTADO YARACUY'), 0, 0, 'C');

        $textoPagina = 'PAGINA N° ' . $numeroPagina;

        if ($numeroPagina % 2 === 0) {
            $pdf->SetXY(15, 5);
            $pdf->Cell(60, 4, $this->textoPdf($textoPagina), 0, 0, 'L');
        } else {
            $pdf->SetXY($anchoPagina - 75, 5);
            $pdf->Cell(60, 4, $this->textoPdf($textoPagina), 0, 0, 'R');
        }

        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, 10, $anchoPagina - 15, 10);
    }

    /**
     * Convierte UTF-8 a Windows-1252 (codificación de las fuentes core de FPDF).
     */
    private function textoPdf(string $texto): string
    {
        $convertido = @mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');

        return $convertido === false ? $texto : $convertido;
    }
}
