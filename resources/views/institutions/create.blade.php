<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registrar Institución') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">
                    
                    <form method="POST" action="{{ route('institutions.store') }}" autocomplete="off">
                        @csrf

                        <div class="mb-4">
                            <label for="nombre" class="block font-medium text-sm text-slate-700">Nombre de la Institución</label>
                            <input id="nombre" class="val-texto-limpio block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800" type="text" name="nombre" value="{{ old('nombre') }}" maxlength="255" required autofocus autocomplete="off" />
                            @error('nombre') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="siglas" class="block font-medium text-sm text-slate-700">Siglas (Opcional)</label>
                            <input id="siglas" class="val-texto-limpio block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800" type="text" name="siglas" value="{{ old('siglas') }}" maxlength="50" autocomplete="off" />
                            @error('siglas') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tipo_institucion_id" class="block font-medium text-sm text-slate-700">Tipo de Institución</label>
                            <select id="tipo_institucion_id" name="tipo_institucion_id" required class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800">
                                <option value="" disabled selected>Seleccione un Tipo</option>
                                @foreach($tipoInstituciones as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_institucion_id') == $tipo->id ? 'selected' : '' }}>
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
                                <input type="checkbox" name="estatus" value="1" class="sr-only peer" {{ old('estatus', 1) ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ms-3 text-sm font-medium text-slate-700">Institución Activa</span>
                            </label>
                            @error('estatus') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('institutions.index') }}" class="btn btn-back">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Registrar Institución
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>