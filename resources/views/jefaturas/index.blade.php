<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Estructura Interna') }}
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

                    <div class="mb-4 flex justify-between items-center">
                        <div>
                            @if(request('trashed'))
                                <a href="{{ route('jefaturas.index') }}"
                                    class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-200 hover:bg-slate-300 text-slate-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Volver a Activas
                                </a>
                            @else
                                <a href="{{ route('jefaturas.index', ['trashed' => 'true']) }}"
                                    class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-500 hover:bg-slate-600 text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    Ver Eliminadas
                                </a>
                            @endif
                        </div>
                        <div class="flex items-center">
                            <a href="{{ route('jefaturas.pdf') }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow mr-2">Exportar Organigrama PDF</a>
                            <a href="{{ route('jefaturas.create') }}"
                                class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Registrar Nueva Unidad
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                        <table class="min-w-full">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Nombre</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Nivel</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Dependencia</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider text-center" width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jefaturas as $jefatura)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-sm text-slate-600 font-medium">{{ $jefatura->nombre }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            <span class="bg-slate-200 text-slate-800 text-xs px-2 py-1 rounded font-bold">
                                                Nivel {{ $jefatura->nivel }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            @if($jefatura->unidadSuperior)
                                                <span class="text-blue-600 font-medium">{{ $jefatura->unidadSuperior->nombre }}</span>
                                            @else
                                                <span class="text-emerald-600 font-bold">Máxima Autoridad</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            <div class="flex space-x-2 items-center justify-center">
                                                @if($jefatura->trashed())
                                                    <form action="{{ route('jefaturas.restore', $jefatura) }}"
                                                        method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Restaurar unidad?', text: 'Se restaurará esta unidad interna.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Restaurar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                                        class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white"
                                                            title="Restaurar">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('jefaturas.edit', $jefatura) }}"
                                                        class="p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-amber-500 hover:bg-amber-600 text-white"
                                                        title="Editar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route('jefaturas.destroy', $jefatura) }}"
                                                        method="POST"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Estás seguro?', text: 'Acción irreversible.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                                        class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-red-600 hover:bg-red-700 text-white"
                                                            title="Eliminar">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-500">No hay unidades internas registradas.</td>
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
