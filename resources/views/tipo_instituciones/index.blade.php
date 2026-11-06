<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Gestión de Tipos de Institución') }}
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

                    <div class="mb-6 flex justify-end">
                        <a href="{{ route('tipo-instituciones.create') }}" class="btn btn-primary">
                            Registrar Nuevo Tipo
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tipoInstituciones as $tipo)
                                    <tr>
                                        <td>{{ $tipo->nombre }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <x-action-button :href="route('tipo-instituciones.edit', $tipo)"
                                                    action="Editar tipo de institución"
                                                    icon="fa-solid fa-pen-to-square"
                                                    tone="edit" />
                                                <form action="{{ route('tipo-instituciones.destroy', $tipo) }}" method="POST"
                                                    onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Acción irreversible.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-action-button type="submit"
                                                        action="Eliminar tipo de institución"
                                                        icon="fa-solid fa-trash-can"
                                                        tone="delete" />
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-6 text-center text-slate-500">No hay tipos de instituciones registradas.</td>
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
