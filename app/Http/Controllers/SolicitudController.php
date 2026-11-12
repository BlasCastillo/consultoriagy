<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SolicitudController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole('Representante Institucional')) {
            // El externo solo ve lo suyo
            $solicitudes = Solicitud::with(['institucion', 'actos'])
                ->where('institucion_id', auth()->user()->institucion_id)
                ->latest()->get();
            $vista = 'institutions.mis-solicitudes'; 
        } else {
            // Súper Admin y Jefe ven TODAS las solicitudes (sin filtros ciegos)
            $solicitudes = Solicitud::with(['institucion', 'actos'])
                ->latest()->get();
            $vista = 'solicitudes.index';
        }
        return view($vista, compact('solicitudes'));
    }

    public function create()
    {
        $instituciones = [];
        if (!auth()->user()->institucion_id) {
            $instituciones = \App\Models\Institucion::orderBy('nombre')->get();
        }
        return view('solicitudes.create', compact('instituciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo_acto' => 'required|string|max:255',
            'archivo_pdf' => 'required|mimes:pdf|max:' . config('sgcj.archivos.max_pdf_kb'),
            'institucion_id' => [
                \Illuminate\Validation\Rule::requiredIf(fn() => !auth()->user()->institucion_id),
                'nullable',
                'exists:instituciones,id',
            ],
            'actos' => 'required|array|min:1',
            'actos.*.tipo_acto' => 'required|string|max:255',
            'actos.*.numero' => 'required|string|max:255',
            'actos.*.sumario' => 'required|string',
        ]);

        $path = $request->file('archivo_pdf')->store('solicitudes/pdfs', 'public');

        $institucion_id = auth()->user()->institucion_id ?? $request->institucion_id;

        $solicitud = Solicitud::create([
            'titulo_acto' => $request->titulo_acto,
            'archivo_pdf_path' => $path,
            'institucion_id' => $institucion_id,
            'estado' => 'Pendiente',
        ]);

        foreach ($request->actos as $acto) {
            // Forzamos la asignación manual para asegurar que los datos entren limpios a la BD
            $solicitud->actos()->create([
                'tipo_acto' => $acto['tipo_acto'] ?? 'Otro',
                'numero'    => $acto['numero'] ?? 'S/N',
                'sumario'   => $acto['sumario'] ?? '',
            ]);
        }

        $evaluadores = \App\Models\User::permission('actualizar Solicitud')->get();
        \Illuminate\Support\Facades\Notification::send($evaluadores, new \App\Notifications\NuevaSolicitudNotification($solicitud));

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud creada exitosamente.');
    }

    public function show(Solicitud $solicitud)
    {
        // Si es externo, validamos que el institucion_id coincida estrictamente
        if (auth()->user()->hasRole('Representante Institucional')) {
            if ($solicitud->institucion_id !== auth()->user()->institucion_id) {
                abort(403, 'No tienes autorización para visualizar esta solicitud.');
            }
        }

        $solicitud->load(['institucion', 'actos']);

        return view('solicitudes.show', compact('solicitud'));
    }

    public function aprobar(Request $request, Solicitud $solicitud)
    {
        $request->validate([
            'fecha_recepcion_fisica' => 'required|date',
            'checklist' => 'nullable|array',
        ]);

        $solicitud->update([
            'estado' => 'Aprobada',
            'checklist' => $request->checklist ?? [],
            'fecha_recepcion_fisica' => $request->fecha_recepcion_fisica,
        ]);
        return redirect()->route('solicitudes.index')->with('success', 'Solicitud aprobada y validada correctamente. Lista para ensamblaje en Gaceta.');
    }

    public function rechazar(Request $request, Solicitud $solicitud)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:5'
        ]);
        
        // Asignación directa a prueba de fallos
        $solicitud->estado = 'Rechazada';
        $solicitud->motivo_rechazo = $request->motivo_rechazo;
        $solicitud->save();
        
        // Despachar notificación (si aplica)
        $usuariosAfectados = \App\Models\User::where('institucion_id', $solicitud->institucion_id)->get();
        if($usuariosAfectados->isNotEmpty()){
            \Illuminate\Support\Facades\Notification::send($usuariosAfectados, new \App\Notifications\SolicitudRechazadaNotification($solicitud, $request->motivo_rechazo));
        }
        
        return redirect()->route('solicitudes.index')->with('error', 'La solicitud ha sido rechazada y devuelta a la institución de origen.');
    }

    public function edit(Solicitud $solicitud)
    {
        // Si es externo, validamos que el institucion_id coincida
        if (auth()->user()->hasRole('Representante Institucional')) {
            if ($solicitud->institucion_id !== auth()->user()->institucion_id) {
                abort(403, 'No tienes autorización para editar esta solicitud.');
            }
        }

        // Solo permitir editar si está rechazada
        if ($solicitud->estado !== 'Rechazada') {
            return redirect()->route('solicitudes.show', $solicitud->id)->with('error', 'Solo se pueden editar solicitudes rechazadas.');
        }

        $solicitud->load('actos');
        $instituciones = [];
        if (!auth()->user()->institucion_id) {
            $instituciones = \App\Models\Institucion::orderBy('nombre')->get();
        }

        return view('solicitudes.edit', compact('solicitud', 'instituciones'));
    }

    public function update(Request $request, Solicitud $solicitud)
    {
        // Validar autorización
        if (auth()->user()->hasRole('Representante Institucional')) {
            if ($solicitud->institucion_id !== auth()->user()->institucion_id) {
                abort(403, 'No tienes autorización para editar esta solicitud.');
            }
        }

        if ($solicitud->estado !== 'Rechazada') {
            return redirect()->route('solicitudes.show', $solicitud->id)->with('error', 'Solo se pueden editar solicitudes rechazadas.');
        }

        $request->validate([
            'titulo_acto' => 'required|string|max:255',
            'archivo_pdf' => 'nullable|mimes:pdf|max:' . config('sgcj.archivos.max_pdf_kb'),
            'institucion_id' => [
                \Illuminate\Validation\Rule::requiredIf(fn() => !auth()->user()->institucion_id),
                'nullable',
                'exists:instituciones,id',
            ],
            'actos' => 'required|array|min:1',
            'actos.*.tipo_acto' => 'required|string|max:255',
            'actos.*.numero' => 'required|string|max:255',
            'actos.*.sumario' => 'required|string',
        ]);

        if ($request->hasFile('archivo_pdf')) {
            // Eliminar el anterior si existe
            if ($solicitud->archivo_pdf_path) {
                Storage::disk('public')->delete($solicitud->archivo_pdf_path);
            }
            $path = $request->file('archivo_pdf')->store('solicitudes/pdfs', 'public');
            $solicitud->archivo_pdf_path = $path;
        }

        $institucion_id = auth()->user()->institucion_id ?? $request->institucion_id;
        
        $solicitud->titulo_acto = $request->titulo_acto;
        $solicitud->institucion_id = $institucion_id;
        
        // Reinicio del ciclo
        $solicitud->estado = 'Pendiente';
        $solicitud->motivo_rechazo = null; // Limpiar el registro de rechazo
        $solicitud->save();

        // Actualizar actos: para simplificar, borramos los anteriores y creamos los nuevos
        $solicitud->actos()->delete();
        foreach ($request->actos as $acto) {
            $solicitud->actos()->create([
                'tipo_acto' => $acto['tipo_acto'] ?? 'Otro',
                'numero'    => $acto['numero'] ?? 'S/N',
                'sumario'   => $acto['sumario'] ?? '',
            ]);
        }

        $evaluadores = \App\Models\User::permission('actualizar Solicitud')->get();
        \Illuminate\Support\Facades\Notification::send($evaluadores, new \App\Notifications\NuevaSolicitudNotification($solicitud));

        return redirect()->route('solicitudes.show', $solicitud->id)->with('success', 'Apartado corregido y re-enviado exitosamente a Jefatura.');
    }
}
