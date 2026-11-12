<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Gaceta;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Notifications\NuevaSolicitudNotification;
use App\Notifications\GacetaAsignadaNotification;
use App\Notifications\GacetaPorAprobarNotification;
use App\Services\GacetaPdfService;
use Illuminate\Support\Facades\Notification;

class GacetaController extends Controller
{
    public function __construct(
        private readonly GacetaPdfService $pdfService
    ) {
    }

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

        if ($request->filled('numero')) { $query->where('numero', 'ILIKE', "%{$request->numero}%"); }
        if ($request->filled('anio') && is_numeric($request->anio)) { $query->whereYear('fecha_publicacion', (int) $request->anio); }
        if ($request->filled('tipo')) { $query->where('tipo', $request->tipo); }

        // Búsqueda profunda en la relación Solicitudes (Institución)
        if ($request->filled('institucion_id') && is_numeric($request->institucion_id)) {
            $query->whereHas('solicitudes', function($q) use ($request) {
                $q->where('institucion_id', (int) $request->institucion_id);
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
                $q->where('sumario', 'ILIKE', "%{$request->keyword}%")
                  ->orWhere('numero', 'ILIKE', "%{$request->keyword}%");
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
        $uniqueRule = \Illuminate\Support\Facades\Schema::hasColumn('gacetas', 'deleted_at')
            ? Rule::unique('gacetas', 'numero')->whereNull('deleted_at')
            : Rule::unique('gacetas', 'numero');

        $request->validate([
            'numero'                => ['required', 'integer', 'min:1', $uniqueRule],
            'tipo'                  => 'required|in:Ordinaria,Extraordinaria',
            'ano_politico'          => 'required|string|max:100',
            'mes_politico'          => 'required|string|max:100',
            'fecha_publicacion'     => 'required|date',
            'gobernador_id'         => 'required|exists:gobernadores,id',
            'digitalizador_id'      => 'required|exists:users,id',
            'apartados_ordenados'   => 'required|array|min:1',
            'apartados_ordenados.*' => 'required|integer|exists:solicitudes,id',
        ]);

        try {
            DB::beginTransaction();

            $gaceta = new Gaceta();
            $gaceta->numero           = $request->numero;
            $gaceta->tipo             = $request->tipo;
            $gaceta->ano_politico     = $request->ano_politico;
            $gaceta->mes_politico     = $request->mes_politico;
            $gaceta->fecha_publicacion = $request->fecha_publicacion;
            $gaceta->gobernador_id    = $request->gobernador_id;
            $gaceta->digitalizador_id = $request->digitalizador_id;
            $gaceta->estado           = Gaceta::ESTADO_EN_PROCESO;
            $gaceta->anio             = date('Y', strtotime($request->fecha_publicacion));
            $gaceta->save();

            // Persistir cada apartado con su posición exacta tal como el usuario la definió.
            // El array apartados_ordenados[] viene en el orden visual del drag & drop.
            foreach ($request->apartados_ordenados as $posicion => $solicitudId) {
                \App\Models\Solicitud::where('id', $solicitudId)->update([
                    'gaceta_id'      => $gaceta->id,
                    'estado'         => Gaceta::ESTADO_EN_DIGITALIZACION,
                    'orden_sumario'  => $posicion + 1, // 1-based: posición 0 → orden 1
                ]);
            }

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
            'anio' => 'required|integer|min:1900|max:2100',
            'gobernador_id' => 'required|exists:gobernadores,id',
            'anio_politico' => 'required|string|max:100',
            'ano_politico' => 'nullable|string|max:100',
            'mes_politico' => 'required|string|max:100',
            'fecha_recepcion_fisica' => 'required|date',
        ]);

        $gaceta->checklist = $request->checklist;
        $gaceta->tipo = $request->tipo;
        $gaceta->anio = $request->anio;
        $gaceta->gobernador_id = $request->gobernador_id;
        $gaceta->ano_politico = $request->input('anio_politico', $request->input('ano_politico'));
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
            'fecha_recepcion_fisica' => 'nullable|date',
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
        $gaceta = Gaceta::with([
            'gobernador.titulo',
            'solicitudes.institucion',
            'solicitudes.actos',
        ])->findOrFail($id);

        return view('gacetas.upload_pdf', compact('gaceta'));
    }

    /**
     * Sirve el PDF de un acto administrativo para la vista previa.
     * Se entrega vía controlador para no depender del enlace público de storage.
     */
    public function verPdfActo(string $id, string $actoId)
    {
        $gaceta = Gaceta::findOrFail($id);

        $acto = \App\Models\ActoAdministrativo::whereHas('solicitud', function ($query) use ($gaceta) {
            $query->where('gaceta_id', $gaceta->id);
        })->findOrFail($actoId);

        $storage = \Illuminate\Support\Facades\Storage::disk('public');

        if (empty($acto->archivo_pdf) || !$storage->exists($acto->archivo_pdf)) {
            abort(404, 'El PDF del acto no está disponible en el servidor.');
        }

        return response()->file($storage->path($acto->archivo_pdf), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="acto_' . $acto->id . '.pdf"',
        ]);
    }

    /**
     * Muestra el editor para recortar las hojas del PDF de un acto administrativo.
     */
    public function mostrarRecortePdf(string $id, string $actoId)
    {
        $gaceta = Gaceta::findOrFail($id);

        $acto = \App\Models\ActoAdministrativo::whereHas('solicitud', function ($query) use ($gaceta) {
            $query->where('gaceta_id', $gaceta->id);
        })->findOrFail($actoId);

        $storage = \Illuminate\Support\Facades\Storage::disk('public');

        if (empty($acto->archivo_pdf) || !$storage->exists($acto->archivo_pdf)) {
            return redirect()
                ->route('gacetas.upload_pdf', $gaceta->id)
                ->with('error', 'El acto no tiene un PDF disponible para recortar.');
        }

        return view('gacetas.recortar_pdf', [
            'gaceta' => $gaceta,
            'acto' => $acto,
            'pdfUrl' => route('gacetas.actos.pdf.ver', [$gaceta->id, $acto->id], false),
            'tamanoMb' => round($storage->size($acto->archivo_pdf) / 1048576, 2),
        ]);
    }

    /**
     * Aplica el recorte solicitado (hojas eliminadas y zonas recortadas).
     */
    public function guardarRecortePdf(
        Request $request,
        string $id,
        string $actoId,
        \App\Services\RecortePdfService $recorteService
    ) {
        $gaceta = Gaceta::findOrFail($id);

        $acto = \App\Models\ActoAdministrativo::whereHas('solicitud', function ($query) use ($gaceta) {
            $query->where('gaceta_id', $gaceta->id);
        })->findOrFail($actoId);

        $request->validate([
            'paginas' => 'required|string',
        ], [
            'paginas.required' => 'Debe conservar al menos una hoja del PDF.',
        ]);

        $paginas = json_decode((string) $request->input('paginas'), true);

        if (!is_array($paginas) || empty($paginas)) {
            return redirect()
                ->route('gacetas.actos.recortar', [$gaceta->id, $acto->id])
                ->with('error', 'No se recibió la configuración del recorte. Intente de nuevo.');
        }

        $storage = \Illuminate\Support\Facades\Storage::disk('public');

        if (empty($acto->archivo_pdf) || !$storage->exists($acto->archivo_pdf)) {
            return redirect()
                ->route('gacetas.upload_pdf', $gaceta->id)
                ->with('error', 'El PDF del acto ya no está disponible en el servidor.');
        }

        $rutaActual = $acto->archivo_pdf;
        $directorio = dirname($rutaActual);
        $nombreBase = pathinfo($rutaActual, PATHINFO_FILENAME);
        $rutaTemporal = $directorio . '/.recorte_' . $nombreBase . '_' . uniqid() . '.pdf';

        try {
            $hojasConservadas = $recorteService->recortar(
                $storage->path($rutaActual),
                $paginas,
                $storage->path($rutaTemporal)
            );
        } catch (\Throwable $e) {
            // Limpiar el temporal si alcanzó a crearse.
            if ($storage->exists($rutaTemporal)) {
                $storage->delete($rutaTemporal);
            }

            return redirect()
                ->route('gacetas.actos.recortar', [$gaceta->id, $acto->id])
                ->with('error', 'No se pudo recortar el PDF: ' . $e->getMessage());
        }

        // Respaldo del original antes de reemplazarlo.
        $rutaRespaldo = $directorio . '/respaldos/' . $nombreBase . '_' . date('Ymd_His') . '.pdf';

        if (!$storage->copy($rutaActual, $rutaRespaldo)) {
            if ($storage->exists($rutaTemporal)) {
                $storage->delete($rutaTemporal);
            }

            return redirect()
                ->route('gacetas.actos.recortar', [$gaceta->id, $acto->id])
                ->with('error', 'No se pudo crear el respaldo del PDF original. No se aplicaron cambios.');
        }

        // Reemplazar conservando la misma ruta registrada en la base de datos.
        $storage->delete($rutaActual);

        if (!$storage->move($rutaTemporal, $rutaActual)) {
            // Restaurar el original desde el respaldo si el reemplazo falla.
            $storage->copy($rutaRespaldo, $rutaActual);

            if ($storage->exists($rutaTemporal)) {
                $storage->delete($rutaTemporal);
            }

            return redirect()
                ->route('gacetas.actos.recortar', [$gaceta->id, $acto->id])
                ->with('error', 'No se pudo reemplazar el PDF del acto. Se restauró el archivo original.');
        }

        $this->invalidarPdfConsolidado($gaceta);

        activity()
            ->useLog('BPM_Gacetas')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log("PDF del acto {$acto->tipo_acto} {$acto->numero} recortado: {$hojasConservadas} hoja(s) conservada(s).");

        return redirect()
            ->route('gacetas.upload_pdf', $gaceta->id)
            ->with('success', "PDF del acto {$acto->tipo_acto} N° {$acto->numero} recortado. Se conservaron {$hojasConservadas} hoja(s) y se guardó un respaldo del original.");
    }

    /**
     * Sirve el PDF consolidado de la Gaceta para la vista previa incrustada.
     * Se entrega vía controlador para no depender del enlace público de storage.
     */
    public function verPdf(string $id)
    {
        $gaceta = Gaceta::with([
            'gobernador.titulo',
            'solicitudes.institucion',
            'solicitudes.actos',
        ])->findOrFail($id);

        $ruta = $this->pdfService->obtenerRutaVistaPrevia($gaceta);

        if (!$ruta || !is_file($ruta)) {
            abort(404, 'El PDF de esta Gaceta no está disponible en el servidor.');
        }

        return response()->file($ruta, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="gaceta_' . $gaceta->numero . '.pdf"',
        ]);
    }

    /**
     * Asigna (o reemplaza) el PDF escaneado de un acto administrativo.
     */
    public function subirPdfActo(Request $request, string $id, string $actoId)
    {
        $gaceta = Gaceta::findOrFail($id);

        $acto = \App\Models\ActoAdministrativo::whereHas('solicitud', function ($query) use ($gaceta) {
            $query->where('gaceta_id', $gaceta->id);
        })->findOrFail($actoId);

        $request->validate([
            'archivo_pdf' => 'required|mimes:pdf|max:' . config('sgcj.archivos.max_pdf_kb'),
        ]);

        // Reemplazar el archivo anterior si existía.
        if ($acto->archivo_pdf && \Illuminate\Support\Facades\Storage::disk('public')->exists($acto->archivo_pdf)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($acto->archivo_pdf);
        }

        $path = $request->file('archivo_pdf')->store('gacetas/actos/' . $gaceta->id, 'public');

        $acto->update(['archivo_pdf' => $path]);

        $this->invalidarPdfConsolidado($gaceta);

        activity()
            ->useLog('BPM_Gacetas')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log("PDF del acto {$acto->tipo_acto} {$acto->numero} asignado por el digitalizador.");

        return back()->with('success', "PDF asignado al acto {$acto->tipo_acto} N° {$acto->numero}.");
    }

    /**
     * Asigna varios PDFs de actos administrativos en una sola operación.
     */
    public function subirPdfsActos(Request $request, string $id)
    {
        $gaceta = Gaceta::findOrFail($id);

        $archivos = collect($request->file('archivos', []))
            ->filter(fn($archivo) => $archivo instanceof \Illuminate\Http\UploadedFile && $archivo->isValid());

        if ($archivos->isEmpty()) {
            return back()->with('error', 'Seleccione al menos un archivo PDF para subir.');
        }

        $maxPdfMb = intdiv((int) config('sgcj.archivos.max_pdf_kb'), 1024);

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['archivos' => $archivos->all()],
            [
                'archivos' => 'required|array',
                'archivos.*' => 'file|mimes:pdf|max:' . config('sgcj.archivos.max_pdf_kb'),
            ],
            [
                'archivos.*.mimes' => 'Todos los archivos deben ser PDF.',
                'archivos.*.max' => "Cada PDF debe pesar como máximo {$maxPdfMb} MB.",
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $actos = \App\Models\ActoAdministrativo::whereHas('solicitud', function ($query) use ($gaceta) {
            $query->where('gaceta_id', $gaceta->id);
        })->get()->keyBy('id');

        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        $subidos = 0;

        foreach ($archivos as $actoId => $archivo) {
            $acto = $actos->get((int) $actoId);

            if (!$acto) {
                continue;
            }

            if ($acto->archivo_pdf && $storage->exists($acto->archivo_pdf)) {
                $storage->delete($acto->archivo_pdf);
            }

            $path = $archivo->store('gacetas/actos/' . $gaceta->id, 'public');
            $acto->update(['archivo_pdf' => $path]);
            $subidos++;
        }

        if ($subidos === 0) {
            return back()->with('error', 'No se pudo asignar ningún PDF. Verifique los actos seleccionados.');
        }

        $this->invalidarPdfConsolidado($gaceta);

        activity()
            ->useLog('BPM_Gacetas')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log("Se asignaron {$subidos} PDF(s) a los actos administrativos.");

        return back()->with('success', "Se asignaron {$subidos} PDF(s) a los actos administrativos.");
    }

    /**
     * Quita el PDF asignado a un acto administrativo.
     */
    public function eliminarPdfActo(string $id, string $actoId)
    {
        $gaceta = Gaceta::findOrFail($id);

        $acto = \App\Models\ActoAdministrativo::whereHas('solicitud', function ($query) use ($gaceta) {
            $query->where('gaceta_id', $gaceta->id);
        })->findOrFail($actoId);

        if ($acto->archivo_pdf && \Illuminate\Support\Facades\Storage::disk('public')->exists($acto->archivo_pdf)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($acto->archivo_pdf);
        }

        $acto->update(['archivo_pdf' => null]);

        $this->invalidarPdfConsolidado($gaceta);

        return back()->with('success', "PDF del acto {$acto->tipo_acto} N° {$acto->numero} eliminado.");
    }

    /**
     * Elimina el PDF consolidado generado para que la próxima vista previa
     * refleje los archivos de actos recién subidos o eliminados.
     */
    private function invalidarPdfConsolidado(Gaceta $gaceta): void
    {
        if ($gaceta->estado === Gaceta::ESTADO_PUBLICADA) {
            return;
        }

        $storage = \Illuminate\Support\Facades\Storage::disk('public');

        if ($gaceta->archivo_pdf_completo && $storage->exists($gaceta->archivo_pdf_completo)) {
            $storage->delete($gaceta->archivo_pdf_completo);
        }

        if ($gaceta->archivo_pdf_completo !== null) {
            $gaceta->forceFill(['archivo_pdf_completo' => null])->save();
        }
    }

    public function savePdf(Request $request, string $id)
    {
        $request->validate([
            'ruta_archivo' => 'required|mimes:pdf|max:' . config('sgcj.archivos.max_pdf_kb'),
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

    public function preview(string $id)
    {
        $gaceta = Gaceta::findOrFail($id);
        $tempPath = $this->pdfService->generarPreviewTemporal($gaceta);

        return response()->file($tempPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="previsualizacion_gaceta_' . $gaceta->numero . '.pdf"',
        ]);
    }

    /**
     * Descarga el PDF final (sumario + actos) de una Gaceta publicada.
     */
    public function descargarPdf(string $id)
    {
        $gaceta = Gaceta::with(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos'])
            ->findOrFail($id);

        $rutaDescarga = $this->pdfService->obtenerRutaDescarga($gaceta);

        if (!$rutaDescarga) {
            abort(404, 'El PDF final de esta Gaceta no está disponible.');
        }

        $nombre = 'Gaceta_Oficial_' . $this->pdfService->slugArchivo((string) $gaceta->numero) . '_' . $gaceta->anio . '.pdf';

        return response()->download($rutaDescarga, $nombre);
    }

    public function enviarARevision(Request $request, string $id)
    {
        $gaceta = Gaceta::with(['solicitudes.actos'])->findOrFail($id);

        $request->validate([
            'archivo_pdf_completo' => 'nullable|mimes:pdf|max:' . config('sgcj.archivos.max_pdf_kb')
        ]);

        $actos = $gaceta->solicitudes->flatMap(fn($solicitud) => $solicitud->actos);
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        $actosSinPdf = $actos->filter(
            fn($acto) => empty($acto->archivo_pdf) || !$storage->exists($acto->archivo_pdf)
        );

        // Si ya existía un PDF maestro de un intento anterior, se descarta.
        if ($gaceta->archivo_pdf_completo && \Illuminate\Support\Facades\Storage::disk('public')->exists($gaceta->archivo_pdf_completo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($gaceta->archivo_pdf_completo);
        }

        // 1. Si el digitalizador sube el PDF maestro, se usa ese archivo.
        if ($request->hasFile('archivo_pdf_completo')) {
            $path = $request->file('archivo_pdf_completo')->store('gacetas/revision', 'public');
        }
        // 2. Si no hay PDF maestro pero todos los actos tienen su PDF, se consolidan.
        elseif ($actos->isNotEmpty() && $actosSinPdf->isEmpty()) {
            try {
                $path = $this->pdfService->mergeActPdfs($gaceta);
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'No se pudieron consolidar los PDFs de los actos: ' . $e->getMessage());
            }
        }
        // 3. Faltan archivos: no se puede enviar a revisión.
        else {
            $faltantes = $actosSinPdf
                ->map(fn($acto) => trim(($acto->tipo_acto ?? 'Acto') . ' ' . ($acto->numero ?? '')))
                ->filter()
                ->values();

            $mensaje = $faltantes->isNotEmpty()
                ? 'Asigne el PDF a cada acto antes de enviar a revisión. Faltan: ' . $faltantes->implode(', ')
                : 'Debe cargar el PDF de cada acto administrativo antes de enviar a revisión.';

            return back()->withInput()->with('error', $mensaje);
        }

        // Actualizar la Gaceta a estado de revisión
        $gaceta->update([
            'archivo_pdf_completo' => $path,
            'estado' => Gaceta::ESTADO_REVISION_FINAL
        ]);

        return redirect()->route('gacetas.digitalizador')->with('success', '¡Gaceta enviada a Jefatura para revisión final!');
    }

    public function revisarPdfFinal(string $id)
    {
        $gaceta = Gaceta::with([
            'gobernador.titulo',
            'solicitudes.institucion',
            'solicitudes.actos',
        ])->findOrFail($id);
        $pdfDisponible = $this->pdfService->tienePdfDisponible($gaceta);

        return view('gacetas.revision', compact('gaceta', 'pdfDisponible'));
    }

    public function publicarGaceta(Request $request, string $id)
    {
        if ($request->input('accion') === 'rechazar') {
            return $this->rechazarEscaneo($request, $id);
        }

        $gaceta = Gaceta::with(['gobernador.titulo', 'solicitudes.institucion', 'solicitudes.actos'])->findOrFail($id);

        $storage = \Illuminate\Support\Facades\Storage::disk('public');

        // 1. Asegurar el PDF de los actos (consolidado o escaneo físico).
        $actsPath = $gaceta->archivo_pdf_completo;

        if (!$actsPath || !$storage->exists($actsPath)) {
            $actos = $gaceta->solicitudes->flatMap(fn($solicitud) => $solicitud->actos);

            if ($actos->isNotEmpty() && $actos->every(fn($acto) => !empty($acto->archivo_pdf) && $storage->exists($acto->archivo_pdf))) {
                $actsPath = $this->pdfService->mergeActPdfs($gaceta);
            }
        }

        // 2. Generar el PDF final (sumario + actos) antes de mover nada,
        //    para no dejar archivos huérfanos si algo falla.
        $gaceta->archivo_pdf_completo = $actsPath;

        try {
            $pdfFinal = $this->pdfService->generarPdfFinal($gaceta);
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo generar el PDF final: ' . $e->getMessage());
        }

    // 3. Mover el PDF de los actos a la carpeta de publicadas.
    if ($actsPath && str_starts_with($actsPath, 'gacetas/revision/') && $storage->exists($actsPath)) {
        $nuevoPath = str_replace('gacetas/revision/', 'gacetas/publicadas/', $actsPath);
        $storage->move($actsPath, $nuevoPath);
        $actsPath = $nuevoPath;
    }

    $gaceta->update([
        'archivo_pdf_completo' => $actsPath,
        'pdf_final' => $pdfFinal,
        'estado' => Gaceta::ESTADO_PUBLICADA
    ]);

    // CASCADA DE ESTADOS ATÓMICA
    $gaceta->solicitudes()->update(['estado' => 'Publicada']);

    // La meta del POA se incrementa automáticamente una sola vez mediante GacetaObserver@updated

    // Notificar a las instituciones
    $institucionesIds = $gaceta->solicitudes->pluck('institucion_id')->unique();
    $usuariosNotificar = \App\Models\User::whereIn('institucion_id', $institucionesIds)->get();

    if ($usuariosNotificar->isNotEmpty()) {
        \Illuminate\Support\Facades\Notification::send($usuariosNotificar, new \App\Notifications\GacetaPublicadaNotification($gaceta));
    }

    return redirect()->route('gacetas.show', $gaceta->id)
        ->with('success', '¡Gaceta Oficial publicada con éxito!')
        ->with('descargar_pdf', true);
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

    if ($gaceta->pdf_final && \Illuminate\Support\Facades\Storage::disk('public')->exists($gaceta->pdf_final)) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($gaceta->pdf_final);
    }

    $gaceta->update([
        'archivo_pdf_completo' => null,
        'pdf_final' => null,
        'estado' => Gaceta::ESTADO_DEVUELTA_DIGITALIZADOR,
        'motivo_devolucion' => $request->motivo_rechazo_escaneo
    ]);

    return redirect()->route('gacetas.index')->with('error', 'Gaceta devuelta al Digitalizador por errores en el escaneo.');
}

public function subsanarGaceta(Request $request, string $id)
{
    $gaceta = Gaceta::findOrFail($id);

    $uniqueRule = \Illuminate\Support\Facades\Schema::hasColumn('gacetas', 'deleted_at')
        ? Rule::unique('gacetas', 'numero')->ignore($gaceta->id)->whereNull('deleted_at')
        : Rule::unique('gacetas', 'numero')->ignore($gaceta->id);

    $request->validate([
        'numero' => ['required', 'integer', 'min:1', $uniqueRule],
        'tipo' => 'required|in:Ordinaria,Extraordinaria',
        'ano_politico' => 'required|string|max:100',
        'mes_politico' => 'required|string|max:100',
        'fecha_publicacion' => 'required|date',
        'gobernador_id' => 'required|exists:gobernadores,id',
        'digitalizador_id' => 'required|exists:users,id',
        'apartados' => 'required|array|min:1',
        'apartados.*' => 'integer|exists:solicitudes,id',
    ]);
    
    $gaceta->update([
        'numero' => $request->numero,
        'anio' => \Carbon\Carbon::parse($request->fecha_publicacion)->year,
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
        $gaceta = Gaceta::with([
            'gobernador.titulo',
            'solicitudes.institucion',
            'solicitudes.actos',
            'corregidaDe',
            'correcciones'
        ])->findOrFail($id);
        $pdfDisponible = $this->pdfService->tienePdfDisponible($gaceta);

        return view('gacetas.show', compact('gaceta', 'pdfDisponible'));
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
