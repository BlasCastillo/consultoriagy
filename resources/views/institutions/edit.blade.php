<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Editar Institución') }}: {{ $institution->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">
                    
                    <form method="POST" action="{{ route('institutions.update', $institution) }}" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="nombre" class="block font-medium text-sm text-slate-700">Nombre de la Institución</label>
                            <input id="nombre" class="val-texto-limpio block mt-1 w-full border-slate-300 rounded-md shadow-sm" type="text" name="nombre" value="{{ old('nombre', $institution->nombre) }}" maxlength="255" required autofocus autocomplete="off" />
                            @error('nombre') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="siglas" class="block font-medium text-sm text-slate-700">Siglas (Opcional)</label>
                            <input id="siglas" class="val-texto-limpio block mt-1 w-full border-slate-300 rounded-md shadow-sm" type="text" name="siglas" value="{{ old('siglas', $institution->siglas) }}" maxlength="50" autocomplete="off" />
                            @error('siglas') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tipo_institucion_id" class="block font-medium text-sm text-slate-700">Tipo de Institución</label>
                            <select id="tipo_institucion_id" name="tipo_institucion_id" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm" required>
                                <option value="" disabled {{ old('tipo_institucion_id', $institution->tipo_institucion_id) ? '' : 'selected' }}>Seleccione un Tipo</option>
                                @foreach($tipoInstituciones as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_institucion_id', $institution->tipo_institucion_id) == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_institucion_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Estatus -->
                        <div class="mb-4 pl-1 mt-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="estatus" value="0">
                                <input type="checkbox" name="estatus" value="1" class="sr-only peer" {{ old('estatus', $institution->estatus) ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ms-3 text-sm font-medium text-slate-700">Institución Activa</span>
                            </label>
                            @error('estatus') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-4 border-t border-slate-200">
                            <a href="{{ route('institutions.index') }}" class="btn btn-back">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary ml-3">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
