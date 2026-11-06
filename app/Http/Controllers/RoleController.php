<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\ModelPermissionAuditor;

class RoleController extends Controller
{
    /**
     * Muestra el listado de roles registrados.
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }

        $roles = $query->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo rol.
     */
    public function create()
    {
        $matrix = ModelPermissionAuditor::getPermissionsMatrix();
        return view('roles.create', compact('matrix'));
    }

    /**
     * Almacena un nuevo rol en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = [];
            foreach ($request->permissions as $permName) {
                $permission = Permission::firstOrCreate(['name' => $permName]);
                $permissions[] = $permission;
            }
            $role->syncPermissions($permissions);
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar el rol especificado.
     */
    public function edit(Role $role)
    {
        $matrix = ModelPermissionAuditor::getPermissionsMatrix();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'matrix', 'rolePermissions'));
    }

    /**
     * Actualiza el rol especificado en la base de datos.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update(['name' => $request->name]);

        $permissions = [];
        if ($request->has('permissions')) {
            foreach ($request->permissions as $permName) {
                $permission = Permission::firstOrCreate(['name' => $permName]);
                $permissions[] = $permission;
            }
        }
        
        $role->syncPermissions($permissions);
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Elimina el rol especificado de la base de datos.
     */
    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Super Admin', 'Super Administrador', 'Súper Administrador'])) {
            return redirect()->route('roles.index')->with('error', 'El rol de administración del sistema no puede ser eliminado.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    }

    /**
     * Restaura un rol eliminado lógicamente.
     */
    public function restore($id)
    {
        $role = Role::withTrashed()->findOrFail($id);
        $role->restore();
        
        // Al restaurar, forzamos que el estatus sea 1 (Activo)
        $role->update(['status' => 1]);

        return redirect()->route('roles.index')->with('success', 'Rol restaurado exitosamente.');
    }
}
