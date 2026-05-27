<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gaceta;
use App\Models\SumarioGaceta;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;

class GacetaController extends Controller
{
    public function index(Request $request)
    {
        $query = Gaceta::query();

        if ($request->filled('anio')) {
            $query->where('anio', $request->anio);
        }
        if ($request->filled('numero')) {
            $query->where('numero', $request->numero);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if (auth()->user()->hasAnyRole(['Institucion', 'Institucional'])) {
            $query->where('estado', 'Publicada');
        }

        if ($request->filled('institucion_id') || $request->filled('tipo_acto') || $request->filled('keyword')) {
            $query->whereHas('sumarios', function ($q) use ($request) {
                if ($request->filled('institucion_id')) {
                    $q->where('institucion_id', $request->institucion_id);
                }
                if ($request->filled('tipo_acto')) {
                    $q->where('tipo_acto', $request->tipo_acto);
                }
                if ($request->filled('keyword')) {
                    $q->where(function($sub) use ($request) {
                        $sub->where('descripcion', 'like', '%' . $request->keyword . '%')
                            ->orWhere('numero_acto', 'like', '%' . $request->keyword . '%');
                    });
                }
            });
        }

        // Eager loading de sumarios e institucion para evitar N+1 en las vistas
        $gacetas = $query->with('sumarios.institucion')->orderBy('anio', 'desc')->orderBy('numero', 'desc')->paginate(15);
        $institutions = Institution::all();

        return view('gacetas.index', compact('gacetas', 'institutions'));
    }

    public function create()
    {
        $institutions = Institution::all();
        $gacetas = Gaceta::orderBy('anio', 'desc')->orderBy('numero', 'desc')->get();
        $gobernadores = \App\Models\Gobernador::with('titulo')->get();
        return view('gacetas.create', compact('institutions', 'gacetas', 'gobernadores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer',
            'tipo' => 'required|in:Ordinaria,Extraordinaria',
            'fecha_emision' => 'nullable|date',
            'fecha_recepcion_fisica' => 'nullable|date',
            'gobernador_id' => 'required|exists:gobernadores,id',
        ]);

        try {
            DB::beginTransaction();

            $ultimoNumero = Gaceta::where('anio', $request->anio)
                ->where('tipo', $request->tipo)
                ->max('numero');

            $nuevoNumero = $ultimoNumero ? $ultimoNumero + 1 : 1;

            $gaceta = new Gaceta();
            $gaceta->numero = $nuevoNumero;
            $gaceta->anio = $request->anio;
            $gaceta->tipo = $request->tipo;
            $gaceta->anio_politico = $request->anio_politico;
            $gaceta->mes_politico = $request->mes_politico;
            $gaceta->fecha_emision = $request->fecha_emision;
            $gaceta->fecha_recepcion_fisica = $request->fecha_recepcion_fisica;
            $gaceta->estado = 'Reservada';
            $gaceta->corregida_de_id = $request->corregida_de_id;
            $gaceta->gobernador_id = $request->gobernador_id;

            $gaceta->save();

            if ($request->has('sumarios') && is_array($request->sumarios)) {
                foreach ($request->sumarios as $sumario) {
                    if (!empty($sumario['institucion_id']) && !empty($sumario['tipo_acto'])) {
                        $gaceta->sumarios()->create([
                            'institucion_id' => $sumario['institucion_id'],
                            'tipo_acto' => $sumario['tipo_acto'],
                            'numero_acto' => $sumario['numero_acto'],
                            'descripcion' => $sumario['descripcion'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('gacetas.index')->with('success', 'Gaceta reservada con éxito (Número asignado: ' . $nuevoNumero . '). Puede subir el PDF cuando esté lista.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar gaceta: ' . $e->getMessage());
        }
    }

    public function uploadPdf(string $id)
    {
        $gaceta = Gaceta::findOrFail($id);
        return view('gacetas.upload_pdf', compact('gaceta'));
    }

    public function savePdf(Request $request, string $id)
    {
        $request->validate([
            'ruta_archivo' => 'required|mimes:pdf|max:10240', // max 10MB
        ]);

        $gaceta = Gaceta::findOrFail($id);
        $gaceta->load(['gobernador.titulo', 'sumarios.institucion']);

        if ($request->hasFile('ruta_archivo')) {
            // 1. Generar Sumario PDF temporal
            $pdfSumarioPath = storage_path('app/public/temp_sumario_' . $gaceta->id . '.pdf');
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gacetas.pdf_sumario', compact('gaceta'));
            $pdf->save($pdfSumarioPath);

            // 2. Obtener PDF físico subido
            $uploadedPdfPath = $request->file('ruta_archivo')->getRealPath();

            // 3. Fusionar PDFs con FPDI
            $fpdi = new \setasign\Fpdi\Fpdi();

            // Añadir páginas del sumario
            $pageCountSummary = $fpdi->setSourceFile($pdfSumarioPath);
            for ($i = 1; $i <= $pageCountSummary; $i++) {
                $tplId = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tplId);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);
            }

            // Añadir páginas del archivo subido
            $pageCountUploaded = $fpdi->setSourceFile($uploadedPdfPath);
            for ($i = 1; $i <= $pageCountUploaded; $i++) {
                $tplId = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tplId);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);
            }

            // 4. Guardar archivo final en public/gacetas_pdf
            $outputDir = public_path('gacetas_pdf');
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $fileName = 'gaceta_' . $gaceta->anio . '_' . str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) . '.pdf';
            $outputPath = $outputDir . '/' . $fileName;
            
            $fpdi->Output($outputPath, 'F');

            // 5. Actualizar la base de datos
            $gaceta->ruta_archivo = $fileName;
            $gaceta->estado = 'Publicada';
            $gaceta->fecha_publicacion = \Carbon\Carbon::now();
            $gaceta->save();

            // Eliminar temporal
            if (file_exists($pdfSumarioPath)) {
                unlink($pdfSumarioPath);
            }

            return redirect()->route('gacetas.index')->with('success', 'PDF de la Gaceta generado, fusionado y publicado con éxito.');
        }

        return back()->with('error', 'No se pudo subir el archivo.');
    }

    public function show(string $id)
    {
        $gaceta = Gaceta::with(['sumarios.institucion', 'corregidaDe', 'correcciones'])->findOrFail($id);
        return view('gacetas.show', compact('gaceta'));
    }

    public function edit(string $id) { }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }
}
