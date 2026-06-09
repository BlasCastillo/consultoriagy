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
        $metas = MetaTrimestral::with(['actividadPoa.poa.jefatura'])->get();
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
            'imagenes.*' => 'image|max:5120' // 5MB
        ]);

        $ficha = FichaActividad::create($request->except('imagenes'));

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {
                $path = $file->store('fichas_imagenes', 'public');
                ImagenFicha::create([
                    'ficha_actividad_id' => $ficha->id,
                    'ruta_imagen' => $path
                ]);
            }
        }

        return redirect()->route('poa.dashboard')->with('success', 'Ficha de Actividad registrada con éxito.');
    }

    public function exportPdf($id)
    {
        $ficha = FichaActividad::with(['imagenes', 'metaTrimestral.actividadPoa.poa.jefatura'])->findOrFail($id);
        
        $pdf = Pdf::loadView('poa.fichas.pdf', compact('ficha'));
        // Puedes usar stream() para ver en navegador o download()
        return $pdf->stream('Ficha_Actividad_' . $ficha->id . '.pdf');
    }
}
