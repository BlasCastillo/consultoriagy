<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Gestión de Usuarios') }}
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
                            <h3 class="text-lg font-bold text-slate-800">Listado de Usuarios</h3>

                            @if(request('trashed'))
                                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                                    Ocultar Eliminados
                                </a>
                            @else
                                <a href="{{ route('users.index', ['trashed' => 'true']) }}" class="btn btn-secondary btn-sm">
                                    Ver Eliminados
                                </a>
                            @endif
                        </div>

                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            Nuevo Usuario
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Institución</th>
                                    <th>Rol(es)</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center" width="160">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td class="font-semibold text-slate-800">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->institucion)
                                                <span class="badge badge-success" title="{{ $user->institucion->nombre }}">
                                                    Ente Adscrito
                                                </span>
                                            @elseif($user->jefatura)
                                                <span class="badge badge-info" title="{{ $user->jefatura->nombre }}">
                                                    Consultoría Jurídica
                                                </span>
                                            @else
                                                <span class="text-xs italic text-slate-400">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($user->roles as $role)
                                                    <span class="badge badge-neutral">{{ $role->name }}</span>
                                                @empty
                                                    <span class="text-xs italic text-slate-400">Sin rol</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($user->trashed())
                                                <span class="badge badge-danger uppercase tracking-wider">Eliminado</span>
                                            @elseif($user->status)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-warning">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2">
                                                @if($user->trashed())
                                                    <form action="{{ route('users.restore', $user) }}" method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Restaurar Usuario?', text: 'Este usuario volverá a estar activo en el sistema.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, restaurar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline-flex">
                                                        @csrf
                                                        @method('PATCH')
                                                        <x-action-button type="submit"
                                                            action="Restaurar usuario"
                                                            icon="fa-solid fa-rotate-left"
                                                            tone="create" />
                                                    </form>
                                                @else
                                                    <x-action-button :href="route('users.edit', $user)"
                                                        action="Editar usuario"
                                                        icon="fa-solid fa-pen-to-square"
                                                        tone="edit" />

                                                    @if($user->id !== auth()->id() && !$user->hasRole('Super Admin'))
                                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                            onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Se eliminará este usuario del sistema.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline-flex">
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-action-button type="submit"
                                                                action="Eliminar usuario"
                                                                icon="fa-solid fa-trash-can"
                                                                tone="delete" />
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-500">No hay usuarios registrados que coincidan con la búsqueda.</td>
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
