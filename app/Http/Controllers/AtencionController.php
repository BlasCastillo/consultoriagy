<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AtencionController extends Controller
{
    public function index()
    {
        $atenciones = \App\Models\Atencion::with('jefaturaDestino')->latest()->paginate(10);
        return view('atenciones.index', compact('atenciones'));
    }

    public function create()
    {
        $jefaturas = \App\Models\Jefatura::orderBy('nombre')->get();
        return view('atenciones.create', compact('jefaturas'));
    }

    public function store(\App\Http\Requests\StoreAtencionRequest $request)
    {
        $atencion = \App\Models\Atencion::create($request->validated());

        \App\Services\PoaService::registrarEjecucion('atencion', $request->jefatura_destino_id, now());

        return redirect()->route('atenciones.index')->with('success', 'Atención registrada exitosamente y meta del POA actualizada.');
    }
}
