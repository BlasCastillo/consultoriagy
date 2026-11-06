<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Gobernadores') }}
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

                    <div class="mb-6 flex flex-col items-center justify-between gap-4 md:flex-row">
                        <h3 class="text-lg font-bold text-slate-800">Listado de Gobernadores</h3>
                        <a href="{{ route('gobernadores.create') }}" class="btn btn-primary">
                            Nuevo Gobernador
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center" width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gobernadores as $gobernador)
                                    <tr>
                                        <td>{{ $gobernador->id }}</td>
                                        <td>{{ $gobernador->titulo->abreviatura ?? 'N/A' }}</td>
                                        <td class="font-semibold text-slate-800">{{ $gobernador->nombres }}</td>
                                        <td class="font-semibold text-slate-800">{{ $gobernador->apellidos }}</td>
                                        <td class="text-center">
                                            @if($gobernador->estado)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center">
                                                <x-action-button :href="route('gobernadores.edit', $gobernador->id)"
                                                    action="Editar gobernador"
                                                    icon="fa-solid fa-pen-to-square"
                                                    tone="edit" />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-500">No hay gobernadores registrados.</td>
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
