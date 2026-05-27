<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Gobernador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('gobernadores.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="titulo_id" class="block text-sm font-medium text-gray-700">Título</label>
                            <select name="titulo_id" id="titulo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm" required>
                                <option value="">Seleccione un título</option>
                                @foreach($titulos as $titulo)
                                    <option value="{{ $titulo->id }}">{{ $titulo->abreviatura }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                            <input type="text" name="nombres" id="nombres" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm" required>
                        </div>
                        <div class="mb-4">
                            <label for="apellidos" class="block text-sm font-medium text-gray-700">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm" required>
                        </div>
                        <div class="mb-4 flex items-center">
                            <input type="checkbox" name="estado" id="estado" value="1" class="h-4 w-4 text-blue-900 border-gray-300 rounded focus:ring-blue-900">
                            <label for="estado" class="ml-2 block text-sm text-gray-900">Gobernador Activo (Desactivará a otros automáticamente)</label>
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('gobernadores.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">Cancelar</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 transition ease-in-out duration-150">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
