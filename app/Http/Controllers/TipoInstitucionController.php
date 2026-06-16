<?php

namespace App\Http\Controllers;

use App\Models\TipoInstitucion;
use Illuminate\Http\Request;

class TipoInstitucionController extends Controller
{
    public function index()
    {
        $tipoInstituciones = TipoInstitucion::all();
        return view('tipo_instituciones.index', compact('tipoInstituciones'));
    }

    public function create()
    {
        return view('tipo_instituciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_instituciones',
        ]);

        TipoInstitucion::create($request->all());

        return redirect()->route('tipo-instituciones.index')->with('success', 'Tipo de Institución creado con éxito.');
    }

    public function edit(TipoInstitucion $tipoInstitucion)
    {
        return view('tipo_instituciones.edit', compact('tipoInstitucion'));
    }

    public function update(Request $request, TipoInstitucion $tipoInstitucion)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_instituciones,nombre,' . $tipoInstitucion->id,
        ]);

        $tipoInstitucion->update($request->all());

        return redirect()->route('tipo-instituciones.index')->with('success', 'Tipo de Institución actualizado con éxito.');
    }

    public function destroy(TipoInstitucion $tipoInstitucion)
    {
        $tipoInstitucion->delete();

        return redirect()->route('tipo-instituciones.index')->with('success', 'Tipo de Institución eliminado con éxito.');
    }
}
