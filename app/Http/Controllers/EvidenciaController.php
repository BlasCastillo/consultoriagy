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
        $user = auth()->user();
        if ($user->hasAnyRole(['Super Admin', 'Consultor Juridico', 'Abogado Planificador'])) {
            return true;
        }

        $rolesUsuario = $user->roles->pluck('name')->toArray();
        $jefaturaIds = [];
        foreach ($rolesUsuario as $roleName) {
            if ($roleName === 'Jefe de Publicaciones') {
                $j = \App\Models\Jefatura::where('nombre', 'like', '%Publicaciones%')->first();
                if($j) $jefaturaIds[] = $j->id;
            } elseif ($roleName === 'Jefe de Legislacion y Asuntos Juridicos') {
                $j = \App\Models\Jefatura::where('nombre', 'like', '%Legislación y Asuntos Jurídicos%')->first();
                if($j) $jefaturaIds[] = $j->id;
            }
        }

        return in_array($poa->jefatura_id, $jefaturaIds);
    }

    public function manage($actividad_id)
    {
        $actividad = \App\Models\ActividadPoa::with(['poa', 'metasTrimestrales.evidencias'])->findOrFail($actividad_id);

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
            'archivo' => 'required|mimes:jpg,png,pdf|max:5120',
            'descripcion' => 'nullable|string|max:1000',
            'tipo' => 'required|string|in:Ficha,Foto,Informe',
        ]);

        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('evidencias', 'public');

            Evidencia::create([
                'meta_trimestral_id' => $meta->id,
                'archivo_path' => $path,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
            ]);

            return back()->with('success', 'Evidencia subida correctamente.');
        }

        return back()->with('error', 'No se adjuntó ningún archivo válido.');
    }

    public function destroy($id)
    {
        $evidencia = Evidencia::with('metaTrimestral.actividadPoa.poa')->findOrFail($id);

        if (!$this->validarAccesoPOA($evidencia->metaTrimestral->actividadPoa->poa)) {
            return back()->with('error', 'No tienes permiso para eliminar evidencias en este POA.');
        }

        if (Storage::disk('public')->exists($evidencia->archivo_path)) {
            Storage::disk('public')->delete($evidencia->archivo_path);
        }

        $evidencia->delete();

        return back()->with('success', 'Evidencia eliminada permanentemente.');
    }
}
