<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poa;
use App\Models\ActividadPoa;
use App\Models\MetaTrimestral;
use Illuminate\Support\Facades\DB;

class ActividadPoaController extends Controller
{
    public function create($poa_id)
    {
        $poa = Poa::findOrFail($poa_id);
        return view('poa.admin.actividades.create', compact('poa'));
    }

    public function store(Request $request, $poa_id)
    {
        $poa = Poa::findOrFail($poa_id);

        $request->validate([
            'descripcion' => 'required|string',
            'partida_presupuestaria' => 'nullable|string',
            'indicador_nombre' => 'nullable|string',
            'unidad_medida' => 'nullable|string',
            'medios_verificacion' => 'nullable|string',
            'frecuencia_lectura' => 'nullable|string',
            'formulacion' => 'nullable|string',
            'q1' => 'required|integer|min:0',
            'q2' => 'required|integer|min:0',
            'q3' => 'required|integer|min:0',
            'q4' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $poa) {
            $actividad = ActividadPoa::create([
                'poa_id' => $poa->id,
                'descripcion' => $request->descripcion,
                'partida_presupuestaria' => $request->partida_presupuestaria,
                'indicador_nombre' => $request->indicador_nombre,
                'unidad_medida' => $request->unidad_medida,
                'medios_verificacion' => $request->medios_verificacion,
                'frecuencia_lectura' => $request->frecuencia_lectura ?? 'Trimestral',
                'formulacion' => $request->formulacion,
            ]);

            for ($i = 1; $i <= 4; $i++) {
                $metaValue = $request->input('q' . $i);
                MetaTrimestral::create([
                    'actividad_poa_id' => $actividad->id,
                    'trimestre' => $i,
                    'meta_actual' => $metaValue,
                    'ejecutada' => 0,
                ]);
            }
        });

        Poa::checkAndClose($poa);

        return redirect()->route('poa.show', $poa->id)->with('success', 'Actividad y metas trimestrales registradas correctamente.');
    }

    public function replanificar($actividad_id)
    {
        $actividad = ActividadPoa::with(['metasTrimestrales' => function ($query) {
            $query->orderBy('trimestre');
        }, 'poa'])->findOrFail($actividad_id);

        return view('poa.admin.actividades.replanificar', compact('actividad'));
    }

    public function storeReplanificacion(Request $request, $actividad_id)
    {
        $actividad = ActividadPoa::with('poa')->findOrFail($actividad_id);

        $request->validate([
            'trimestre_origen' => 'required|integer|min:1|max:4',
            'trimestre_destino' => 'required|integer|min:1|max:4|different:trimestre_origen',
            'cantidad_movida' => 'required|integer|min:1',
            'justificacion' => 'required|string|min:10',
        ]);

        $origen = MetaTrimestral::where('actividad_poa_id', $actividad_id)
            ->where('trimestre', $request->trimestre_origen)
            ->firstOrFail();

        $destino = MetaTrimestral::where('actividad_poa_id', $actividad_id)
            ->where('trimestre', $request->trimestre_destino)
            ->firstOrFail();

        $saldoDisponible = $origen->meta_actual - $origen->ejecutada;

        if ($saldoDisponible < $request->cantidad_movida) {
            return back()->withErrors(['cantidad_movida' => 'El trimestre origen no tiene suficiente saldo disponible para mover esta cantidad.']);
        }

        DB::transaction(function () use ($origen, $destino, $request, $actividad) {
            $origen->meta_actual -= $request->cantidad_movida;
            $origen->save();

            $destino->meta_actual += $request->cantidad_movida;
            $destino->save();

            \App\Models\Replanificacion::create([
                'actividad_poa_id' => $actividad->id,
                'trimestre_origen' => $request->trimestre_origen,
                'trimestre_destino' => $request->trimestre_destino,
                'cantidad_movida' => $request->cantidad_movida,
                'justificacion' => $request->justificacion,
                'fecha_solicitud' => \Carbon\Carbon::now(),
            ]);

            activity()
                ->performedOn($actividad)
                ->log("Replanificación: Se movieron {$request->cantidad_movida} metas del Trim. {$request->trimestre_origen} al Trim. {$request->trimestre_destino} en la actividad {$actividad->descripcion}. Justificación: {$request->justificacion}");
        });

        return redirect()->route('poa.show', $actividad->poa_id)->with('success', 'Replanificación registrada y ejecutada exitosamente.');
    }
}
