<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Gestión de Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">

                    @if(session('success'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
                                    icon: 'success', title: "{!! addslashes(session('success')) !!}"
                                });
                            });
                        </script>
                    @endif
                    @if(session('error'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({
                                    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
                                    icon: 'error', title: "{!! addslashes(session('error')) !!}"
                                });
                            });
                        </script>
                    @endif

                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-bold text-slate-800">Listado de Usuarios</h3>
                            
                            @if(request('trashed'))
                                <a href="{{ route('users.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-md border border-slate-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Ocultar Eliminados
                                </a>
                            @else
                                <a href="{{ route('users.index', ['trashed' => 'true']) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-md border border-slate-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Ver Eliminados
                                </a>
                            @endif
                        </div>
                        
                        <a href="{{ route('users.create') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nuevo Usuario
                        </a>
                    </div>

                    <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                        <table class="min-w-full">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Nombre</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Email</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Institución</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Rol(es)</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Estatus</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider" width="140">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-sm text-slate-800 font-semibold">{{ $user->name }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-600">{{ $user->email }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            @if($user->institution)
                                                @if($user->institution->type === 'consultoria')
                                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded border border-blue-200" title="{{ $user->institution->name }}">
                                                        Consultoría Jurídica
                                                    </span>
                                                @else
                                                    <span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-1 rounded border border-emerald-200" title="{{ $user->institution->name }}">
                                                        Ente Adscrito
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-400 italic text-xs">N/A</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($user->roles as $role)
                                                    <span class="bg-slate-200 text-slate-800 text-xs font-medium px-2 py-1 rounded-full border border-slate-300">
                                                        {{ $role->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-slate-400 italic text-xs">Sin rol</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-center">
                                            @if($user->trashed())
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded border border-red-200 uppercase tracking-widest">
                                                    Eliminado
                                                </span>
                                            @elseif($user->status)
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded border border-green-200">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-1 rounded border border-amber-200">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-center">
                                            <div class="flex justify-center space-x-2 items-center">
                                                @if($user->trashed())
                                                    <form action="{{ route('users.restore', $user) }}" method="POST" 
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Restaurar Usuario?', text: 'Este usuario volverá a estar activo en el sistema.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, restaurar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white" title="Restaurar">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('users.edit', $user) }}" class="p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white" title="Editar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </a>

                                                    <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Se eliminará este usuario del sistema.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center bg-red-600 hover:bg-red-700 text-white" title="Eliminar">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-slate-500">No hay usuarios registrados que coincidan con la búsqueda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>