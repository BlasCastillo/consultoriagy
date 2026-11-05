<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Estructura Interna') }}
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
                                <a href="{{ route('jefaturas.index') }}" class="btn btn-secondary">
                                    Volver a Activas
                                </a>
                            @else
                                <a href="{{ route('jefaturas.index', ['trashed' => 'true']) }}" class="btn btn-neutral">
                                    Ver Eliminadas
                                </a>
                            @endif
                        </div>
                        <div class="flex flex-col items-center gap-2 sm:flex-row">
                            <a href="{{ route('jefaturas.pdf') }}" target="_blank" class="btn btn-secondary">
                                Exportar Organigrama PDF
                            </a>
                            <a href="{{ route('jefaturas.create') }}" class="btn btn-primary">
                                Registrar Nueva Unidad
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Nivel</th>
                                    <th>Dependencia</th>
                                    <th class="text-center" width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jefaturas as $jefatura)
                                    <tr>
                                        <td class="font-medium text-slate-700">{{ $jefatura->nombre }}</td>
                                        <td>
                                            <span class="badge badge-neutral font-bold">
                                                Nivel {{ $jefatura->nivel }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($jefatura->unidadSuperior)
                                                <span class="font-medium text-blue-800">{{ $jefatura->unidadSuperior->nombre }}</span>
                                            @else
                                                <span class="font-semibold text-emerald-700">Máxima Autoridad</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2">
                                                @if($jefatura->trashed())
                                                    <form action="{{ route('jefaturas.restore', $jefatura) }}" method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Restaurar unidad?', text: 'Se restaurará esta unidad interna.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Restaurar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-primary btn-sm">Restaurar</button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('jefaturas.edit', $jefatura) }}" class="btn btn-warning btn-sm">Editar</a>
                                                    <form action="{{ route('jefaturas.destroy', $jefatura) }}" method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Acción irreversible.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-500">No hay unidades internas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $jefaturas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
