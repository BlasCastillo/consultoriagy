<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\ModelPermissionAuditor;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $matrix = ModelPermissionAuditor::getPermissionsMatrix();
        return view('roles.create', compact('matrix'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = [];
            foreach ($request->permissions as $permName) {
                // Ensure the permission exists in the database
                $permission = Permission::firstOrCreate(['name' => $permName]);
                $permissions[] = $permission;
            }
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $matrix = ModelPermissionAuditor::getPermissionsMatrix();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'matrix', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
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

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')->with('error', 'El rol Super Admin no puede ser eliminado.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    }

    /**
     * Restore the specified resource from storage.
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
