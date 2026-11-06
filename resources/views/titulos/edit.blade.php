<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Editar Título') }}
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

                    <form action="{{ route('titulos.update', $titulo->id) }}" method="POST" autocomplete="off">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="abreviatura" class="block text-sm font-medium text-slate-700">Abreviatura *</label>
                            <input type="text" name="abreviatura" id="abreviatura" value="{{ old('abreviatura', $titulo->abreviatura) }}" maxlength="50" class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" required>
                        </div>
                        
                        <div class="mb-6">
                            <label for="descripcion" class="block text-sm font-medium text-slate-700">Descripción (Opcional)</label>
                            <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $titulo->descripcion) }}" maxlength="255" class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                        </div>
                        
                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('titulos.index') }}" class="btn btn-back">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Actualizar Título
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>