<?php

namespace App\Services;

use App\Models\Gaceta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class GacetaPdfService
{
    /**
     * Une los PDFs de todos los actos (en el orden del sumario) en un solo archivo,
     * estampando el título institucional en la primera página de cada acto.
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
                $size = $fpdi->getTemplateSize($tplId);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);

                if ($i === 1 && !empty($titulo)) {
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
            $size = $fpdi->getTemplateSize($tplId);
            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($tplId);

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
     * Resuelve la ruta física en el servidor para descargar el PDF oficial de una Gaceta.
     */
    public function obtenerRutaDescarga(Gaceta $gaceta): ?string
    {
        $storage = Storage::disk('public');
        $rutaDescarga = null;

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
     * Dibuja el título institucional en la parte superior de la página actual.
     *
     * @param array<int, string> $lineas
     */
    private function estamparTituloActo(Fpdi $pdf, array $lineas): void
    {
        $anchoPagina = $pdf->GetPageWidth();
        $altoBanda = count($lineas) > 1 ? 26.0 : 20.0;

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, $anchoPagina, $altoBanda, 'F');

        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY(15, 14);
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
        $pdf->Rect(0, 0, $anchoPagina, 12, 'F');

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
