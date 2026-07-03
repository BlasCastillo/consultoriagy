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
        $gacetas = Gaceta::where('estado', 'Publicada')->latest('fecha_publicacion')->get();
        $prestamosActivos = PrestamoArchivo::with('gaceta')->where('estado_prestamo', 'Activo')->latest()->get();
        return view('archivo.index', compact('gacetas', 'prestamosActivos'));
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
            'numero' => 'required|string',
            'tipo' => 'required|in:Ordinaria,Extraordinaria',
            'ano_politico' => 'required|string',
            'mes_politico' => 'required|string',
            'fecha_publicacion' => 'required|date',
            'gobernador_id' => 'required|exists:gobernadores,id',
            'archivo_pdf_completo' => 'required|mimes:pdf|max:20480',
            'actos' => 'required|array|min:1',
            'actos.*.institucion_id' => 'required|exists:instituciones,id',
            'actos.*.tipo_acto' => 'required|string',
            'actos.*.numero' => 'required|string',
            'actos.*.sumario' => 'required|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // 1. Guardar PDF
            $path = $request->file('archivo_pdf_completo')->store('gacetas/publicadas', 'public');

            // 2. Crear Gaceta Retroactiva
            $gaceta = \App\Models\Gaceta::create([
                'numero' => $request->numero,
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

            // 3. Crear Solicitudes Fantasma y sus Actos
            foreach ($request->actos as $actoData) {
                $solicitudFantasma = new \App\Models\Solicitud();
                $solicitudFantasma->institucion_id = $actoData['institucion_id'];
                $solicitudFantasma->gaceta_id = $gaceta->id;
                $solicitudFantasma->titulo_acto = 'Carga Histórica Retroactiva'; // Columna exigida por la BD
                $solicitudFantasma->estado = 'Publicada';
                $solicitudFantasma->save();

                $acto = new \App\Models\ActoAdministrativo();
                $acto->solicitud_id = $solicitudFantasma->id;
                $acto->tipo_acto = $actoData['tipo_acto'];
                $acto->numero = $actoData['numero'];
                $acto->sumario = $actoData['sumario'];
                $acto->save();
            }
            
            // Registrar en Bitácora
            activity()
                ->useLog('Modulo_Archivo')
                ->causedBy(auth()->user())
                ->performedOn($gaceta)
                ->log("Gaceta Histórica N° {$gaceta->numero} ingresada al sistema vía Fast-Track.");
        });

        return redirect()->route('archivo.index')->with('success', 'Gaceta histórica procesada e indexada correctamente.');
    }

    public function actualizarUbicacion(Request $request, Gaceta $gaceta)
    {
        $request->validate([
            'ubicacion_topografica' => 'nullable|string|max:255',
            'ejemplares_fisicos' => 'required|integer|min:0',
        ]);

        $gaceta->ubicacion_topografica = $request->ubicacion_topografica;
        $gaceta->ejemplares_fisicos = $request->ejemplares_fisicos;
        $gaceta->save();

        activity()
            ->useLog('Modulo_Archivo')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Ubicación topográfica de la Gaceta N° ' . $gaceta->numero . ' actualizada a: ' . $request->ubicacion_topografica);

        return back()->with('success', 'Ubicación e inventario actualizados correctamente.');
    }

    public function prestar(Request $request, Gaceta $gaceta)
    {
        $request->validate([
            'solicitante_cedula' => 'required|string|max:20',
            'solicitante_nombres' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:255',
            'motivo_uso' => 'required|string',
            'fecha_salida' => 'required|date',
            'fecha_devolucion_estimada' => 'required|date|after_or_equal:fecha_salida',
        ]);

        if ($gaceta->ejemplares_fisicos <= 0) {
            return back()->with('error', 'No hay ejemplares físicos disponibles para préstamo.');
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

        $gaceta->ejemplares_fisicos -= 1;
        $gaceta->save();

        activity()
            ->useLog('Modulo_Archivo')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Préstamo Gaceta N° ' . $gaceta->numero . ' a ' . $request->solicitante_nombres . ' (Tlf: ' . $request->telefono . ')');

        return back()->with('success', 'Préstamo registrado correctamente.');
    }

    public function devolver(PrestamoArchivo $prestamo)
    {
        $prestamo->estado_prestamo = 'Devuelto';
        $prestamo->fecha_devolucion_real = now();
        $prestamo->save();

        $prestamo->gaceta->ejemplares_fisicos += 1;
        $prestamo->gaceta->save();

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
}
