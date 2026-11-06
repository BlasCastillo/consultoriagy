<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Copias de Seguridad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">

                    {{-- SweetAlert2 Toasts --}}
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

                    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
                        <p class="text-slate-600">Archivos ZIP generados por Spatie Backup (Base de datos y archivos públicos).</p>
                        <form action="{{ route('backups.create') }}" method="POST" onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Generar backup?', text: 'Esto puede tardar unos momentos.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, generar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-database" aria-hidden="true"></i> Generar Ahora
                            </button>
                        </form>
                    </div>

                    {{-- Tabla estandarizada --}}
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Tamaño</th>
                                    <th>Última Modificación</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                    <tr>
                                        <td class="font-medium text-slate-800">
                                            {{ $backup['file_name'] }}
                                        </td>
                                        <td class="text-slate-600">
                                            {{ $backup['file_size'] }}
                                        </td>
                                        <td class="text-slate-600">
                                            {{ $backup['last_modified'] }}
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <x-action-button :href="route('backups.download', $backup['file_name'])"
                                                    action="Descargar respaldo"
                                                    icon="fa-solid fa-download"
                                                    tone="export" />

                                                <form action="{{ route('backups.destroy', $backup['file_name']) }}" method="POST" onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Acción irreversible.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-action-button type="submit"
                                                        action="Eliminar respaldo"
                                                        icon="fa-solid fa-trash-can"
                                                        tone="delete" />
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center font-medium text-slate-500">No hay respaldos generados todavía.</td>
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
