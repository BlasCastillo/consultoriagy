<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Gestión de Roles y Permisos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">

                    {{-- Alertas de Éxito --}}
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Alertas de Error --}}
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif {{-- <- CORREGIDO: Faltaba cerrar este if --}}

                    {{-- Cabecera de la Tabla: Filtros y Botón Crear --}}
                    <div class="mb-4 flex justify-between items-center">
                        <div>
                            @if(request('trashed'))
                                <a href="{{ route('roles.index') }}" class="text-slate-600 hover:text-slate-900 underline text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Volver a Activos
                                </a>
                            @else
                                <a href="{{ route('roles.index', ['trashed' => 'true']) }}" class="text-slate-600 hover:text-slate-900 underline text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Ver Eliminados
                                </a>
                            @endif
                        </div>
                        <a href="{{ route('roles.create') }}" class="font-bold py-2 px-4 rounded transition duration-150 shadow-sm" style="background-color: #1e293b; color: white;">
                            Crear Nuevo Rol
                        </a>
                    </div>

                    {{-- Tabla de Roles --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-slate-300">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-300">
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">Nombre del Rol</th>
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">Permisos Asignados</th>
                                    <th class="py-3 px-4 text-center font-semibold text-slate-700">Estatus</th>
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700" width="200">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    <tr class="border-b border-slate-200 hover:bg-slate-50">
                                        <td class="py-3 px-4 align-top">{{ $role->name }}</td>
                                        
                                        {{-- Conteo de permisos --}}
                                        <td class="py-3 px-4">
                                            @php $permsCount = $role->permissions->count(); @endphp
                                            <span class="bg-slate-200 text-slate-800 text-xs px-2 py-1 rounded-full border border-slate-300 cursor-help"
                                                  title="{{ $role->permissions->pluck('name')->implode(', ') }}">
                                                {{ $permsCount }} Permisos Asignados
                                            </span>
                                        </td>

                                        {{-- Badge de Estatus / Eliminación --}}
                                        <td class="py-3 px-4 align-top text-center">
                                            @if($role->trashed())
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded border border-red-200 uppercase tracking-widest">
                                                    Eliminado
                                                </span>
                                            @elseif($role->status)
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded border border-green-200">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded border border-red-200">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Bloque de Acciones --}}
                                        <td class="py-3 px-4 align-top">
                                            <div class="flex space-x-3 items-center">
                                                @if($role->trashed())
                                                    <form action="{{ route('roles.restore', $role) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas restaurar este rol?');" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-green-600 hover:text-green-900 transition-colors" title="Restaurar">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('roles.edit', $role) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="Editar">
                                                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l-3 8 8-3 9.414-9.414a2 2 0 000-2.828l-3.536-3.536a2 0 00-2.828 0L9 11z"></path></svg>
                                                    </a>

                                                    @if($role->name !== 'Super Admin')
                                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este rol?');" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" title="Eliminar">
                                                                <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif {{-- <- CORREGIDO: Faltaba este @endif para el if/else de trashed --}}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>