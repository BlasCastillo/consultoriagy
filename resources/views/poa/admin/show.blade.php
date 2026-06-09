<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Detalle del POA') }} - {{ $poa->anio }}
            </h2>
            <div class="space-x-2 flex">
                <a href="{{ route('poa.index') }}"
                    class="px-4 py-2 bg-slate-200 border border-transparent rounded-md font-semibold text-xs text-slate-800 uppercase tracking-widest hover:bg-slate-300">
                    Volver
                </a>
                <a href="{{ route('poa.matriz_pdf', $poa->id) }}" target="_blank"
                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-sm flex items-center">
                    <i class="fa-solid fa-file-pdf mr-2"></i> Exportar PDF
                </a>
                <a href="{{ route('actividades.create', $poa->id) }}"
                    class="px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 shadow-sm">
                    Agregar Actividad
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Datos del POA -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Jefatura Asignada</p>
                        <p class="text-lg font-bold text-slate-800">{{ $poa->jefatura->nombre ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Estado</p>
                        <p class="text-lg font-bold text-slate-800">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($poa->estado == 'Aprobado') bg-blue-100 text-blue-800 
                                @elseif($poa->estado == 'Ejecucion') bg-green-100 text-green-800 
                                @elseif($poa->estado == 'Cerrado') bg-slate-100 text-slate-800 
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $poa->estado }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabla de Actividades -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Actividades y Metas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-100 text-slate-600 border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">
                                        Descripción</th>
                                    <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider">Partida /
                                        Indicador</th>
                                    <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider"
                                        width="80">Trim. 1</th>
                                    <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider"
                                        width="80">Trim. 2</th>
                                    <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider"
                                        width="80">Trim. 3</th>
                                    <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider"
                                        width="80">Trim. 4</th>
                                    <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider"
                                        width="90">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($poa->actividades as $actividad)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="py-4 px-4 text-sm text-slate-800 font-medium">
                                            {{ $actividad->descripcion }}
                                        </td>
                                        <td class="py-4 px-4 text-sm text-slate-600">
                                            <div class="text-xs mb-1"><strong class="text-slate-800">P:</strong>
                                                {{ $actividad->partida_presupuestaria ?? 'N/A' }}</div>
                                            <div class="text-xs"><strong class="text-slate-800">I:</strong>
                                                {{ $actividad->indicador_nombre ?? 'N/A' }}</div>
                                        </td>

                                        @for($i = 1; $i <= 4; $i++)
                                            @php
                                                $meta = $actividad->metasTrimestrales->where('trimestre', $i)->first();
                                                $ejecutada = $meta ? $meta->ejecutada : 0;
                                                $programada = $meta ? $meta->meta_actual : 0;
                                                $cumplida = $programada > 0 && $ejecutada >= $programada;
                                            @endphp
                                            <td
                                                class="py-4 px-4 text-center bg-slate-50/50 border-l border-slate-100 relative group">
                                                <div
                                                    class="text-sm font-bold mb-1 {{ $cumplida ? 'text-green-600' : 'text-slate-700' }}">
                                                    {{ $ejecutada }} / {{ $programada }}
                                                </div>
                                                @if($programada > 0)
                                                    <div class="w-full bg-slate-200 rounded-full h-1.5 mx-auto max-w-[50px] mb-2">
                                                        <div class="bg-blue-600 h-1.5 rounded-full"
                                                            style="width: {{ min(100, ($ejecutada / $programada) * 100) }}%"></div>
                                                    </div>
                                                @else
                                                    <div class="text-xs text-slate-400 italic mb-2">N/A</div>
                                                @endif
                                            </td>
                                        @endfor

                                        <td class="py-4 px-4 text-center border-l border-slate-100">
                                            <div class="flex justify-center space-x-3">
                                                <a href="{{ route('actividades.replanificar', $actividad->id) }}"
                                                    class="text-slate-500 hover:text-blue-600 transition"
                                                    title="Replanificar metas">
                                                    <i class="fa-solid fa-calendar-days text-base"></i>
                                                </a>
                                                <a href="{{ route('actividades.evidencias', $actividad->id) }}"
                                                    class="text-slate-500 hover:text-emerald-600 transition"
                                                    title="Gestionar Evidencias">
                                                    <i class="fa-solid fa-folder-open text-base"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-500 italic">No hay actividades
                                            registradas en este POA. Haz clic en "Agregar Actividad" para comenzar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>