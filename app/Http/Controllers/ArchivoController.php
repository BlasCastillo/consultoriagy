<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gaceta;
use App\Models\PrestamoArchivo;
use Barryvdh\DomPDF\Facade\Pdf;

class ArchivoController extends Controller
{

    public function index()
    {
        $sedes = \App\Models\Sede::with('estantes.gavetas')->get();
        $gacetas = Gaceta::with(['inventario.sede', 'inventario.estante', 'inventario.gaveta'])->where('estado', 'Publicada')->latest('fecha_publicacion')->get();
        $prestamosActivos = PrestamoArchivo::with('gaceta')->whereIn('estado_prestamo', ['Activo', 'Vencido'])->latest()->get();
        return view('archivo.index', compact('gacetas', 'prestamosActivos', 'sedes'));
    }

    public function crearRetroactiva()
    {
        $gobernadores = \App\Models\Gobernador::with('titulo')->get();
        $instituciones = \App\Models\Institucion::all();
        return view('archivo.retroactiva', compact('gobernadores', 'instituciones'));
    }

    public function guardarRetroactiva(Request $request)
    {
        $request->validate([
            'numero' => 'required|integer|min:1',
            'tipo' => 'required|in:Ordinaria,Extraordinaria',
            'ano_politico' => 'required|string|max:100',
            'mes_politico' => 'required|string|max:100',
            'fecha_publicacion' => 'required|date',
            'gobernador_id' => 'required|exists:gobernadores,id',
            'meta_trimestral_id' => 'nullable|integer|exists:meta_trimestrals,id',
            'archivo_pdf_completo' => 'required|mimes:pdf|max:20480',
            'actos' => 'required|array|min:1',
            'actos.*.institucion_id' => 'required|exists:instituciones,id',
            'actos.*.tipo_acto' => 'required|string|max:255',
            'actos.*.numero' => 'required|string|max:255',
            'actos.*.sumario' => 'required|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // 1. Guardar PDF
            $path = $request->file('archivo_pdf_completo')->store('gacetas/publicadas', 'public');

            // 2. Crear Gaceta Retroactiva
            $gaceta = \App\Models\Gaceta::create([
                'numero' => $request->numero,
                'anio' => \Carbon\Carbon::parse($request->fecha_publicacion)->year,
                'tipo' => $request->tipo,
                'ano_politico' => $request->ano_politico,
                'mes_politico' => $request->mes_politico,
                'fecha_publicacion' => $request->fecha_publicacion,
                'gobernador_id' => $request->gobernador_id,
                'digitalizador_id' => auth()->id(), // El jefe de archivo asume la autoría
                'archivo_pdf_completo' => $path,
                'estado' => 'Publicada',
                'es_retroactiva' => true,
                'ejemplares_fisicos' => 1 // Ingresa con stock físico
            ]);

            // 3. Crear Solicitudes Fantasma y sus Actos (sin disparar doble conteo en Observer)
            foreach ($request->actos as $actoData) {
                $solicitudFantasma = new \App\Models\Solicitud();
                $solicitudFantasma->institucion_id = $actoData['institucion_id'];
                $solicitudFantasma->gaceta_id = $gaceta->id;
                $solicitudFantasma->titulo_acto = 'Carga Histórica Retroactiva'; // Columna exigida por la BD
                $solicitudFantasma->estado = 'Publicada';
                $solicitudFantasma->save();

                \App\Models\ActoAdministrativo::withoutEvents(function () use ($solicitudFantasma, $actoData) {
                    $acto = new \App\Models\ActoAdministrativo();
                    $acto->solicitud_id = $solicitudFantasma->id;
                    $acto->tipo_acto = $actoData['tipo_acto'];
                    $acto->numero = $actoData['numero'];
                    $acto->sumario = $actoData['sumario'];
                    $acto->save();
                });
            }
            
            // Registrar en Bitácora
            activity()
                ->useLog('Modulo_Archivo')
                ->causedBy(auth()->user())
                ->performedOn($gaceta)
                ->log("Gaceta Histórica N° {$gaceta->numero} ingresada al sistema vía Fast-Track.");
        });

        // TRIGGER POA: Incremento único por carga histórica desde Kardex
        if ($request->filled('meta_trimestral_id')) {
            $meta = \App\Models\MetaTrimestral::with(['actividadPoa.poa' => fn($q) => $q->withoutGlobalScopes()])->find($request->meta_trimestral_id);
            if ($meta) {
                $meta->increment('ejecutada');
                if ($meta->actividadPoa && $meta->actividadPoa->poa) {
                    \App\Models\Poa::checkAndClose($meta->actividadPoa->poa);
                }
            }
        } else {
            $jefaturaArchivoId = (auth()->check() && auth()->user()->jefatura_id)
                ? auth()->user()->jefatura_id
                : \App\Models\Jefatura::where('nombre', 'ILIKE', '%Archivo%')->value('id');

            if ($jefaturaArchivoId) {
                $poa = \App\Models\Poa::withoutGlobalScopes()
                    ->where('anio', now()->year)
                    ->where('jefatura_id', $jefaturaArchivoId)
                    ->where('estado', '!=', 'Cerrado')
                    ->first();

                if ($poa) {
                    // Busca la actividad específica de carga histórica
                    $actividad = $poa->actividades()->where('descripcion', 'ILIKE', '%históricas%')->first();

                    if ($actividad) {
                        // Suma al trimestre actual, ya que el esfuerzo físico ocurre hoy
                        $meta = $actividad->metasTrimestrales()->where('trimestre', ceil(now()->month / 3))->first();

                        if ($meta) {
                            $meta->increment('ejecutada');
                            \App\Models\Poa::checkAndClose($poa);
                        }
                    }
                }
            }
        }

        return redirect()->route('archivo.index')->with('success', 'Gaceta histórica procesada e indexada correctamente.');
    }

    public function updateInventario(Request $request, Gaceta $gaceta)
    {
        $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'estante_id' => 'nullable|exists:estantes,id',
            'gaveta_id' => 'nullable|exists:gavetas,id',
            'ejemplares_fisicos' => 'required|integer|min:0',
        ]);

        \App\Models\InventarioArchivo::updateOrCreate(
            ['gaceta_id' => $gaceta->id],
            [
                'sede_id' => $request->sede_id,
                'estante_id' => $request->estante_id,
                'gaveta_id' => $request->gaveta_id,
                'stock' => $request->ejemplares_fisicos
            ]
        );

        $gaceta->update(['ejemplares_fisicos' => $request->ejemplares_fisicos]);

        activity()
            ->useLog('Modulo_Archivo')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Inventario logístico de la Gaceta N° ' . $gaceta->numero . ' actualizado.');

        return back()->with('success', 'Inventario actualizado');
    }

    public function storePrestamo(Request $request, Gaceta $gaceta)
    {
        $request->validate([
            'solicitante_cedula' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'solicitante_nombres' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'telefono' => ['nullable', 'string', 'max:50', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'correo' => ['nullable', 'email', 'max:255'],
            'motivo_uso' => ['required', 'string', 'max:1000'],
            'fecha_salida' => ['required', 'date'],
            'fecha_devolucion_estimada' => ['required', 'date', 'after_or_equal:fecha_salida'],
        ]);

        $tieneStockInventario = $gaceta->inventario && $gaceta->inventario->stock > 0;
        $tieneEjemplaresFisicos = ($gaceta->ejemplares_fisicos ?? 0) > 0;

        if (!$tieneStockInventario && !$tieneEjemplaresFisicos) {
            return back()->with('error', 'Stock insuficiente o no asignado.');
        }

        PrestamoArchivo::create([
            'gaceta_id' => $gaceta->id,
            'solicitante_cedula' => $request->solicitante_cedula,
            'solicitante_nombres' => $request->solicitante_nombres,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
            'motivo_uso' => $request->motivo_uso,
            'fecha_salida' => $request->fecha_salida,
            'fecha_devolucion_estimada' => $request->fecha_devolucion_estimada,
            'estado_prestamo' => 'Activo'
        ]);

        if ($gaceta->inventario && $gaceta->inventario->stock > 0) {
            $gaceta->inventario->decrement('stock', 1);
        }
        if (($gaceta->ejemplares_fisicos ?? 0) > 0) {
            $gaceta->decrement('ejemplares_fisicos', 1);
        }

        $usuariosANotificar = \App\Models\User::role(['Super Admin', 'Coordinador General', 'Director de Consultoría Jurídica'])->get();
        \Illuminate\Support\Facades\Notification::send($usuariosANotificar, new \App\Notifications\MovimientoArchivoNotification($gaceta, 'Préstamo', auth()->user()->name));

        activity()
            ->useLog('Modulo_Archivo')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Préstamo Gaceta N° ' . $gaceta->numero . ' a ' . $request->solicitante_nombres . ' (Tlf: ' . $request->telefono . ')');

        return back()->with('success', 'Préstamo registrado correctamente.');
    }

    public function marcarDevuelto(PrestamoArchivo $prestamo)
    {
        $prestamo->estado_prestamo = 'Devuelto';
        $prestamo->fecha_devolucion_real = now();
        $prestamo->save();

        if ($prestamo->gaceta) {
            if ($prestamo->gaceta->inventario) {
                $prestamo->gaceta->inventario->increment('stock', 1);
            }
            $prestamo->gaceta->increment('ejemplares_fisicos', 1);
        }

        $usuariosANotificar = \App\Models\User::role(['Super Admin', 'Coordinador General', 'Director de Consultoría Jurídica'])->get();
        \Illuminate\Support\Facades\Notification::send($usuariosANotificar, new \App\Notifications\MovimientoArchivoNotification($prestamo->gaceta, 'Devolución', auth()->user()->name));

        activity()
            ->useLog('Modulo_Archivo')
            ->performedOn($prestamo->gaceta)
            ->causedBy(auth()->user())
            ->log('Devolución de Gaceta Física N° ' . $prestamo->gaceta->numero . ' confirmada. Stock retornado.');

        return back()->with('success', 'Devolución procesada correctamente.');
    }

    public function imprimirActa(PrestamoArchivo $prestamo)
    {
        $prestamo->load('gaceta');
        $pdf = Pdf::loadView('archivo.pdf.acta_prestamo', compact('prestamo'));
        return $pdf->stream('Acta_Prestamo_' . $prestamo->gaceta->numero . '.pdf');
    }

    public function generarReporte(Request $request)
    {
        $sede_id = $request->input('sede_id');

        $query = \App\Models\InventarioArchivo::with(['gaceta', 'sede', 'estante', 'gaveta'])
                    ->where('stock', '>', 0);

        if ($sede_id) {
            $query->where('sede_id', $sede_id);
        }

        $query->orderBy('sede_id')->orderBy('estante_id');

        $inventario = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('archivo.pdf.inventario', compact('inventario'));
        return $pdf->stream('reporte_inventario.pdf');
    }
}
