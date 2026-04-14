<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity log.
     */
    public function index()
    {
        $activities = Activity::with('causer')->latest()->paginate(20);
        return view('activitylog.index', compact('activities'));
    }
}
