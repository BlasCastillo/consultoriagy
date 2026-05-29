<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Evaluar Checklist - Gaceta Solicitada') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4 text-blue-900 border-b pb-2">Verificación de Requisitos</h3>
                    <p class="mb-4 text-sm text-gray-600">Por favor, verifique que la solicitud cumpla con todos los requisitos necesarios para reservar el número de Gaceta.</p>
                    
                    <div class="bg-gray-50 p-4 rounded-md mb-6 text-sm border border-gray-200">
                        <p><strong>Institución Solicitante:</strong> {{ $gaceta->sumarios->first()->institucion->nombre ?? 'N/A' }}</p>
                        <p><strong>Tipo:</strong> {{ $gaceta->tipo }}</p>
                        <p><strong>Fecha Solicitud:</strong> {{ $gaceta->created_at->format('d/m/Y') }}</p>
                    </div>

                    <form action="{{ route('gacetas.checklist.save', $gaceta->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-8 border border-blue-200 rounded-md p-4 bg-blue-50">
                            <h4 class="text-md font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Completar Datos Administrativos de Gaceta</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Año <span class="text-red-500">*</span></label>
                                    <input type="number" name="anio" required value="{{ date('Y') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo de Gaceta <span class="text-red-500">*</span></label>
                                    <select name="tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="Ordinaria">Ordinaria</option>
                                        <option value="Extraordinaria">Extraordinaria</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Gobernador Firmante <span class="text-red-500">*</span></label>
                                    <select name="gobernador_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($gobernadores as $gob)
                                            <option value="{{ $gob->id }}">{{ $gob->titulo->abreviatura }} {{ $gob->nombres }} {{ $gob->apellidos }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Año Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="anio_politico" required placeholder="Ej: 168°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mes Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="mes_politico" required placeholder="Ej: 120°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Recepción Física <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_recepcion_fisica" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        @php
                            $checklist = is_array($gaceta->checklist) ? $gaceta->checklist : [];
                        @endphp

                        <h4 class="text-md font-bold text-gray-800 mb-3 border-b pb-2">Validación de Requisitos</h4>
                        <div class="space-y-4">
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[firma_fisica]" value="1" {{ isset($checklist['firma_fisica']) && $checklist['firma_fisica'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-gray-700">El documento físico tiene la firma autorizada original.</span>
                            </label>
                            
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[sello_humedo]" value="1" {{ isset($checklist['sello_humedo']) && $checklist['sello_humedo'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-gray-700">El documento físico tiene el sello húmedo de la institución.</span>
                            </label>

                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[formato_correcto]" value="1" {{ isset($checklist['formato_correcto']) && $checklist['formato_correcto'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-gray-700">El formato y la numeración interna del documento son correctos.</span>
                            </label>
                            
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[respaldo_digital]" value="1" {{ isset($checklist['respaldo_digital']) && $checklist['respaldo_digital'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-gray-700">Se adjuntó el respaldo digital (Word/PDF Editable) para el sumario.</span>
                            </label>
                        </div>

                        <div class="mt-8 flex justify-end gap-2">
                            <a href="{{ route('gacetas.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-800 transition">Guardar y Validar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
