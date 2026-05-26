<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SumarioGaceta;

class MisSolicitudesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $institutionId = $user->institution_id;

        if (!$institutionId) {
            return redirect()->route('dashboard')->with('error', 'No tienes una institución asignada.');
        }

        $sumarios = SumarioGaceta::where('institucion_id', $institutionId)
            ->with('gaceta')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('institutions.mis-solicitudes', compact('sumarios'));
    }
}
