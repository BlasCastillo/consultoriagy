<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Gestión de Instituciones') }}
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
                        <div>
                            @if(request('trashed'))
                                <a href="{{ route('institutions.index') }}" class="btn btn-secondary">
                                    Volver a Activas
                                </a>
                            @else
                                <a href="{{ route('institutions.index', ['trashed' => 'true']) }}" class="btn btn-neutral">
                                    Ver Eliminadas
                                </a>
                            @endif
                        </div>
                        <a href="{{ route('institutions.create') }}" class="btn btn-primary">
                            Registrar Nueva Institución
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>SIGLAS</th>
                                    <th>Tipo</th>
                                    <th>Estatus</th>
                                    <th width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($institutions as $institution)
                                    <tr>
                                        <td>{{ $institution->nombre }}</td>
                                        <td>{{ $institution->siglas ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $institution->tipoInstitucion->nombre ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($institution->trashed())
                                                <span class="badge badge-danger uppercase tracking-wider">Eliminado</span>
                                            @elseif($institution->estatus)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                @if($institution->trashed())
                                                    <form action="{{ route('institutions.restore', $institution) }}" method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Restaurar institución?', text: 'Se restaurará esta institución.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Restaurar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline-flex">
                                                        @csrf
                                                        @method('PATCH')
                                                        <x-action-button type="submit"
                                                            action="Restaurar institución"
                                                            icon="fa-solid fa-rotate-left"
                                                            tone="create" />
                                                    </form>
                                                @else
                                                    <x-action-button :href="route('institutions.edit', $institution)"
                                                        action="Editar institución"
                                                        icon="fa-solid fa-pen-to-square"
                                                        tone="edit" />
                                                    <form action="{{ route('institutions.destroy', $institution) }}" method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Acción irreversible.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline-flex">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-action-button type="submit"
                                                            action="Eliminar institución"
                                                            icon="fa-solid fa-trash-can"
                                                            tone="delete" />
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-500">No hay instituciones registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $institutions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
