<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity log.
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('usuario_id')) {
            $query->where('causer_id', $request->usuario_id);
        }
        if ($request->filled('modulo')) {
            $query->where('subject_type', 'LIKE', '%' . $request->modulo . '%');
        }
        if ($request->filled('evento')) {
            $query->where('event', $request->evento);
        }

        $activities = $query->paginate(20)->withQueryString();
        $usuarios = \App\Models\User::orderBy('name')->get();

        return view('activitylog.index', compact('activities', 'usuarios'));
    }
}
