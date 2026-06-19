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

    $totalUsers = User::count();
    $totalRoles = Role::count();
    $totalInstitutions = \App\Models\Institucion::count();

    $rolesDistribution = Role::withCount('users')->get()->map(function ($role) {
        return [
            'name' => $role->name,
            'count' => $role->users_count
        ];
    });

    $poasAnuales = \App\Models\Poa::with(['actividades.metasTrimestrales'])
        ->where('anio', \Carbon\Carbon::now()->year)
        ->get();

    return view('dashboard', compact('totalUsers', 'totalRoles', 'rolesDistribution', 'totalInstitutions', 'poasAnuales'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/gacetas-publicadas', [\App\Http\Controllers\GacetaController::class, 'publicadas'])->name('gacetas.publicadas');

    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    Route::get('/notificaciones/{id}/leer', [\App\Http\Controllers\NotificacionController::class, 'leer'])->name('notificaciones.leer');

    // Gacetas - Administrativas (Jefatura)
    Route::middleware(['permission:crear gaceta'])->group(function () {
        Route::get('gacetas/create', [GacetaController::class, 'create'])->name('gacetas.create');
        Route::post('gacetas', [GacetaController::class, 'store'])->name('gacetas.store');
        Route::patch('gacetas/{gaceta}/reasignar', [GacetaController::class, 'reasignar'])->name('gacetas.reasignar');
    });


    // Gacetas - Jefe de Digitalización
    Route::middleware(['permission:evaluar solicitud'])->group(function () {
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
    });

    // Gacetas - Digitalizador
    Route::middleware(['permission:crear gaceta'])->group(function () {
        Route::get('gacetas/panel-digitalizador', [GacetaController::class, 'panelDigitalizador'])->name('gacetas.digitalizador');
        Route::get('gacetas/{gaceta}/upload-pdf', [GacetaController::class, 'uploadPdf'])->name('gacetas.upload_pdf');
        Route::post('gacetas/{gaceta}/upload-pdf', [GacetaController::class, 'savePdf'])->name('gacetas.save_pdf');
        Route::post('gacetas/{gaceta}/enviar-revision', [GacetaController::class, 'enviarARevision'])->name('gacetas.enviar_revision');
        Route::patch('gacetas/{gaceta}/devolver', [GacetaController::class, 'devolver'])->name('gacetas.devolver');
    });

    Route::middleware(['permission:ver gaceta'])->group(function () {
        Route::get('gacetas', [GacetaController::class, 'index'])->middleware('role:Súper Administrador|Coordinación de Publicaciones Oficiales')->name('gacetas.index');
        Route::get('gacetas/{gaceta}', [GacetaController::class, 'show'])->name('gacetas.show');
        Route::get('/gacetas/{id}/preview', [App\Http\Controllers\GacetaController::class, 'preview'])->name('gacetas.preview');
    });

    Route::middleware(['permission:ver solicitud'])->group(function () {
        Route::get('mis-solicitudes', [MisSolicitudesController::class, 'index'])->name('mis-solicitudes.index');
    });

    Route::group(['middleware' => ['permission:crear solicitud']], function () {
        Route::get('solicitudes/create', [SolicitudController::class, 'create'])->name('solicitudes.create');
        Route::post('solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
        Route::get('solicitudes/{solicitud}/edit', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
        Route::put('solicitudes/{solicitud}', [SolicitudController::class, 'update'])->name('solicitudes.update');
    });

    // Rutas accesibles para el Ente Externo e Interno
    Route::group(['middleware' => ['permission:ver solicitud']], function () {
        Route::get('solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::get('solicitudes/{solicitud}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    });

    Route::middleware(['permission:gestionar instituciones'])->group(function () {
        Route::resource('gobernadores', GobernadorController::class);
        Route::resource('titulos', TituloController::class);
    });

    // Módulo POA
    Route::get('/poa/dashboard', [\App\Http\Controllers\PoaController::class, 'dashboard'])->name('poa.dashboard');
    Route::get('/poa/fichas/create', [\App\Http\Controllers\FichaActividadController::class, 'create'])->name('fichas.create');
    Route::post('/poa/fichas', [\App\Http\Controllers\FichaActividadController::class, 'store'])->name('fichas.store');
    Route::get('/poa/fichas/{id}/pdf', [\App\Http\Controllers\FichaActividadController::class, 'exportPdf'])->name('fichas.pdf');

    Route::middleware(['permission:ver poa|gestionar poa'])->group(function () {
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

    Route::middleware(['permission:gestionar usuarios'])->group(function () {
        Route::patch('roles/{id}/restore', [RoleController::class , 'restore'])->name('roles.restore');
        Route::resource('roles', RoleController::class);

        Route::patch('users/{id}/restore', [UserController::class , 'restore'])->name('users.restore');
        Route::resource('users', UserController::class);
    });

    Route::middleware(['permission:gestionar instituciones'])->group(function () {
        Route::patch('institutions/{id}/restore', [InstitutionController::class , 'restore'])->name('institutions.restore');
        Route::resource('institutions', InstitutionController::class);
        Route::resource('tipo-instituciones', TipoInstitucionController::class);
        Route::patch('jefaturas/{id}/restore', [App\Http\Controllers\JefaturaController::class , 'restore'])->name('jefaturas.restore');
        Route::get('jefaturas/exportar-organigrama', [App\Http\Controllers\JefaturaController::class, 'exportarPdf'])->name('jefaturas.pdf');
        Route::resource('jefaturas', App\Http\Controllers\JefaturaController::class);
    });

    Route::middleware(['permission:ver reportes'])->group(function () {
        Route::get('/bitacora', [ActivityLogController::class , 'index'])->name('bitacora.index');
    });

    Route::middleware(['permission:gestionar configuracion'])->group(function () {
        Route::get('/backups', [BackupController::class , 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class , 'create'])->name('backups.create');
        Route::get('/backups/{fileName}', [BackupController::class , 'download'])->name('backups.download');
        Route::delete('/backups/{fileName}', [BackupController::class , 'destroy'])->name('backups.destroy');
    });
});

require __DIR__ . '/auth.php';
