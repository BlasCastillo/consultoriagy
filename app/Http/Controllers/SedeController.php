<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    public function index()
    {
        $sedes = Sede::latest()->get();
        return view('sedes.index', compact('sedes'));
    }

    public function create()
    {
        return view('sedes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:sedes,nombre'
        ]);

        Sede::create($validated);
        return redirect()->route('sedes.index')->with('success', 'Sede registrada correctamente.');
    }

    public function edit(Sede $sede)
    {
        return view('sedes.edit', compact('sede'));
    }

    public function update(Request $request, Sede $sede)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:sedes,nombre,' . $sede->id
        ]);

        $sede->update($validated);
        return redirect()->route('sedes.index')->with('success', 'Sede actualizada correctamente.');
    }

    public function destroy(Sede $sede)
    {
        $sede->delete();
        return redirect()->route('sedes.index')->with('success', 'Sede inhabilitada (SoftDelete) correctamente.');
    }
}
