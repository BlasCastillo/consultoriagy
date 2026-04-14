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

                    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
                        <p class="text-slate-600">Archivos ZIP generados por Spatie Backup (Base de datos y archivos públicos).</p>
                        <form action="{{ route('backups.create') }}" method="POST" onsubmit="return confirm('Generar un backup puede tardar unos momentos. ¿Continuar?');">
                            @csrf
                            <button type="submit" class="bg-[#1e293b] hover:bg-[#0f172a] text-white font-bold py-2 px-4 rounded transition duration-150 shadow-sm flex items-center" style="background-color: #1e293b; color: white;">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Generar Ahora
                            </button>
                        </form>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-slate-300">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-300">
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">Archivo</th>
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">Tamaño</th>
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">Última Modificación</th>
                                    <th class="py-3 px-4 text-center font-semibold text-slate-700">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                    <tr class="border-b border-slate-200 hover:bg-slate-50">
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
                                            <div class="flex space-x-3 justify-center items-center">
                                                <!-- Icono Descargar -->
                                                <a href="{{ route('backups.download', $backup['file_name']) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="Descargar">
                                                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                </a>

                                                <!-- Icono Eliminar -->
                                                <form action="{{ route('backups.destroy', $backup['file_name']) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este archivo permanentemente?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" title="Eliminar">
                                                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
