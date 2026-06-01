<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Crear Gobernador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <form action="{{ route('gobernadores.store') }}" method="POST" autocomplete="off">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="titulo_id" class="block text-sm font-medium text-slate-700">Título</label>
                            <select name="titulo_id" id="titulo_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" required>
                                <option value="">Seleccione un título</option>
                                @foreach($titulos as $titulo)
                                    <option value="{{ $titulo->id }}">{{ $titulo->abreviatura }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="nombres" class="block text-sm font-medium text-slate-700">Nombres</label>
                            <input type="text" name="nombres" id="nombres" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="apellidos" class="block text-sm font-medium text-slate-700">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" required>
                        </div>
                        
                        <div class="mb-6 flex items-center">
                            <input type="checkbox" name="estado" id="estado" value="1" class="h-4 w-4 text-slate-800 border-slate-300 rounded focus:ring-slate-800">
                            <label for="estado" class="ml-2 block text-sm text-slate-700">Gobernador Activo <span class="text-slate-400 text-xs">(Desactivará a otros automáticamente)</span></label>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('gobernadores.index') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-200 hover:bg-slate-300 text-slate-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                            <button type="submit" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-800 hover:bg-slate-900 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Guardar Gobernador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>