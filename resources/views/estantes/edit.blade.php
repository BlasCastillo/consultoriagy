<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Editar Estante') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden p-6">
                <form action="{{ route('estantes.update', $estante) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Sede (Nivel Superior)</label>
                        <select name="sede_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}" {{ $estante->sede_id == $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sede_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700">Nombre del Estante</label>
                        <input type="text" name="nombre" value="{{ $estante->nombre }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end mt-4">
                        <a href="{{ route('estantes.index') }}" class="btn btn-back mr-3">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
