<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evidencia;
use App\Models\MetaTrimestral;
use Illuminate\Support\Facades\Storage;

class EvidenciaController extends Controller
{
    private function validarAccesoPOA($poa)
    {
        if (!$poa) {
            return false;
        }

        $user = auth()->user();
        if (\App\Models\Poa::usuarioTieneAccesoGlobal($user)) {
            return true;
        }

        $jefaturaIds = \App\Models\Poa::obtenerJefaturasPermitidas($user);

        return in_array((int) $poa->jefatura_id, $jefaturaIds, true);
    }

    public function manage($actividad_id)
    {
        $actividad = \App\Models\ActividadPoa::with([
            'poa',
            'metasTrimestrales.evidencias',
            'metasTrimestrales.fichasActividades.imagenes',
        ])->findOrFail($actividad_id);

        if (!$this->validarAccesoPOA($actividad->poa)) {
            abort(403, 'No tienes permiso para gestionar evidencias de este POA.');
        }

        return view('poa.admin.actividades.evidencias', compact('actividad'));
    }

    public function store(Request $request, $meta_trimestral_id)
    {
        $meta = MetaTrimestral::with('actividadPoa.poa')->findOrFail($meta_trimestral_id);

        if (!$this->validarAccesoPOA($meta->actividadPoa->poa)) {
            return back()->with('error', 'No tienes permiso para subir evidencias en este POA.');
        }

        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:' . config('sgcj.archivos.max_pdf_kb'),
            'descripcion' => 'nullable|string|max:1000',
            'tipo' => 'required|string|in:Informe,Ficha,Foto',
        ]);

        if ($request->hasFile('archivo')) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $meta) {
                $path = $request->file('archivo')->store('evidencias', 'public');

                $meta->evidencias()->create([
                    'archivo_path' => $path,
                    'descripcion' => $request->descripcion,
                    'tipo' => $request->tipo
                ]);

                $meta->increment('ejecutada');
                \App\Models\Poa::checkAndClose($meta->actividadPoa->poa);
            });

            return back()->with('success', 'Evidencia subida correctamente.');
        }

        return back()->with('error', 'No se adjuntó ningún archivo válido.');
    }

    public function destroy($id)
    {
        $evidencia = Evidencia::with('metaTrimestral.actividadPoa.poa')->findOrFail($id);
        $meta = $evidencia->metaTrimestral;

        if (!$this->validarAccesoPOA($meta->actividadPoa->poa)) {
            return back()->with('error', 'No tienes permiso para eliminar evidencias en este POA.');
        }

        if (Storage::disk('public')->exists($evidencia->archivo_path)) {
            Storage::disk('public')->delete($evidencia->archivo_path);
        }

        $evidencia->delete();

        if ($meta->ejecutada > 0) {
            $meta->decrement('ejecutada');
        }

        if ($meta->actividadPoa && $meta->actividadPoa->poa) {
            \App\Models\Poa::checkAndClose($meta->actividadPoa->poa);
        }

        return back()->with('success', 'Evidencia eliminada permanentemente.');
    }
}
