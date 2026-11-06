<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Nueva Gaveta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden p-6">
                <form action="{{ route('gavetas.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Estante (Nivel Superior)</label>
                        <select name="estante_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                            <option value="">Seleccione un estante...</option>
                            @foreach($estantes as $estante)
                                <option value="{{ $estante->id }}" {{ old('estante_id') == $estante->id ? 'selected' : '' }}>{{ $estante->sede?->nombre ?? 'Sede inhabilitada' }} &rsaquo; {{ $estante->nombre }}</option>
                            @endforeach
                        </select>
                        @error('estante_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Nombre de la Gaveta</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="255" required class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end mt-4">
                        <a href="{{ route('gavetas.index') }}" class="btn btn-back mr-3">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
