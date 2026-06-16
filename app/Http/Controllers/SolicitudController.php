<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SolicitudController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Logica estricta de visibilidad
        if ($user->institucion_id) {
            // Es externo
            $solicitudes = Solicitud::where('institucion_id', $user->institucion_id)->with('institucion')->latest()->get();
        } elseif ($user->jefatura_id) {
            // Es interno (Consultoria u otros)
            $solicitudes = Solicitud::with('institucion')->latest()->get();
        } else {
            // Super Admin u otro
            $solicitudes = Solicitud::with('institucion')->latest()->get();
        }

        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create()
    {
        return view('solicitudes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo_acto' => 'required|string|max:255',
            'archivo_word' => 'required|mimes:doc,docx|max:10240', // 10MB max
        ]);

        $path = $request->file('archivo_word')->store('solicitudes/words', 'public');

        Solicitud::create([
            'titulo_acto' => $request->titulo_acto,
            'archivo_word_path' => $path,
            'institucion_id' => auth()->user()->institucion_id,
            'estado' => 'Pendiente',
        ]);

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud creada exitosamente.');
    }

    public function show($id)
    {
        $solicitud = Solicitud::with('institucion')->findOrFail($id);
        
        // Validacion de seguridad
        if (auth()->user()->institucion_id && auth()->user()->institucion_id !== $solicitud->institucion_id) {
            abort(403, 'No tienes permiso para ver esta solicitud.');
        }

        return view('solicitudes.show', compact('solicitud'));
    }
}
