<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gaceta;
use App\Models\SumarioGaceta;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Notifications\NuevaSolicitudNotification;
use App\Notifications\GacetaAsignadaNotification;
use App\Notifications\GacetaPorAprobarNotification;
use Illuminate\Support\Facades\Notification;

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

        $gacetas = $query->with('sumarios.institucion')->orderBy('created_at', 'desc')->paginate(15);
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
            'gobernador_id' => 'required|exists:gobernadores,id',
        ]);

        try {
            DB::beginTransaction();

            $gaceta = new Gaceta();
            $gaceta->anio = $request->anio;
            $gaceta->tipo = $request->tipo;
            $gaceta->anio_politico = $request->anio_politico;
            $gaceta->mes_politico = $request->mes_politico;
            $gaceta->fecha_emision = $request->fecha_emision;
            $gaceta->estado = 'Solicitada';
            $gaceta->corregida_de_id = $request->corregida_de_id;
            $gaceta->gobernador_id = $request->gobernador_id;
            $gaceta->numero = 0; // Temporal until assigned

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

            activity()
                ->useLog('BPM_Gacetas')
                ->performedOn($gaceta)
                ->causedBy(auth()->user())
                ->log('Solicitud Administrativa Creada');

            // Notify Jefes
            $jefes = User::role(['Jefe de Digitalización', 'Super Administrador', 'Super Admin'])->get();
            Notification::send($jefes, new NuevaSolicitudNotification($gaceta));

            return redirect()->route('gacetas.index')->with('success', 'Gaceta administrativa creada con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al solicitar gaceta: ' . $e->getMessage());
        }
    }

    public function evaluarChecklist(string $id)
    {
        $gaceta = Gaceta::findOrFail($id);
        $gobernadores = \App\Models\Gobernador::with('titulo')->get();
        return view('gacetas.checklist', compact('gaceta', 'gobernadores'));
    }

    public function guardarChecklist(Request $request, string $id)
    {
        $gaceta = Gaceta::findOrFail($id);

        $request->validate([
            'checklist' => 'required|array',
            'checklist.*' => 'boolean',
            'tipo' => 'required|in:Ordinaria,Extraordinaria',
            'anio' => 'required|integer',
            'gobernador_id' => 'required|exists:gobernadores,id',
            'anio_politico' => 'required|string',
            'mes_politico' => 'required|string',
            'fecha_recepcion_fisica' => 'required|date',
        ]);

        $gaceta->checklist = $request->checklist;
        $gaceta->tipo = $request->tipo;
        $gaceta->anio = $request->anio;
        $gaceta->gobernador_id = $request->gobernador_id;
        $gaceta->anio_politico = $request->anio_politico;
        $gaceta->mes_politico = $request->mes_politico;
        $gaceta->fecha_recepcion_fisica = $request->fecha_recepcion_fisica;
        
        $allChecked = count(array_filter($request->checklist)) === count($request->checklist);

        if ($allChecked) {
            $ultimoNumero = Gaceta::where('anio', $gaceta->anio)
                ->where('tipo', $gaceta->tipo)
                ->where('numero', '>', 0)
                ->max('numero');

            $gaceta->numero = $ultimoNumero ? $ultimoNumero + 1 : 1;
            $gaceta->estado = 'Reservada';
            $gaceta->jefe_id = auth()->id();
            $gaceta->save();

            activity()
                ->useLog('BPM_Gacetas')
                ->performedOn($gaceta)
                ->causedBy(auth()->user())
                ->log('Checklist aprobado. Gaceta Reservada.');

            return redirect()->route('gacetas.index')->with('success', 'Checklist validado. Se ha reservado el Número: ' . str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT));
        }

        $gaceta->save();

        activity()
            ->useLog('BPM_Gacetas')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Checklist guardado parcialmente');

        return back()->with('info', 'Checklist guardado parcialmente. Aún faltan requisitos.');
    }

    public function asignarDigitalizador(string $id)
    {
        $gaceta = Gaceta::findOrFail($id);
        $digitalizadores = User::role('Digitalizador')->get();
        return view('gacetas.asignar', compact('gaceta', 'digitalizadores'));
    }

    public function guardarAsignacion(Request $request, string $id)
    {
        $request->validate([
            'digitalizador_id' => 'required|exists:users,id',
        ]);

        $gaceta = Gaceta::findOrFail($id);
        $gaceta->digitalizador_id = $request->digitalizador_id;
        if ($request->has('fecha_recepcion_fisica')) {
            $gaceta->fecha_recepcion_fisica = $request->fecha_recepcion_fisica;
        }
        $gaceta->estado = 'En Escaneo';
        $gaceta->save();

        activity()
            ->useLog('BPM_Gacetas')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Asignada a Digitalizador');

        $digitalizador = User::find($request->digitalizador_id);
        $digitalizador->notify(new GacetaAsignadaNotification($gaceta));

        return redirect()->route('gacetas.index')->with('success', 'Gaceta asignada a ' . $digitalizador->name . ' correctamente.');
    }

    public function uploadPdf(string $id)
    {
        $gaceta = Gaceta::findOrFail($id);
        return view('gacetas.upload_pdf', compact('gaceta'));
    }

    public function savePdf(Request $request, string $id)
    {
        $request->validate([
            'ruta_archivo' => 'required|mimes:pdf|max:10240',
        ]);

        $gaceta = Gaceta::findOrFail($id);

        if ($request->hasFile('ruta_archivo')) {
            $outputDir = storage_path('app/SGCJ');
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $fileName = 'temp_fisico_' . $gaceta->anio . '_' . str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) . '.pdf';
            $request->file('ruta_archivo')->move($outputDir, $fileName);

            $gaceta->ruta_archivo = $fileName;
            $gaceta->estado = 'Por Aprobar';
            $gaceta->save();

            activity()
                ->useLog('BPM_Gacetas')
                ->performedOn($gaceta)
                ->causedBy(auth()->user())
                ->log('PDF físico escaneado subido');

            // Notify Jefe
            if ($gaceta->jefe_id) {
                $jefe = User::find($gaceta->jefe_id);
                if ($jefe) {
                    $jefe->notify(new GacetaPorAprobarNotification($gaceta));
                }
            }

            return redirect()->route('gacetas.digitalizador')->with('success', 'PDF escaneado subido correctamente. En espera de aprobación.');
        }

        return back()->with('error', 'No se pudo subir el archivo.');
    }

    public function aprobarPublicacion(string $id)
    {
        $gaceta = Gaceta::findOrFail($id);
        return view('gacetas.aprobar', compact('gaceta'));
    }

    public function publicar(Request $request, string $id)
    {
        $gaceta = Gaceta::findOrFail($id);
        $gaceta->load(['gobernador.titulo', 'sumarios.institucion']);

        if ($request->accion === 'rechazar') {
            $gaceta->estado = 'En Escaneo';
            $gaceta->save();

            activity()
                ->useLog('BPM_Gacetas')
                ->performedOn($gaceta)
                ->causedBy(auth()->user())
                ->log('PDF rechazado por Jefatura. Devuelta a Escaneo.');

            return redirect()->route('gacetas.index')->with('warning', 'La gaceta fue rechazada y devuelta al digitalizador.');
        }

        // Fusión de PDF
        $pdfSumarioPath = storage_path('app/SGCJ/temp_sumario_' . $gaceta->id . '.pdf');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gacetas.pdf_sumario', compact('gaceta'));
        $pdf->save($pdfSumarioPath);

        $uploadedPdfPath = storage_path('app/SGCJ/' . $gaceta->ruta_archivo);

        if (!file_exists($uploadedPdfPath)) {
            return back()->with('error', 'El archivo físico temporal no se encuentra.');
        }

        $fpdi = new \setasign\Fpdi\Fpdi();

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

        $outputDir = public_path('gacetas_pdf');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $fileName = 'gaceta_' . $gaceta->anio . '_' . str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) . '.pdf';
        $outputPath = $outputDir . '/' . $fileName;
        
        $fpdi->Output($outputPath, 'F');

        // Clean up temps
        if (file_exists($pdfSumarioPath)) unlink($pdfSumarioPath);
        if (file_exists($uploadedPdfPath)) unlink($uploadedPdfPath);

        $gaceta->ruta_archivo = $fileName;
        $gaceta->estado = 'Publicada';
        $gaceta->fecha_publicacion = \Carbon\Carbon::now();
        $gaceta->save();

        activity()
            ->useLog('BPM_Gacetas')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Gaceta Aprobada y Publicada con éxito');

        return redirect()->route('gacetas.index')->with('success', 'Gaceta aprobada y publicada con éxito.');
    }

    public function panelDigitalizador()
    {
        $gacetas = Gaceta::where('digitalizador_id', auth()->id())
                         ->whereIn('estado', ['En Escaneo'])
                         ->orderBy('created_at', 'desc')
                         ->get();

        return view('gacetas.panel_digitalizador', compact('gacetas'));
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
