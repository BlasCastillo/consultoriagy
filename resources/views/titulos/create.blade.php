<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Título') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('titulos.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="abreviatura" class="block text-sm font-medium text-gray-700">Abreviatura *</label>
                            <input type="text" name="abreviatura" id="abreviatura" placeholder="Ej: Lcdo." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm" required>
                        </div>
                        <div class="mb-6">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                            <input type="text" name="descripcion" id="descripcion" placeholder="Ej: Licenciado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('titulos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">Cancelar</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 transition ease-in-out duration-150">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
