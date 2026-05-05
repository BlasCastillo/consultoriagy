<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institution::query();
        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }
        $institutions = $query->paginate(10);
        return view('institutions.index', compact('institutions'));
    }

    public function create()
    {
        return view('institutions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rif' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:consultoria,ente_adscrito'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Institution::create($request->all());

        return redirect()->route('institutions.index')->with('success', 'Institución creada exitosamente.');
    }

    public function edit(Institution $institution)
    {
        return view('institutions.edit', compact('institution'));
    }

    public function update(Request $request, Institution $institution)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rif' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:consultoria,ente_adscrito'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $institution->update($request->all());

        return redirect()->route('institutions.index')->with('success', 'Institución actualizada exitosamente.');
    }

    public function destroy(Institution $institution)
    {
        $institution->delete();
        return redirect()->route('institutions.index')->with('success', 'Institución eliminada exitosamente.');
    }

    public function restore($id)
    {
        $institution = Institution::withTrashed()->findOrFail($id);
        $institution->restore();
        $institution->update(['status' => 'active']);
        return redirect()->route('institutions.index')->with('success', 'Institución restaurada exitosamente.');
    }
}
