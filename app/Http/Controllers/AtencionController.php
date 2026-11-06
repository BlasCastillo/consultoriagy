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

        // La meta del POA se incrementa automáticamente una sola vez a través de AtencionObserver@created

        $usuariosDestino = \App\Models\User::where('jefatura_id', $atencion->jefatura_destino_id)->get();
        if ($usuariosDestino->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($usuariosDestino, new \App\Notifications\NuevaAtencionNotification($atencion));
        }

        return redirect()->route('atenciones.index')->with('success', 'Atención registrada exitosamente y meta del POA actualizada.');
    }
}
