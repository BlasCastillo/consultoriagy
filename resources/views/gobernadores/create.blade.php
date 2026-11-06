<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Crear Gobernador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('gobernadores.store') }}" method="POST" autocomplete="off">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="titulo_id" class="block text-sm font-medium text-slate-700">Título</label>
                            <select name="titulo_id" id="titulo_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" required>
                                <option value="">Seleccione un título</option>
                                @foreach($titulos as $titulo)
                                    <option value="{{ $titulo->id }}" {{ old('titulo_id') == $titulo->id ? 'selected' : '' }}>{{ $titulo->abreviatura }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="nombres" class="block text-sm font-medium text-slate-700">Nombres</label>
                            <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" maxlength="255" class="val-solo-letras mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="apellidos" class="block text-sm font-medium text-slate-700">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" maxlength="255" class="val-solo-letras mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" required>
                        </div>

                        <div class="mb-4">
                            <label for="decreto_nombramiento" class="block text-sm font-medium text-slate-700">Decreto de Nombramiento (Opcional)</label>
                            <input type="text" name="decreto_nombramiento" id="decreto_nombramiento" value="{{ old('decreto_nombramiento') }}" maxlength="255" class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                        </div>
                        
                        <div class="mb-6 flex items-center">
                            <input type="checkbox" name="estado" id="estado" value="1" {{ old('estado') ? 'checked' : '' }} class="h-4 w-4 text-slate-800 border-slate-300 rounded focus:ring-slate-800">
                            <label for="estado" class="ml-2 block text-sm text-slate-700">Gobernador Activo <span class="text-slate-400 text-xs">(Desactivará a otros automáticamente)</span></label>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('gobernadores.index') }}" class="btn btn-back">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar Gobernador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>