<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Evaluar Checklist - Gaceta Solicitada') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body text-slate-900">
                    @if (session('info'))
                        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                            {{ session('info') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h3 class="mb-4 border-b border-slate-200 pb-2 text-lg font-bold text-slate-800">Verificación de Requisitos</h3>
                    <p class="mb-4 text-sm text-slate-600">Por favor, verifique que la solicitud cumpla con todos los requisitos necesarios para reservar el número de Gaceta.</p>

                    <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
                        <p><strong>Institución Solicitante:</strong> {{ $gaceta->solicitudes->first()->institucion->nombre ?? 'N/A' }}</p>
                        <p><strong>Tipo:</strong> {{ $gaceta->tipo }}</p>
                        <p><strong>Fecha Solicitud:</strong> {{ $gaceta->created_at->format('d/m/Y') }}</p>
                    </div>

                    <form action="{{ route('gacetas.checklist.save', $gaceta->id) }}" method="POST">
                        @csrf

                        <div class="mb-8 rounded-lg border border-blue-200 bg-blue-50 p-4">
                            <h4 class="mb-4 border-b border-blue-200 pb-2 text-base font-bold text-blue-900">Completar Datos Administrativos de Gaceta</h4>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Año <span class="text-red-500">*</span></label>
                                    <input type="number" name="anio" min="1900" max="2100" required value="{{ old('anio', $gaceta->anio ?? date('Y')) }}"
                                        class="val-numerico mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Tipo de Gaceta <span class="text-red-500">*</span></label>
                                    <select name="tipo" required
                                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="Ordinaria" {{ old('tipo', $gaceta->tipo) == 'Ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                                        <option value="Extraordinaria" {{ old('tipo', $gaceta->tipo) == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Gobernador Firmante <span class="text-red-500">*</span></label>
                                    <select name="gobernador_id" required
                                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($gobernadores as $gob)
                                            <option value="{{ $gob->id }}" {{ old('gobernador_id', $gaceta->gobernador_id) == $gob->id ? 'selected' : '' }}>{{ $gob->titulo->abreviatura }} {{ $gob->nombres }} {{ $gob->apellidos }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Año Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="anio_politico" maxlength="100" required value="{{ old('anio_politico', $gaceta->ano_politico ?? $gaceta->anio_politico) }}" placeholder="Ej: 168°"
                                        class="val-texto-limpio mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Mes Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="mes_politico" maxlength="100" required value="{{ old('mes_politico', $gaceta->mes_politico) }}" placeholder="Ej: 120°"
                                        class="val-texto-limpio mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Fecha de Recepción Física <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_recepcion_fisica" required value="{{ old('fecha_recepcion_fisica', date('Y-m-d')) }}"
                                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        @php
                            $checklist = is_array($gaceta->checklist) ? $gaceta->checklist : [];
                        @endphp

                        <h4 class="mb-3 border-b border-slate-200 pb-2 text-base font-bold text-slate-800">Validación de Requisitos</h4>
                        <div class="space-y-4">
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[firma_fisica]" value="1" {{ isset($checklist['firma_fisica']) && $checklist['firma_fisica'] ? 'checked' : '' }} class="rounded border-slate-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-slate-700">El documento físico tiene la firma autorizada original.</span>
                            </label>

                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[sello_humedo]" value="1" {{ isset($checklist['sello_humedo']) && $checklist['sello_humedo'] ? 'checked' : '' }} class="rounded border-slate-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-slate-700">El documento físico tiene el sello húmedo de la institución.</span>
                            </label>

                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[formato_correcto]" value="1" {{ isset($checklist['formato_correcto']) && $checklist['formato_correcto'] ? 'checked' : '' }} class="rounded border-slate-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-slate-700">El formato y la numeración interna del documento son correctos.</span>
                            </label>

                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="checklist[respaldo_digital]" value="1" {{ isset($checklist['respaldo_digital']) && $checklist['respaldo_digital'] ? 'checked' : '' }} class="rounded border-slate-300 text-blue-900 shadow-sm focus:ring-blue-900">
                                <span class="text-slate-700">Se adjuntó el respaldo digital (Word/PDF Editable) para el sumario.</span>
                            </label>
                        </div>

                        <div class="mt-8 flex justify-end gap-2">
                            <a href="{{ route('gacetas.index') }}" class="btn btn-back">
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-check" aria-hidden="true"></i> Guardar y Validar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
