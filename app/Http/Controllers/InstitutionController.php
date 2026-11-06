<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institucion::with('tipoInstitucion');
        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }
        $institutions = $query->paginate(10);
        return view('institutions.index', compact('institutions'));
    }

    public function create()
    {
        $tipoInstituciones = \App\Models\TipoInstitucion::all();
        return view('institutions.create', compact('tipoInstituciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('instituciones', 'nombre')],
            'siglas' => ['nullable', 'string', 'max:50'],
            'tipo_institucion_id' => ['required', 'exists:tipo_instituciones,id'],
            'estatus' => ['boolean'],
        ]);

        Institucion::create($request->all());

        return redirect()->route('institutions.index')->with('success', 'Institución creada exitosamente.');
    }

    public function edit(Institucion $institution)
    {
        $tipoInstituciones = \App\Models\TipoInstitucion::all();
        return view('institutions.edit', compact('institution', 'tipoInstituciones'));
    }

    public function update(Request $request, Institucion $institution)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('instituciones', 'nombre')->ignore($institution->id)],
            'siglas' => ['nullable', 'string', 'max:50'],
            'tipo_institucion_id' => ['required', 'exists:tipo_instituciones,id'],
            'estatus' => ['boolean'],
        ]);

        $institution->update($request->all());

        return redirect()->route('institutions.index')->with('success', 'Institución actualizada exitosamente.');
    }

    public function destroy(Institucion $institution)
    {
        $institution->delete();
        return redirect()->route('institutions.index')->with('success', 'Institución eliminada exitosamente.');
    }

    public function restore($id)
    {
        $institution = Institucion::withTrashed()->findOrFail($id);
        $institution->restore();
        return redirect()->route('institutions.index')->with('success', 'Institución restaurada exitosamente.');
    }
}
