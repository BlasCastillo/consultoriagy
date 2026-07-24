<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Institucion;
use App\Models\Jefatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'institucion', 'jefatura']);

        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }

        $users = $query->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::where('status', true)->get();
        $instituciones = Institucion::orderBy('nombre')->get();
        $jefaturas = Jefatura::orderBy('nombre')->get();
        return view('users.create', compact('roles', 'instituciones', 'jefaturas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array'],
            'institucion_id' => ['nullable', 'exists:instituciones,id'],
            'jefatura_id' => ['nullable', 'exists:jefaturas,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => true,
            'institucion_id' => $request->institucion_id,
            'jefatura_id' => $request->jefatura_id,
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::where('status', true)->get();
        $instituciones = Institucion::orderBy('nombre')->get();
        $jefaturas = Jefatura::orderBy('nombre')->get();
        return view('users.edit', compact('user', 'roles', 'instituciones', 'jefaturas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'roles' => ['required', 'array'],
            'status' => ['required', 'boolean'],
            'institucion_id' => ['nullable', 'exists:instituciones,id'],
            'jefatura_id' => ['nullable', 'exists:jefaturas,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
            'institucion_id' => $request->institucion_id,
            'jefatura_id' => $request->jefatura_id,
        ];

        // Regla de Negocio: Mutua exclusión forzada
        if (!empty($data['institucion_id'])) {
            $data['jefatura_id'] = null;
        } elseif (!empty($data['jefatura_id'])) {
            $data['institucion_id'] = null;
        }

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $isSuperAdmin = $user->hasRole('Super Admin');
        $superAdminCount = User::role('Super Admin')->count();
        if ($isSuperAdmin && $superAdminCount <= 1) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar al único Super Admin del sistema.');
        }

        // Ejecuta SoftDelete (borrado lógico) gracias al trait SoftDeletes en el modelo User
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado (Soft Delete) exitosamente.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        
        // Al restaurar, forzamos que el estatus sea 1 (Activo)
        $user->update(['status' => 1]);

        return redirect()->route('users.index')->with('success', 'Usuario restaurado exitosamente.');
    }
}
