<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gaceta;
use App\Models\SumarioGaceta;
use App\Models\Institucion;
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
        $gacetas = \App\Models\Gaceta::with(['digitalizador', 'solicitudes'])->latest()->get();
        $digitalizadores = \App\Models\User::role('Digitalizador')->get(); 
        return view('gacetas.index', compact('gacetas', 'digitalizadores'));
    }

    public function publicadas(Request $request)
    {
        $institutions = \App\Models\Institucion::all();
        // Cargamos relaciones para evitar N+1
        $query = \App\Models\Gaceta::with(['solicitudes.institucion', 'solicitudes.actos'])->where('estado', Gaceta::ESTADO_PUBLICADA);

        if ($request->filled('numero')) { $query->where('numero', 'LIKE', "%{$request->numero}%"); }
        if ($request->filled('anio')) { $query->whereYear('fecha_publicacion', $request->anio); }
        if ($request->filled('tipo')) { $query->where('tipo', $request->tipo); }

        // Búsqueda profunda en la relación Solicitudes (Institución)
        if ($request->filled('institucion_id')) {
            $query->whereHas('solicitudes', function($q) use ($request) {
                $q->where('institucion_id', $request->institucion_id);
            });
        }

        // Búsqueda profunda en la relación Actos (Tipo y Sumario)
        if ($request->filled('tipo_acto')) {
            $query->whereHas('solicitudes.actos', function($q) use ($request) {
                $q->where('tipo_acto', $request->tipo_acto);
            });
        }
        if ($request->filled('keyword')) {
            $query->whereHas('solicitudes.actos', function($q) use ($request) {
                $q->where('sumario', 'LIKE', "%{$request->keyword}%")
                  ->orWhere('numero_acto', 'LIKE', "%{$request->keyword}%");
            });
        }

        $gacetas = $query->latest('fecha_publicacion')->paginate(15);
        return view('gacetas.publicadas', compact('gacetas', 'institutions'));
    }

    public function create()
    {
        $apartadosAprobados = \App\Models\Solicitud::with('institucion')
            ->where('estado', 'Aprobada')
            ->whereNull('gaceta_id')
            ->get();
            
        $gobernadores = \App\Models\Gobernador::with('titulo')->get();
        $digitalizadores = \App\Models\User::role('Digitalizador')->get();

        return view('gacetas.create', compact('apartadosAprobados', 'gobernadores', 'digitalizadores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:255',
            'tipo' => 'required|in:Ordinaria,Extraordinaria',
            'ano_politico' => 'required|string',
            'mes_politico' => 'required|string',
            'fecha_publicacion' => 'required|date',
            'gobernador_id' => 'required|exists:gobernadores,id',
            'digitalizador_id' => 'required|exists:users,id',
            'apartados' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $gaceta = new Gaceta();
            $gaceta->numero = $request->numero;
            $gaceta->tipo = $request->tipo;
            $gaceta->ano_politico = $request->ano_politico;
            $gaceta->mes_politico = $request->mes_politico;
            $gaceta->fecha_publicacion = $request->fecha_publicacion;
            $gaceta->gobernador_id = $request->gobernador_id;
            $gaceta->digitalizador_id = $request->digitalizador_id;
            $gaceta->estado = Gaceta::ESTADO_EN_PROCESO;
            $gaceta->anio = date('Y', strtotime($request->fecha_publicacion));
            $gaceta->save();

            \App\Models\Solicitud::whereIn('id', $request->apartados)
                ->update([
                    'gaceta_id' => $gaceta->id,
                    'estado' => Gaceta::ESTADO_EN_DIGITALIZACION
                ]);

            DB::commit();

            return redirect()->route('gacetas.index')->with('success', 'La Gaceta ha sido ensamblada exitosamente y enviada al proceso de digitalización.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al ensamblar gaceta: ' . $e->getMessage());
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
            $gaceta->estado = Gaceta::ESTADO_RESERVADA;
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
        $gaceta->estado = Gaceta::ESTADO_EN_ESCANEO;
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

    public function reasignar(Request $request, Gaceta $gaceta)
    {
        $request->validate(['digitalizador_id' => 'required|exists:users,id']);
        $gaceta->update([
            'digitalizador_id' => $request->digitalizador_id,
            'estado' => Gaceta::ESTADO_EN_PROCESO
        ]);
        return back()->with('success', 'Gaceta reasignada correctamente.');
    }

    public function devolver(Request $request, Gaceta $gaceta)
    {
        $request->validate(['motivo_devolucion' => 'required|string|min:5']);
        $gaceta->update([
            'estado' => Gaceta::ESTADO_DEVUELTA,
            'motivo_devolucion' => $request->motivo_devolucion
        ]);
        return redirect()->route('gacetas.digitalizador')->with('error', 'Gaceta devuelta a Jefatura.');
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
            $gaceta->estado = Gaceta::ESTADO_POR_APROBAR;
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

    // 1. Método nuevo para previsualizar (sin guardar el archivo final)
public function preview(string $id)
{
    $gaceta = Gaceta::findOrFail($id);
    // Usamos nuestra lógica compartida para generar el PDF fusionado temporal
    $tempPath = $this->mergePdfs($gaceta, false); // false = no guardar permanentemente

    return response()->file($tempPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="previsualizacion_gaceta_'.$gaceta->numero.'.pdf"'
    ]);
}

// 2. Extraemos la lógica de Fusión en un método privado
private function mergePdfs(Gaceta $gaceta, $isFinal = false)
{
    $gaceta->load(['gobernador.titulo', 'solicitudes.institucion']);

    $pdfSumarioPath = storage_path('app/SGCJ/temp_sumario_' . $gaceta->id . '.pdf');
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gacetas.pdf_sumario', compact('gaceta'));
    $pdf->save($pdfSumarioPath);

    $uploadedPdfPath = storage_path('app/SGCJ/' . $gaceta->ruta_archivo);

    if (!file_exists($uploadedPdfPath)) {
        throw new \Exception('El archivo físico no se encuentra.');
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

    $tempOutputPath = storage_path('app/SGCJ/preview_' . $gaceta->id . '.pdf');
    $fpdi->Output($tempOutputPath, 'F');

    // Limpiamos el sumario temporal, pero el preview lo dejamos para que el navegador lo lea
    if (file_exists($pdfSumarioPath)) unlink($pdfSumarioPath);

    return $tempOutputPath;
}

public function enviarARevision(Request $request, string $id)
{
    $gaceta = Gaceta::findOrFail($id);

    $request->validate([
        'archivo_pdf_completo' => 'required|mimes:pdf|max:20480' // Máximo 20MB
    ]);

    // Guardar el PDF temporalmente en el disco público
    $path = $request->file('archivo_pdf_completo')->store('gacetas/revision', 'public');

    // Actualizar la Gaceta a estado de revisión
    $gaceta->update([
        'archivo_pdf_completo' => $path,
        'estado' => Gaceta::ESTADO_REVISION_FINAL
    ]);

    return redirect()->route('gacetas.digitalizador')->with('success', '¡Gaceta enviada a Jefatura para revisión final!');
}

public function revisarPdfFinal(string $id)
{
    $gaceta = Gaceta::findOrFail($id);
    return view('gacetas.revision', compact('gaceta'));
}

public function publicarGaceta(Request $request, string $id)
{
    $gaceta = Gaceta::findOrFail($id);

    // Mover el archivo a la carpeta de publicadas
    $oldPath = $gaceta->archivo_pdf_completo;
    $newPath = str_replace('gacetas/revision/', 'gacetas/publicadas/', $oldPath);
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
        \Illuminate\Support\Facades\Storage::disk('public')->move($oldPath, $newPath);
    } else {
        $newPath = $oldPath; // fallback si algo pasa
    }

    $gaceta->update([
        'archivo_pdf_completo' => $newPath,
        'estado' => Gaceta::ESTADO_PUBLICADA
    ]);

    // CASCADA DE ESTADOS ATÓMICA
    $gaceta->solicitudes()->update(['estado' => 'Publicada']);

    // Notificar a las instituciones
    $institucionesIds = $gaceta->solicitudes->pluck('institucion_id')->unique();
    $usuariosNotificar = \App\Models\User::whereIn('institucion_id', $institucionesIds)->get();
    
    if($usuariosNotificar->isNotEmpty()){
        \Illuminate\Support\Facades\Notification::send($usuariosNotificar, new \App\Notifications\GacetaPublicadaNotification($gaceta));
    }

    return redirect()->route('gacetas.index')->with('success', '¡Gaceta Oficial publicada con éxito!');
}

public function rechazarEscaneo(Request $request, string $id)
{
    $gaceta = Gaceta::findOrFail($id);
    
    $request->validate([
        'motivo_rechazo_escaneo' => 'required|string|min:5'
    ]);

    // Opcional: eliminar el PDF en revisión
    if ($gaceta->archivo_pdf_completo && \Illuminate\Support\Facades\Storage::disk('public')->exists($gaceta->archivo_pdf_completo)) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($gaceta->archivo_pdf_completo);
    }

    $gaceta->update([
        'archivo_pdf_completo' => null,
        'estado' => Gaceta::ESTADO_DEVUELTA_DIGITALIZADOR,
        'motivo_devolucion' => $request->motivo_rechazo_escaneo
    ]);

    return redirect()->route('gacetas.index')->with('error', 'Gaceta devuelta al Digitalizador por errores en el escaneo.');
}

public function subsanarGaceta(Request $request, string $id)
{
    $request->validate([
        'numero' => 'required|string|max:255',
        'tipo' => 'required|in:Ordinaria,Extraordinaria',
        'ano_politico' => 'required|string',
        'mes_politico' => 'required|string',
        'fecha_publicacion' => 'required|date',
        'gobernador_id' => 'required|exists:gobernadores,id',
        'digitalizador_id' => 'required|exists:users,id',
        'apartados' => 'required|array|min:1',
    ]);

    $gaceta = Gaceta::findOrFail($id);
    
    $gaceta->update([
        'numero' => $request->numero,
        'tipo' => $request->tipo,
        'ano_politico' => $request->ano_politico,
        'mes_politico' => $request->mes_politico,
        'fecha_publicacion' => $request->fecha_publicacion,
        'gobernador_id' => $request->gobernador_id,
        'digitalizador_id' => $request->digitalizador_id ?? $gaceta->digitalizador_id,
        'estado' => Gaceta::ESTADO_EN_PROCESO,
        'motivo_devolucion' => null
    ]);

    // Desvincular apartados que fueron desmarcados
    \App\Models\Solicitud::where('gaceta_id', $gaceta->id)->update([
        'gaceta_id' => null,
        'estado' => 'Aprobada'
    ]);

    // Vincular los nuevos apartados marcados
    \App\Models\Solicitud::whereIn('id', $request->apartados)->update([
        'gaceta_id' => $gaceta->id,
        'estado' => Gaceta::ESTADO_EN_DIGITALIZACION // O el estado que corresponda al apartado una vez ensamblado
    ]);

    return redirect()->route('gacetas.index')->with('success', 'Gaceta subsanada y enviada a Digitalización.');
}

    

    public function panelDigitalizador()
    {
        $gacetas = \App\Models\Gaceta::with('solicitudes')
            ->where('digitalizador_id', auth()->id())
            ->whereIn('estado', [Gaceta::ESTADO_EN_PROCESO, Gaceta::ESTADO_EN_DIGITALIZACION, Gaceta::ESTADO_DEVUELTA_DIGITALIZADOR])
            ->latest()
            ->get();
            
        return view('gacetas.panel_digitalizador', compact('gacetas'));
    }

    public function show(string $id)
    {
        $gaceta = Gaceta::with(['solicitudes.institucion', 'corregidaDe', 'correcciones'])->findOrFail($id);
        return view('gacetas.show', compact('gaceta'));
    }

    public function edit(string $id) 
    { 
        $gaceta = Gaceta::with('solicitudes')->findOrFail($id);
        $gobernadores = \App\Models\Gobernador::with('titulo')->get();
        $digitalizadores = \App\Models\User::role('Digitalizador')->get();
        
        $apartadosAprobados = \App\Models\Solicitud::with('institucion')
            ->where(function($query) use ($id) {
                $query->where('estado', 'Aprobada')->whereNull('gaceta_id')
                      ->orWhere('gaceta_id', $id);
            })
            ->get();
            
        $apartadosAsignadosIds = $gaceta->solicitudes->pluck('id')->toArray();

        return view('gacetas.edit', compact('gaceta', 'gobernadores', 'digitalizadores', 'apartadosAprobados', 'apartadosAsignadosIds'));
    }
    public function update(Request $request, string $id) { }
    public function destroy(string $id) { }


}
