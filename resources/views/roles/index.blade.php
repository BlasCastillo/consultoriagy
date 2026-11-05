<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Gestión de Roles y Permisos') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body text-slate-900">

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

                    <div class="mb-6 flex flex-col items-center justify-between gap-4 md:flex-row">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-lg font-bold text-slate-800">Listado de Roles</h3>

                            @if(request('trashed'))
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
                                    Ocultar Eliminados
                                </a>
                            @else
                                <a href="{{ route('roles.index', ['trashed' => 'true']) }}" class="btn btn-secondary btn-sm">
                                    Ver Eliminados
                                </a>
                            @endif
                        </div>

                        <a href="{{ route('roles.create') }}" class="btn btn-primary">
                            Crear Nuevo Rol
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Nombre del Rol</th>
                                    <th>Permisos Asignados</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center" width="160">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td class="align-middle font-semibold text-slate-800">{{ $role->name }}</td>

                                        {{-- Conteo de permisos --}}
                                        <td class="align-middle">
                                            @php $permsCount = $role->permissions->count(); @endphp
                                            <span class="badge badge-neutral cursor-help"
                                                  title="{{ $role->permissions->pluck('name')->implode(', ') }}">
                                                {{ $permsCount }} Permisos Asignados
                                            </span>
                                        </td>

                                        {{-- Badge de Estatus / Eliminación --}}
                                        <td class="text-center align-middle">
                                            @if($role->trashed())
                                                <span class="badge badge-danger uppercase tracking-wider">Eliminado</span>
                                            @elseif($role->status)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-warning">Inactivo</span>
                                            @endif
                                        </td>

                                        {{-- Bloque de Acciones --}}
                                        <td class="align-middle">
                                            <div class="flex items-center justify-center gap-2">
                                                @if($role->trashed())
                                                    <form action="{{ route('roles.restore', $role) }}" method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Restaurar Rol?', text: 'Este rol volverá a estar activo en el sistema.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, restaurar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-primary btn-sm">Restaurar</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm">Editar</a>

                                                    @if(!in_array($role->name, ['Super Admin', 'Super Administrador']))
                                                        <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                            onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Se eliminará este rol del sistema.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-500">No hay roles registrados que coincidan con la búsqueda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
