<?php

namespace App\Http\Controllers;

use App\Models\Gobernador;
use App\Models\Titulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GobernadorController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Log::info('Roles del usuario actual: ', auth()->user()->roles->pluck('name')->toArray());
        $gobernadores = Gobernador::with('titulo')->get();
        return view('gobernadores.index', compact('gobernadores'));
    }

    public function create()
    {
        $titulos = Titulo::all();
        return view('gobernadores.create', compact('titulos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo_id' => 'required|exists:titulos,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'estado' => 'nullable'
        ]);

        DB::transaction(function () use ($request) {
            $estado = $request->has('estado');

            if ($estado) {
                Gobernador::query()->update(['estado' => false]);
            }

            Gobernador::create([
                'titulo_id' => $request->titulo_id,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'estado' => $estado,
            ]);
        });

        return redirect()->route('gobernadores.index')->with('success', 'Gobernador creado correctamente.');
    }

    public function show($id)
    {
        // No implementado
    }

    public function edit($id)
    {
        $gobernador = Gobernador::findOrFail($id);
        $titulos = Titulo::all();
        return view('gobernadores.edit', compact('gobernador', 'titulos'));
    }

    public function update(Request $request, $id)
    {
        $gobernador = Gobernador::findOrFail($id);
        $request->validate([
            'titulo_id' => 'required|exists:titulos,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'estado' => 'nullable'
        ]);

        DB::transaction(function () use ($request, $gobernador) {
            $estado = $request->has('estado');

            if ($estado) {
                Gobernador::where('id', '!=', $gobernador->id)->update(['estado' => false]);
            }

            $gobernador->update([
                'titulo_id' => $request->titulo_id,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'estado' => $estado,
            ]);
        });

        return redirect()->route('gobernadores.index')->with('success', 'Gobernador actualizado correctamente.');
    }

    public function destroy($id)
    {
        $gobernador = Gobernador::findOrFail($id);
        $gobernador->delete();
        return redirect()->route('gobernadores.index')->with('success', 'Gobernador eliminado correctamente.');
    }
}
