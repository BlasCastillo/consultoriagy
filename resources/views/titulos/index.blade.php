<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Títulos') }}
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
                        <h3 class="text-lg font-bold text-slate-800">Listado de Títulos</h3>
                        <a href="{{ route('titulos.create') }}" class="btn btn-primary">
                            Nuevo Título
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th class="w-16">ID</th>
                                    <th class="w-1/4">Abreviatura</th>
                                    <th>Descripción</th>
                                    <th class="w-24 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($titulos as $titulo)
                                    <tr>
                                        <td>{{ $titulo->id }}</td>
                                        <td class="font-semibold text-slate-800">{{ $titulo->abreviatura }}</td>
                                        <td>{{ $titulo->descripcion ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center">
                                                <a href="{{ route('titulos.edit', $titulo->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-500">No hay títulos registrados.</td>
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
