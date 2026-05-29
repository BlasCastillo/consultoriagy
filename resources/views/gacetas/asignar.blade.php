<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asignar Digitalizador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4 text-blue-900 border-b pb-2">Recepción Física y Asignación</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-md mb-6 text-sm border border-gray-200 flex justify-between items-center">
                        <div>
                            <p class="text-2xl font-bold text-blue-900">Gaceta Nro. {{ str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) }}</p>
                            <p><strong>Año:</strong> {{ $gaceta->anio }}</p>
                        </div>
                        <div class="text-right">
                            <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-yellow-400">Reservada</span>
                        </div>
                    </div>

                    <form action="{{ route('gacetas.asignar.save', $gaceta->id) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="fecha_recepcion_fisica" class="block text-sm font-medium text-gray-700">Fecha de Recepción Física <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_recepcion_fisica" id="fecha_recepcion_fisica" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                            </div>

                            <div>
                                <label for="digitalizador_id" class="block text-sm font-medium text-gray-700">Seleccionar Digitalizador <span class="text-red-500">*</span></label>
                                <select name="digitalizador_id" id="digitalizador_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                    <option value="">-- Seleccione Digitalizador --</option>
                                    @foreach($digitalizadores as $digi)
                                        <option value="{{ $digi->id }}">{{ $digi->name }} ({{ $digi->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-2">
                            <a href="{{ route('gacetas.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-800 transition">Confirmar Recepción y Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
