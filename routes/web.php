<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Models\User;
use App\Models\Role;

Route::get('/dashboard', function () {
    $totalUsers = User::count();
    $totalRoles = Role::count();

    // Distribución por roles (subquery o foreach)
    $rolesDistribution = Role::withCount('users')->get()->map(function($role) {
        return [
            'name' => $role->name,
            'count' => $role->users_count
        ];
    });

    return view('dashboard', compact('totalUsers', 'totalRoles', 'rolesDistribution'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::patch('roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');
        Route::resource('roles', RoleController::class);
        
        Route::patch('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::resource('users', UserController::class);
        
        Route::get('/bitacora', [ActivityLogController::class, 'index'])->name('bitacora.index');
        
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{fileName}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});

require __DIR__.'/auth.php';
