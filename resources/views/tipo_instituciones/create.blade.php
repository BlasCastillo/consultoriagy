<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registrar Nuevo Tipo de Institución') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">
                    <form method="POST" action="{{ route('tipo-instituciones.store') }}" autocomplete="off">
                        @csrf

                        <div class="mb-4">
                            <label for="nombre" class="block font-medium text-sm text-slate-700">Nombre del Tipo de Institución</label>
                            <input id="nombre" class="val-texto-limpio block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="text" name="nombre" value="{{ old('nombre') }}" maxlength="255" required autofocus autocomplete="off" />
                            @error('nombre') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('tipo-instituciones.index') }}" class="btn btn-back">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Registrar Tipo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
