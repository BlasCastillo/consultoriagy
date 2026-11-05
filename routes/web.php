<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\GacetaController;
use App\Http\Controllers\MisSolicitudesController;
use App\Http\Controllers\GobernadorController;
use App\Http\Controllers\TituloController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\TipoInstitucionController;
use Illuminate\Support\Facades\Route;

/* Modificacion para ir directamente al login */
Route::redirect('/', '/login');

use App\Models\User;
use App\Models\Role;

Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('Representante Institucional')) {
        return redirect('/mis-solicitudes');
    }

    // --- 1. KPIs CON REGLAS DE NEGOCIO ESTRICTAS ---
    $kpiGacetas = \App\Models\Gaceta::where('estado', 'Publicada')->count();

    // Solicitudes se consideran atendidas si su estado es 'Publicada'
    $kpiSolicitudesAtendidas = \App\Models\Solicitud::where('estado', 'Publicada')->count();

    // Instituciones activas: Tienen un usuario asignado
    $kpiInstitucionesActivas = \App\Models\User::whereNotNull('institucion_id')->distinct('institucion_id')->count('institucion_id');

    $kpiTotalInstituciones = \App\Models\Institucion::count();

    $kpiPoasActuales = \App\Models\Poa::where('anio', now()->year)->count();

    // Préstamos Activos (Todo lo que no esté 'Devuelto')
    $kpiPrestamosActivos = \App\Models\PrestamoArchivo::where('estado_prestamo', '!=', 'Devuelto')->count();

    // --- 2. GRÁFICOS ---
    // Gráfico 1: Solicitudes por Mes (PostgreSQL strict)
    $chartSolicitudesMes = \App\Models\Solicitud::selectRaw('EXTRACT(MONTH FROM created_at) as mes, COUNT(*) as total')
        ->whereYear('created_at', now()->year)
        ->groupByRaw('EXTRACT(MONTH FROM created_at)')
        ->orderBy('mes')
        ->pluck('total', 'mes')
        ->toArray();

    // Gráfico 2: Atenciones por Coordinación (Agrupación Real + Eager Loading)
    $atencionesRaw = \App\Models\Atencion::with('jefaturaDestino')
        ->selectRaw('jefatura_destino_id, COUNT(*) as total')
        ->whereYear('created_at', now()->year)
        ->groupBy('jefatura_destino_id')
        ->get();

    // Preparamos colecciones limpias para Chart.js
    $chartAtencionesLabels = $atencionesRaw->map(function ($item) {
        return $item->jefaturaDestino ? $item->jefaturaDestino->nombre : 'Sin Asignar';
    });
    $chartAtencionesData = $atencionesRaw->pluck('total');

    return view('dashboard', compact(
        'kpiGacetas',
        'kpiSolicitudesAtendidas',
        'kpiInstitucionesActivas',
        'kpiTotalInstituciones',
        'kpiPoasActuales',
        'kpiPrestamosActivos',
        'chartSolicitudesMes',
        'chartAtencionesLabels',
        'chartAtencionesData'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/gacetas-publicadas', [\App\Http\Controllers\GacetaController::class, 'publicadas'])->name('gacetas.publicadas');

Route::middleware(['auth', 'permission:read PrestamoArchivo'])->group(function () {
    Route::get('/archivo', [\App\Http\Controllers\ArchivoController::class, 'index'])->name('archivo.index');
    Route::get('/archivo/reporte-inventario', [\App\Http\Controllers\ArchivoController::class, 'generarReporte'])->name('archivo.reporte.inventario')->middleware('permission:read PrestamoArchivo');
    Route::get('/archivo/historico', [\App\Http\Controllers\ArchivoController::class, 'crearRetroactiva'])->name('archivo.retroactiva.create');
    Route::post('/archivo/historico', [\App\Http\Controllers\ArchivoController::class, 'guardarRetroactiva'])->name('archivo.retroactiva.store');
    Route::post('/archivo/gacetas/{gaceta}/inventario', [\App\Http\Controllers\ArchivoController::class, 'updateInventario'])->name('archivo.inventario.update')->middleware('permission:update PrestamoArchivo');
    Route::post('/archivo/prestar/{gaceta}', [\App\Http\Controllers\ArchivoController::class, 'storePrestamo'])->name('archivo.prestar');
    Route::patch('/archivo/devolver/{prestamo}', [\App\Http\Controllers\ArchivoController::class, 'marcarDevuelto'])->name('archivo.devolver');
    Route::get('/archivo/prestamos/{prestamo}/pdf', [\App\Http\Controllers\ArchivoController::class, 'imprimirActa'])->name('archivo.prestamo.pdf');
});

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ruta del Motor de Inteligencia Artificial (Chatbot)
    Route::post('/chatbot/ask', [\App\Http\Controllers\ChatbotController::class, 'ask'])->name('chatbot.ask');

    Route::resource('atenciones', \App\Http\Controllers\AtencionController::class);

    Route::get('/debug-permisos', function () {
        $user = auth()->user();
        return response()->json([
            'usuario_actual' => $user->name,
            'roles_asignados' => $user->getRoleNames(),
            'todos_los_permisos' => $user->getAllPermissions()->pluck('name')
        ], 200, [], JSON_PRETTY_PRINT);
    });

    Route::get('/notificaciones/{id}/leer', [\App\Http\Controllers\NotificacionController::class, 'leer'])->name('notificaciones.leer');

    // Gacetas - Administrativas (Jefatura)
    Route::middleware(['permission:create Gaceta'])->group(function () {
        Route::get('gacetas/create', [GacetaController::class, 'create'])->name('gacetas.create');
        Route::post('gacetas', [GacetaController::class, 'store'])->name('gacetas.store');
        Route::patch('gacetas/{gaceta}/reasignar', [GacetaController::class, 'reasignar'])->name('gacetas.reasignar');
    });


    // Gacetas - Jefe de Digitalización
    Route::middleware(['permission:update Gaceta'])->group(function () {
        Route::patch('solicitudes/{solicitud}/aprobar', [\App\Http\Controllers\SolicitudController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::patch('solicitudes/{solicitud}/rechazar', [\App\Http\Controllers\SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');

        Route::get('gacetas/{gaceta}/checklist', [GacetaController::class, 'evaluarChecklist'])->name('gacetas.checklist');
        Route::post('gacetas/{gaceta}/checklist', [GacetaController::class, 'guardarChecklist'])->name('gacetas.checklist.save');

        Route::get('gacetas/{gaceta}/asignar', [GacetaController::class, 'asignarDigitalizador'])->name('gacetas.asignar');
        Route::post('gacetas/{gaceta}/asignar', [GacetaController::class, 'guardarAsignacion'])->name('gacetas.asignar.save');

        Route::get('gacetas/{gaceta}/aprobar', [GacetaController::class, 'aprobarPublicacion'])->name('gacetas.aprobar');

        // Nuevas rutas de revisión para el Coordinador (Jefatura)
        Route::get('gacetas/{gaceta}/revision', [GacetaController::class, 'revisarPdfFinal'])->name('gacetas.revision');
        Route::post('gacetas/{gaceta}/publicar-oficial', [GacetaController::class, 'publicarGaceta'])->name('gacetas.publicar_oficial');
        Route::post('gacetas/{gaceta}/rechazar-escaneo', [GacetaController::class, 'rechazarEscaneo'])->name('gacetas.rechazar_escaneo');
        Route::post('gacetas/{gaceta}/subsanar', [GacetaController::class, 'subsanarGaceta'])->name('gacetas.subsanar');
        Route::get('gacetas/{gaceta}/edit', [GacetaController::class, 'edit'])->name('gacetas.edit');
    });

    // Gacetas - Digitalizador
    Route::middleware(['permission:create Gaceta'])->group(function () {
        Route::get('gacetas/panel-digitalizador', [GacetaController::class, 'panelDigitalizador'])->name('gacetas.digitalizador');
        Route::get('gacetas/{gaceta}/upload-pdf', [GacetaController::class, 'uploadPdf'])->name('gacetas.upload_pdf');
        Route::post('gacetas/{gaceta}/upload-pdf', [GacetaController::class, 'savePdf'])->name('gacetas.save_pdf');
        Route::post('gacetas/{gaceta}/enviar-revision', [GacetaController::class, 'enviarARevision'])->name('gacetas.enviar_revision');
        Route::post('gacetas/{gaceta}/actos/{acto}/pdf', [GacetaController::class, 'subirPdfActo'])->name('gacetas.actos.pdf');
        Route::delete('gacetas/{gaceta}/actos/{acto}/pdf', [GacetaController::class, 'eliminarPdfActo'])->name('gacetas.actos.pdf.delete');
        Route::patch('gacetas/{gaceta}/devolver', [GacetaController::class, 'devolver'])->name('gacetas.devolver');
    });

    Route::middleware(['permission:read Gaceta'])->group(function () {
        Route::get('gacetas', [GacetaController::class, 'index'])->name('gacetas.index');
        Route::get('gacetas/{gaceta}', [GacetaController::class, 'show'])->name('gacetas.show');
        Route::get('gacetas/{gaceta}/descargar', [GacetaController::class, 'descargarPdf'])->name('gacetas.descargar');
        Route::get('/gacetas/{id}/preview', [App\Http\Controllers\GacetaController::class, 'preview'])->name('gacetas.preview');
    });

    Route::middleware(['permission:read Solicitud'])->group(function () {
        Route::get('mis-solicitudes', [MisSolicitudesController::class, 'index'])->name('mis-solicitudes.index');
    });

    Route::group(['middleware' => ['permission:create Solicitud']], function () {
        Route::get('solicitudes/create', [SolicitudController::class, 'create'])->name('solicitudes.create');
        Route::post('solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
        Route::get('solicitudes/{solicitud}/edit', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
        Route::put('solicitudes/{solicitud}', [SolicitudController::class, 'update'])->name('solicitudes.update');
    });

    // Rutas accesibles para el Ente Externo e Interno
    Route::group(['middleware' => ['permission:read Solicitud']], function () {
        Route::get('solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::get('solicitudes/{solicitud}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    });

    Route::middleware(['permission:read Institucion'])->group(function () {
        Route::resource('gobernadores', GobernadorController::class);
        Route::resource('titulos', TituloController::class);
    });

    // Módulo POA
    Route::get('/poa/dashboard', [\App\Http\Controllers\PoaController::class, 'dashboard'])->name('poa.dashboard');
    Route::get('/poa/fichas/create', [\App\Http\Controllers\FichaActividadController::class, 'create'])->name('fichas.create');
    Route::post('/poa/fichas', [\App\Http\Controllers\FichaActividadController::class, 'store'])->name('fichas.store');
    Route::get('/poa/fichas/{id}/pdf', [\App\Http\Controllers\FichaActividadController::class, 'exportPdf'])->name('fichas.pdf');

    Route::middleware(['permission:read Poa|update Poa'])->group(function () {
        Route::get('/poa/admin/index', [\App\Http\Controllers\PoaController::class, 'index'])->name('poa.index');
        Route::get('/poa/admin/create', [\App\Http\Controllers\PoaController::class, 'create'])->name('poa.create');
        Route::post('/poa/admin/store', [\App\Http\Controllers\PoaController::class, 'store'])->name('poa.store');
        Route::get('/poa/admin/{id}', [\App\Http\Controllers\PoaController::class, 'show'])->name('poa.show');

        Route::get('/poa/admin/{poa_id}/actividades/create', [\App\Http\Controllers\ActividadPoaController::class, 'create'])->name('actividades.create');
        Route::post('/poa/admin/{poa_id}/actividades', [\App\Http\Controllers\ActividadPoaController::class, 'store'])->name('actividades.store');

        Route::get('/poa/admin/actividades/{actividad_id}/replanificar', [\App\Http\Controllers\ActividadPoaController::class, 'replanificar'])->name('actividades.replanificar');
        Route::post('/poa/admin/actividades/{actividad_id}/replanificar', [\App\Http\Controllers\ActividadPoaController::class, 'storeReplanificacion'])->name('actividades.storeReplanificacion');

        Route::get('/poa/admin/actividades/{actividad_id}/evidencias', [\App\Http\Controllers\EvidenciaController::class, 'manage'])->name('actividades.evidencias');
        Route::post('/poa/admin/metas/{meta_trimestral_id}/evidencias', [\App\Http\Controllers\EvidenciaController::class, 'store'])->name('evidencias.store');
        Route::delete('/poa/admin/evidencias/{evidencia_id}', [\App\Http\Controllers\EvidenciaController::class, 'destroy'])->name('evidencias.destroy');

        Route::get('/poa/admin/{poa}/matriz-pdf', [\App\Http\Controllers\PoaController::class, 'exportMatrixPdf'])->name('poa.matriz_pdf');
    });

    Route::middleware(['permission:read User|read Role'])->group(function () {
        Route::patch('roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');
        Route::resource('roles', RoleController::class);

        Route::patch('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::resource('users', UserController::class);
    });

    Route::middleware(['permission:read Institucion'])->group(function () {
        Route::patch('institutions/{id}/restore', [InstitutionController::class, 'restore'])->name('institutions.restore');
        Route::resource('institutions', InstitutionController::class);
        Route::resource('tipo-instituciones', TipoInstitucionController::class);
        Route::patch('jefaturas/{id}/restore', [App\Http\Controllers\JefaturaController::class, 'restore'])->name('jefaturas.restore');
        Route::get('jefaturas/exportar-organigrama', [App\Http\Controllers\JefaturaController::class, 'exportarPdf'])->name('jefaturas.pdf');
        Route::resource('jefaturas', App\Http\Controllers\JefaturaController::class);
    });

    Route::middleware(['permission:update PrestamoArchivo'])->group(function () {
        Route::resource('sedes', \App\Http\Controllers\SedeController::class);
        Route::resource('estantes', \App\Http\Controllers\EstanteController::class);
        Route::resource('gavetas', \App\Http\Controllers\GavetaController::class);
    });

    Route::middleware(['permission:read Bitacora'])->group(function () {
        Route::get('/bitacora', [ActivityLogController::class, 'index'])->name('bitacora.index');
    });

    Route::middleware(['permission:read Backup'])->group(function () {
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{fileName}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});

require __DIR__ . '/auth.php';
Route::get('/test-gemini', function () {
    $llave = config('services.gemini.key') ?? env('GEMINI_API_KEY');

    try {
        // Hacemos una petición GET al endpoint de "models"
        $respuesta = \Illuminate\Support\Facades\Http::withoutVerifying()
            ->get("https://generativelanguage.googleapis.com/v1beta/models?key=" . $llave);

        return $respuesta->json();

    } catch (\Exception $e) {
        return "Error real de red: " . $e->getMessage();
    }
});