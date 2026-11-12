<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FichaActividad;
use App\Models\ImagenFicha;
use App\Models\MetaTrimestral;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class FichaActividadController extends Controller
{
    public function create()
    {
        $query = \App\Models\MetaTrimestral::with(['actividadPoa.poa.jefatura'])
            ->whereHas('actividadPoa.poa', function($q) {
                $q->whereIn('estado', ['Aprobado', 'Ejecucion']);
            });

        $user = auth()->user();
        if (!\App\Models\Poa::usuarioTieneAccesoGlobal($user)) {
            $jefaturaIds = \App\Models\Poa::obtenerJefaturasPermitidas($user);

            $query->whereHas('actividadPoa.poa', function ($q) use ($jefaturaIds) {
                if (empty($jefaturaIds)) {
                    $q->whereIn('jefatura_id', [0]);
                } else {
                    $q->whereIn('jefatura_id', $jefaturaIds);
                }
            });
        }

        $metas = $query->get();
        return view('poa.fichas.create', compact('metas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'meta_trimestral_id' => 'required|exists:meta_trimestrals,id',
            'fecha' => 'required|date',
            'titulo_actividad' => 'required|string|max:255',
            'desarrollada_por' => 'required|string|max:255',
            'cantidad_beneficiados' => 'required|integer|min:0',
            'instituciones_involucradas' => 'nullable|string',
            'objetivo_logrado' => 'nullable|string',
            'imagenes.*' => 'image|max:' . config('sgcj.archivos.max_imagen_kb')
        ]);

        $ficha = FichaActividad::create($request->except('imagenes'));

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $path = $imagen->store('fichas', 'public');
                $ficha->imagenes()->create(['ruta_imagen' => $path]);
            }
        }

        return redirect()->route('poa.dashboard')->with('success', 'Ficha de Actividad registrada con éxito.');
    }

    public function exportPdf($id)
    {
        $ficha = FichaActividad::with(['metaTrimestral.actividadPoa.poa.jefatura'])->findOrFail($id);
        $ficha->load('imagenes');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('poa.fichas.pdf', compact('ficha'));
        return $pdf->stream('informe_actividad_'.$ficha->id.'.pdf');
    }
}
