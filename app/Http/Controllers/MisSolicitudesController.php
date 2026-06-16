<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class MisSolicitudesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institucionId = $user->institucion_id;

        if (!$institucionId) {
            return redirect()->route('dashboard')->with('error', 'No tienes una institución asignada.');
        }

        $solicitudes = Solicitud::where('institucion_id', $institucionId)
            ->with('gaceta')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('institutions.mis-solicitudes', compact('solicitudes'));
    }
}
