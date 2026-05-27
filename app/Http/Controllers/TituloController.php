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
            'abreviatura' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Titulo::create($request->all());

        return redirect()->route('titulos.index')->with('success', 'Título creado correctamente.');
    }

    public function show(Titulo $titulo)
    {
        //
    }

    public function edit(Titulo $titulo)
    {
        return view('titulos.edit', compact('titulo'));
    }

    public function update(Request $request, Titulo $titulo)
    {
        $request->validate([
            'abreviatura' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $titulo->update($request->all());

        return redirect()->route('titulos.index')->with('success', 'Título actualizado correctamente.');
    }

    public function destroy(Titulo $titulo)
    {
        $titulo->delete();
        return redirect()->route('titulos.index')->with('success', 'Título eliminado correctamente.');
    }
}
