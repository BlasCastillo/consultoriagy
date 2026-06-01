<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Gobernadores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-800">Listado de Gobernadores</h3>
                        <a href="{{ route('gobernadores.create') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nuevo Gobernador
                        </a>
                    </div>

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

                    <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                        <table class="min-w-full">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">ID</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Título</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Nombres</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Apellidos</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Estado</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider" width="120">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gobernadores as $gobernador)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-sm text-slate-600">{{ $gobernador->id }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-600">{{ $gobernador->titulo->abreviatura ?? 'N/A' }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-800 font-semibold">{{ $gobernador->nombres }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-800 font-semibold">{{ $gobernador->apellidos }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-600 text-center">
                                            @if($gobernador->estado)
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded border border-green-200">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded border border-red-200">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600 text-center">
                                            <div class="flex item-center justify-center space-x-2">
                                                <a href="{{ route('gobernadores.edit', $gobernador->id) }}" class="p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-amber-500 hover:bg-amber-600 text-white" title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-slate-500">No hay gobernadores registrados.</td>
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