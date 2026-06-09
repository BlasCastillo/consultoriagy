<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poa;
use App\Models\Jefatura;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PoaController extends Controller
{
    public function dashboard()
    {
        // En el dashboard ya no hay "tipo = General", todos son de jefaturas.
        // Simularemos el POA General sumando todos los del año en curso.
        $poas = Poa::with(['jefatura', 'actividades.metasTrimestrales'])->where('anio', Carbon::now()->year)->get();
        return view('poa.dashboard', compact('poas'));
    }

    public function index()
    {
        $user = auth()->user();
        
        $query = Poa::with('jefatura')->orderBy('anio', 'desc');

        if (!$user->hasAnyRole(['Super Admin', 'Consultor Juridico', 'Abogado Planificador'])) {
            $rolesUsuario = $user->roles->pluck('name')->toArray();
            
            $jefaturaIds = [];
            foreach ($rolesUsuario as $roleName) {
                if ($roleName === 'Jefe de Publicaciones') {
                    $j = Jefatura::where('nombre', 'like', '%Publicaciones%')->first();
                    if($j) $jefaturaIds[] = $j->id;
                } elseif ($roleName === 'Jefe de Legislacion y Asuntos Juridicos') {
                    $j = Jefatura::where('nombre', 'like', '%Legislación y Asuntos Jurídicos%')->first();
                    if($j) $jefaturaIds[] = $j->id;
                }
            }
            
            if (empty($jefaturaIds)) {
                $query->whereIn('jefatura_id', [0]); // Ocultar si no coincide jefatura
            } else {
                $query->whereIn('jefatura_id', $jefaturaIds);
            }
        }

        $poas = $query->get();
        return view('poa.admin.index', compact('poas'));
    }

    public function create()
    {
        $jefaturas = Jefatura::all();
        return view('poa.admin.create', compact('jefaturas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|min:2020|max:2099',
            'jefatura_id' => 'required|exists:jefaturas,id',
            'estado' => 'required|in:Aprobado,Ejecucion,Cerrado'
        ]);

        $data = $request->all();

        if ($data['anio'] == Carbon::now()->year) {
            $data['estado'] = 'Ejecucion';
        }

        Poa::create($data);

        return redirect()->route('poa.index')->with('success', 'POA creado correctamente.');
    }

    public function show($id)
    {
        $poa = Poa::with(['jefatura', 'actividades.metasTrimestrales'])->findOrFail($id);
        
        Poa::checkAndClose($poa);

        return view('poa.admin.show', compact('poa'));
    }

    public function exportMatrixPdf(Poa $poa)
    {
        $poa->load('actividades.metasTrimestrales', 'jefatura');
        
        $pdf = Pdf::loadView('poa.admin.matrix_pdf', compact('poa'))
                  ->setPaper('a4', 'landscape');
                  
        return $pdf->stream('matriz_poa_' . $poa->anio . '.pdf');
    }
}
