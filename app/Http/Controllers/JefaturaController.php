<?php

namespace App\Http\Controllers;

use App\Models\Jefatura;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class JefaturaController extends Controller
{
    /**
     * Export the organigrama as PDF.
     */
    public function exportarPdf()
    {
        // Solo traemos los Nivel 1, el 'with' cargará todos los hijos automáticamente
        $raices = Jefatura::whereNull('parent_id')->with('childrenRecursive')->orderBy('nombre')->get();
        
        $pdf = Pdf::loadView('jefaturas.pdf', compact('raices'));
        return $pdf->stream('organigrama_consultoria.pdf');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Jefatura::with('unidadSuperior')->orderBy('nivel');

        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }

        $jefaturas = $query->paginate(15);

        return view('jefaturas.index', compact('jefaturas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posiblesPadres = Jefatura::orderBy('nivel')->get();
        return view('jefaturas.create', compact('posiblesPadres'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'required|integer|min:1|max:10',
            'parent_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->nivel > 1 && empty($value)) {
                        $fail('La unidad superior es obligatoria cuando el nivel es mayor a 1.');
                    }
                    if (!empty($value)) {
                        $parent = Jefatura::find($value);
                        if (!$parent) {
                            $fail('La unidad superior seleccionada no existe.');
                        } elseif ($parent->nivel >= $request->nivel) {
                            $fail('El nivel de la unidad superior (' . $parent->nivel . ') debe ser menor al nivel de la unidad actual (' . $request->nivel . ').');
                        }
                    }
                },
            ],
        ]);

        Jefatura::create($request->all());

        return redirect()->route('jefaturas.index')->with('success', 'Unidad interna creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Jefatura $jefatura)
    {
        return view('jefaturas.show', compact('jefatura'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jefatura $jefatura)
    {
        $posiblesPadres = Jefatura::where('id', '!=', $jefatura->id)->orderBy('nivel')->get();
        return view('jefaturas.edit', compact('jefatura', 'posiblesPadres'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jefatura $jefatura)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'required|integer|min:1|max:10',
            'parent_id' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request, $jefatura) {
                    if ($request->nivel > 1 && empty($value)) {
                        $fail('La unidad superior es obligatoria cuando el nivel es mayor a 1.');
                    }
                    if (!empty($value)) {
                        if ($value == $jefatura->id) {
                            $fail('La unidad no puede depender de sí misma.');
                        }
                        $parent = Jefatura::find($value);
                        if (!$parent) {
                            $fail('La unidad superior seleccionada no existe.');
                        } elseif ($parent->nivel >= $request->nivel) {
                            $fail('El nivel de la unidad superior (' . $parent->nivel . ') debe ser menor al nivel de la unidad actual (' . $request->nivel . ').');
                        }
                    }
                },
            ],
        ]);

        $jefatura->update($request->all());

        return redirect()->route('jefaturas.index')->with('success', 'Unidad interna actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jefatura $jefatura)
    {
        $jefatura->delete();
        return redirect()->route('jefaturas.index')->with('success', 'Unidad interna eliminada correctamente.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $jefatura = Jefatura::withTrashed()->findOrFail($id);
        $jefatura->restore();
        return redirect()->route('jefaturas.index', ['trashed' => 'true'])->with('success', 'Unidad interna restaurada correctamente.');
    }
}
