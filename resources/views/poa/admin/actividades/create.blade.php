<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Agregar Actividad al POA') }} - {{ $poa->anio }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('actividades.store', $poa->id) }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Descripción de la Actividad</label>
                            <textarea name="descripcion" required rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Partida Presupuestaria</label>
                                <select name="partida_presupuestaria" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="">N/A - Sin Afectación Presupuestaria</option>
                                    <option value="401" {{ old('partida_presupuestaria') == '401' ? 'selected' : '' }}>401 - Gastos de Personal</option>
                                    <option value="402" {{ old('partida_presupuestaria') == '402' ? 'selected' : '' }}>402 - Materiales, Suministros y Mercancías</option>
                                    <option value="403" {{ old('partida_presupuestaria') == '403' ? 'selected' : '' }}>403 - Servicios no Personales</option>
                                    <option value="404" {{ old('partida_presupuestaria') == '404' ? 'selected' : '' }}>404 - Activos Reales</option>
                                    <option value="407" {{ old('partida_presupuestaria') == '407' ? 'selected' : '' }}>407 - Transferencias y Donaciones</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Nombre del Indicador</label>
                                <select name="indicador_nombre" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="" disabled selected>Seleccione un Indicador...</option>
                                    <option value="Gestión de la Calidad" {{ old('indicador_nombre') == 'Gestión de la Calidad' ? 'selected' : '' }}>Gestión de la Calidad</option>
                                    <option value="Porcentaje de Ejecución Física" {{ old('indicador_nombre') == 'Porcentaje de Ejecución Física' ? 'selected' : '' }}>Porcentaje de Ejecución Física</option>
                                    <option value="Número de Documentos/Gacetas Publicadas" {{ old('indicador_nombre') == 'Número de Documentos/Gacetas Publicadas' ? 'selected' : '' }}>Número de Documentos/Gacetas Publicadas</option>
                                    <option value="Número de Personas Atendidas/Beneficiadas" {{ old('indicador_nombre') == 'Número de Personas Atendidas/Beneficiadas' ? 'selected' : '' }}>Número de Personas Atendidas/Beneficiadas</option>
                                    <option value="Número de Actividades Realizadas" {{ old('indicador_nombre') == 'Número de Actividades Realizadas' ? 'selected' : '' }}>Número de Actividades Realizadas</option>
                                    <option value="Tasa de Cumplimiento de Metas" {{ old('indicador_nombre') == 'Tasa de Cumplimiento de Metas' ? 'selected' : '' }}>Tasa de Cumplimiento de Metas</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Unidad de Medida</label>
                                <select name="unidad_medida" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="" disabled selected>Seleccione Unidad...</option>
                                    <option value="Documento / Informe" {{ old('unidad_medida') == 'Documento / Informe' ? 'selected' : '' }}>Documento / Informe</option>
                                    <option value="Gaceta Oficial" {{ old('unidad_medida') == 'Gaceta Oficial' ? 'selected' : '' }}>Gaceta Oficial</option>
                                    <option value="Actividad / Evento" {{ old('unidad_medida') == 'Actividad / Evento' ? 'selected' : '' }}>Actividad / Evento</option>
                                    <option value="Expediente Jurídico" {{ old('unidad_medida') == 'Expediente Jurídico' ? 'selected' : '' }}>Expediente Jurídico</option>
                                    <option value="Persona Atendida" {{ old('unidad_medida') == 'Persona Atendida' ? 'selected' : '' }}>Persona Atendida</option>
                                    <option value="Porcentaje (%)" {{ old('unidad_medida') == 'Porcentaje (%)' ? 'selected' : '' }}>Porcentaje (%)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Medios de Verificación</label>
                                <select name="medios_verificacion" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="" disabled selected>Seleccione Medio...</option>
                                    <option value="Ficha de Actividad" {{ old('medios_verificacion') == 'Ficha de Actividad' ? 'selected' : '' }}>Ficha de Actividad</option>
                                    <option value="Memoria Fotográfica" {{ old('medios_verificacion') == 'Memoria Fotográfica' ? 'selected' : '' }}>Memoria Fotográfica</option>
                                    <option value="Gaceta Oficial Publicada" {{ old('medios_verificacion') == 'Gaceta Oficial Publicada' ? 'selected' : '' }}>Gaceta Oficial Publicada</option>
                                    <option value="Informe de Gestión" {{ old('medios_verificacion') == 'Informe de Gestión' ? 'selected' : '' }}>Informe de Gestión</option>
                                    <option value="Listado de Asistencia" {{ old('medios_verificacion') == 'Listado de Asistencia' ? 'selected' : '' }}>Listado de Asistencia</option>
                                    <option value="Reporte del Sistema" {{ old('medios_verificacion') == 'Reporte del Sistema' ? 'selected' : '' }}>Reporte del Sistema</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Frecuencia de Lectura</label>
                                <select name="frecuencia_lectura" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="Trimestral" selected>Trimestral</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Formulación del Indicador</label>
                                <select name="formulacion" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="" disabled selected>Seleccione Fórmula...</option>
                                    <option value="Suma Acumulada" {{ old('formulacion') == 'Suma Acumulada' ? 'selected' : '' }}>Suma Acumulada</option>
                                    <option value="Porcentaje de Ejecución: (Ejecutado / Programado) * 100" {{ old('formulacion') == 'Porcentaje de Ejecución: (Ejecutado / Programado) * 100' ? 'selected' : '' }}>Porcentaje de Ejecución: (Ejecutado / Programado) * 100</option>
                                    <option value="Promedio de Ejecución" {{ old('formulacion') == 'Promedio de Ejecución' ? 'selected' : '' }}>Promedio de Ejecución</option>
                                    <option value="N/A - Aplica directo al Valor Absoluto" {{ old('formulacion') == 'N/A - Aplica directo al Valor Absoluto' ? 'selected' : '' }}>N/A - Aplica directo al Valor Absoluto</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-6 rounded-md border border-slate-200 shadow-inner">
                            <h3 class="text-md font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Programación de Metas</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 text-center">Trim. 1</label>
                                    <input type="number" name="q1" required min="0" value="{{ old('q1', 0) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-center text-lg font-semibold text-slate-800">
                                    <p class="text-xs text-slate-500 mt-1 text-center font-medium">Ene - Feb - Mar</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 text-center">Trim. 2</label>
                                    <input type="number" name="q2" required min="0" value="{{ old('q2', 0) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-center text-lg font-semibold text-slate-800">
                                    <p class="text-xs text-slate-500 mt-1 text-center font-medium">Abr - May - Jun</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 text-center">Trim. 3</label>
                                    <input type="number" name="q3" required min="0" value="{{ old('q3', 0) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-center text-lg font-semibold text-slate-800">
                                    <p class="text-xs text-slate-500 mt-1 text-center font-medium">Jul - Ago - Sep</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 text-center">Trim. 4</label>
                                    <input type="number" name="q4" required min="0" value="{{ old('q4', 0) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-center text-lg font-semibold text-slate-800">
                                    <p class="text-xs text-slate-500 mt-1 text-center font-medium">Oct - Nov - Dic</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-slate-200">
                            <a href="{{ route('poa.show', $poa->id) }}" class="mr-4 text-sm font-medium text-slate-600 hover:text-slate-500">Cancelar</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 shadow-sm">
                                Guardar Actividad y Metas
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
