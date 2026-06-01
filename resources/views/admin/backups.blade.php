<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Copias de Seguridad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
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
                        <form action="{{ route('backups.create') }}" method="POST" onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Generar backup?', text: 'Esto puede tardar unos momentos.', icon: 'question', showCancelButton: true, confirmButtonColor: '#1e293b', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, generar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });">
                            @csrf
                            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Generar Ahora
                            </button>
                        </form>
                    </div>

                    {{-- Tabla estandarizada --}}
                    <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                        <table class="min-w-full">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Archivo</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Tamaño</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Última Modificación</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-sm font-medium text-slate-800">
                                            {{ $backup['file_name'] }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            {{ $backup['file_size'] }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            {{ $backup['last_modified'] }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex space-x-2 justify-center items-center">
                                                {{-- Botón Descargar (icon-only) --}}
                                                <a href="{{ route('backups.download', $backup['file_name']) }}" class="bg-slate-500 hover:bg-slate-600 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Descargar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                </a>

                                                {{-- Botón Eliminar (icon-only) --}}
                                                <form action="{{ route('backups.destroy', $backup['file_name']) }}" method="POST" onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Acción irreversible.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Eliminar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-500 font-medium">No hay respaldos generados todavía.</td>
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
