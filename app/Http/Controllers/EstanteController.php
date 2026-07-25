<?php

namespace App\Http\Controllers;

use App\Models\Estante;
use App\Models\Sede;
use Illuminate\Http\Request;

class EstanteController extends Controller
{
    public function index()
    {
        $estantes = Estante::with('sede')->latest()->get();
        return view('estantes.index', compact('estantes'));
    }

    public function create()
    {
        $sedes = Sede::all();
        return view('estantes.create', compact('sedes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'nombre' => 'required|string|max:255'
        ]);

        Estante::create($validated);
        return redirect()->route('estantes.index')->with('success', 'Estante registrado correctamente.');
    }

    public function edit(Estante $estante)
    {
        $sedes = Sede::all();
        return view('estantes.edit', compact('estante', 'sedes'));
    }

    public function update(Request $request, Estante $estante)
    {
        $validated = $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'nombre' => 'required|string|max:255'
        ]);

        $estante->update($validated);
        return redirect()->route('estantes.index')->with('success', 'Estante actualizado correctamente.');
    }

    public function destroy(Estante $estante)
    {
        $estante->delete();
        return redirect()->route('estantes.index')->with('success', 'Estante inhabilitado (SoftDelete) correctamente.');
    }
}
