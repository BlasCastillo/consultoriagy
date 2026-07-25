<?php

namespace App\Http\Controllers;

use App\Models\Gaveta;
use App\Models\Estante;
use Illuminate\Http\Request;

class GavetaController extends Controller
{
    public function index()
    {
        $gavetas = Gaveta::with('estante.sede')->latest()->get();
        return view('gavetas.index', compact('gavetas'));
    }

    public function create()
    {
        $estantes = Estante::with('sede')->get();
        return view('gavetas.create', compact('estantes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estante_id' => 'required|exists:estantes,id',
            'nombre' => 'required|string|max:255'
        ]);

        Gaveta::create($validated);
        return redirect()->route('gavetas.index')->with('success', 'Gaveta registrada correctamente.');
    }

    public function edit(Gaveta $gaveta)
    {
        $estantes = Estante::with('sede')->get();
        return view('gavetas.edit', compact('gaveta', 'estantes'));
    }

    public function update(Request $request, Gaveta $gaveta)
    {
        $validated = $request->validate([
            'estante_id' => 'required|exists:estantes,id',
            'nombre' => 'required|string|max:255'
        ]);

        $gaveta->update($validated);
        return redirect()->route('gavetas.index')->with('success', 'Gaveta actualizada correctamente.');
    }

    public function destroy(Gaveta $gaveta)
    {
        $gaveta->delete();
        return redirect()->route('gavetas.index')->with('success', 'Gaveta inhabilitada (SoftDelete) correctamente.');
    }
}
