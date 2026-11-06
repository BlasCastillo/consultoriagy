<?php

namespace App\Http\Controllers;

use App\Models\Titulo;
use Illuminate\Http\Request;

class TituloController extends Controller
{
    public function index()
    {
        $titulos = Titulo::all();
        return view('titulos.index', compact('titulos'));
    }

    public function create()
    {
        return view('titulos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'abreviatura' => 'required|string|max:50|unique:titulos,abreviatura',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Titulo::create($request->only(['abreviatura', 'descripcion']));

        return redirect()->route('titulos.index')->with('success', 'Título creado correctamente.');
    }

    public function show(Titulo $titulo)
    {
        return redirect()->route('titulos.index');
    }

    public function edit(Titulo $titulo)
    {
        return view('titulos.edit', compact('titulo'));
    }

    public function update(Request $request, Titulo $titulo)
    {
        $request->validate([
            'abreviatura' => 'required|string|max:50|unique:titulos,abreviatura,' . $titulo->id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        $titulo->update($request->only(['abreviatura', 'descripcion']));

        return redirect()->route('titulos.index')->with('success', 'Título actualizado correctamente.');
    }

    public function destroy(Titulo $titulo)
    {
        if ($titulo->gobernadores()->exists()) {
            return redirect()->route('titulos.index')->with('error', 'No se puede eliminar un título asignado a un gobernador.');
        }
        $titulo->delete();
        return redirect()->route('titulos.index')->with('success', 'Título eliminado correctamente.');
    }
}
