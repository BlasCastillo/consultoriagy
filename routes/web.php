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
use Illuminate\Support\Facades\Route;


/* Modificacion para ir directamente al login */
Route::redirect('/', '/login');
/* Route::get('/', function () {
 return view('welcome'); }); */

use App\Models\User;
use App\Models\Role;

Route::get('/dashboard', function () {
    $totalUsers = User::count();
    $totalRoles = Role::count();
    $totalInstitutions = \App\Models\Institution::count();

    // Distribución por roles (subquery o foreach)
    $rolesDistribution = Role::withCount('users')->get()->map(function ($role) {
            return [
            'name' => $role->name,
            'count' => $role->users_count
            ];
        }
        );

        return view('dashboard', compact('totalUsers', 'totalRoles', 'rolesDistribution', 'totalInstitutions'));    })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    Route::get('/notificaciones/{id}/leer', [\App\Http\Controllers\NotificacionController::class, 'leer'])->name('notificaciones.leer');

    // Gacetas - Administrativas (Jefatura)
    Route::middleware(['role:Super Admin|Super Administrador|Jefe de Digitalización'])->group(function () {
        Route::get('gacetas/create', [GacetaController::class, 'create'])->name('gacetas.create');
        Route::post('gacetas', [GacetaController::class, 'store'])->name('gacetas.store');
        Route::get('/gacetas-solicitadas', [GacetaController::class, 'solicitudesEntrantes'])->name('gacetas.solicitadas');
    });

    // Gacetas - Jefe de Digitalización
    Route::middleware(['role:Jefe de Digitalización|Super Admin|Super Administrador'])->group(function () {
        Route::get('gacetas/{gaceta}/checklist', [GacetaController::class, 'evaluarChecklist'])->name('gacetas.checklist');
        Route::post('gacetas/{gaceta}/checklist', [GacetaController::class, 'guardarChecklist'])->name('gacetas.checklist.save');
        
        Route::get('gacetas/{gaceta}/asignar', [GacetaController::class, 'asignarDigitalizador'])->name('gacetas.asignar');
        Route::post('gacetas/{gaceta}/asignar', [GacetaController::class, 'guardarAsignacion'])->name('gacetas.asignar.save');
        
        Route::get('gacetas/{gaceta}/aprobar', [GacetaController::class, 'aprobarPublicacion'])->name('gacetas.aprobar');
        Route::post('gacetas/{gaceta}/publicar', [GacetaController::class, 'publicar'])->name('gacetas.publicar');
    });

    // Gacetas - Digitalizador
    Route::middleware(['role:Digitalizador|Super Admin|Super Administrador'])->group(function () {
        Route::get('gacetas/panel-digitalizador', [GacetaController::class, 'panelDigitalizador'])->name('gacetas.digitalizador');
        Route::get('gacetas/{gaceta}/upload-pdf', [GacetaController::class, 'uploadPdf'])->name('gacetas.upload_pdf');
        Route::post('gacetas/{gaceta}/upload-pdf', [GacetaController::class, 'savePdf'])->name('gacetas.save_pdf');
    });

    Route::get('gacetas', [GacetaController::class, 'index'])->name('gacetas.index');
    Route::get('gacetas/{gaceta}', [GacetaController::class, 'show'])->name('gacetas.show');

    Route::middleware(['role:Institucion|Institucional|Super Admin|Super Administrador'])->group(function () {
        Route::get('mis-solicitudes', [MisSolicitudesController::class, 'index'])->name('mis-solicitudes.index');
        Route::get('solicitudes/create', [SolicitudController::class, 'create'])->name('solicitudes.create');
        Route::post('solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    });

    Route::middleware(['role:Super Administrador|Super Admin'])->group(function () {
        Route::resource('gobernadores', GobernadorController::class);
        Route::resource('titulos', TituloController::class);
    });

    Route::middleware(['role:Super Admin|Super Administrador'])->group(function () {
            Route::patch('roles/{id}/restore', [RoleController::class , 'restore'])->name('roles.restore');
            Route::resource('roles', RoleController::class);

            Route::patch('users/{id}/restore', [UserController::class , 'restore'])->name('users.restore');
            Route::resource('users', UserController::class);

            Route::patch('institutions/{id}/restore', [InstitutionController::class , 'restore'])->name('institutions.restore');
            Route::resource('institutions', InstitutionController::class);

            Route::get('/bitacora', [ActivityLogController::class , 'index'])->name('bitacora.index');

            Route::get('/backups', [BackupController::class , 'index'])->name('backups.index');
            Route::post('/backups', [BackupController::class , 'create'])->name('backups.create');
            Route::get('/backups/{fileName}', [BackupController::class , 'download'])->name('backups.download');
            Route::delete('/backups/{fileName}', [BackupController::class , 'destroy'])->name('backups.destroy');
        }
        );    });

require __DIR__ . '/auth.php';
