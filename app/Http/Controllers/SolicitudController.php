<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gaceta;
use App\Models\SumarioGaceta;
use App\Models\User;
use App\Notifications\NuevaSolicitudNotification;
use Spatie\Activitylog\Facades\Activity;

class SolicitudController extends Controller
{
    public function create()
    {
        $institution = auth()->user()->institution;
        
        if (!$institution) {
            return redirect()->route('mis-solicitudes.index')->with('error', 'No tienes una institución asignada para realizar solicitudes.');
        }

        return view('solicitudes.create', compact('institution'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sumarios' => 'required|array|min:1',
            'sumarios.*.tipo_acto' => 'required|string',
            'sumarios.*.numero_acto' => 'required|string',
            'sumarios.*.descripcion' => 'required|string',
        ]);

        $institutionId = auth()->user()->institution_id;

        // Crear la Gaceta con los campos administrativos en null
        $gaceta = Gaceta::create([
            'estado' => 'Solicitada',
            'numero' => null,
            'anio' => null,
            'tipo' => null,
            'gobernador_id' => null,
            'anio_politico' => null,
            'mes_politico' => null,
            'checklist' => null,
            'jefe_id' => null,
            'digitalizador_id' => null,
        ]);

        // Registrar actividad manual para dejar rastro de quién creó
        activity()
            ->useLog('BPM_Gacetas')
            ->performedOn($gaceta)
            ->causedBy(auth()->user())
            ->log('Solicitud Institucional Creada');

        // Guardar sumarios
        foreach ($request->sumarios as $sumarioData) {
            $gaceta->sumarios()->create([
                'institucion_id' => $institutionId,
                'tipo_acto' => $sumarioData['tipo_acto'],
                'numero_acto' => $sumarioData['numero_acto'],
                'descripcion' => $sumarioData['descripcion']
            ]);
        }

        // Notificar a Jefes de Digitalización
        $jefes = User::role(['Jefe de Digitalización', 'Super Admin', 'Super Administrador'])->get();
        foreach ($jefes as $jefe) {
            $jefe->notify(new NuevaSolicitudNotification($gaceta));
        }

        return redirect()->route('mis-solicitudes.index')->with('success', 'Solicitud registrada correctamente. Espere su evaluación por la Jefatura.');
    }
}
