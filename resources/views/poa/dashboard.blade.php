<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard de Planificación (POA)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-slate-800">Estado General de los POAs</h3>
                        @can('registrar actividad poa')
                            <a href="{{ route('fichas.create') }}" class="px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                                Registrar Actividad
                            </a>
                        @endcan
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($poas as $poa)
                            @php
                                $totalMeta = 0;
                                $totalEjecutada = 0;

                                foreach($poa->actividades as $act) {
                                    foreach($act->metasTrimestrales as $meta) {
                                        $totalMeta += $meta->meta_actual;
                                        $totalEjecutada += $meta->ejecutada;
                                    }
                                }

                                $porcentaje = $totalMeta > 0 ? min(100, round(($totalEjecutada / $totalMeta) * 100)) : 0;
                            @endphp

                            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="text-md font-bold text-slate-800">{{ $poa->jefatura ? $poa->jefatura->nombre : 'POA General' }}</h4>
                                        <p class="text-sm text-slate-500">Año: {{ $poa->anio }} | Tipo: {{ $poa->tipo }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($poa->estado == 'Aprobado') bg-blue-100 text-blue-800 
                                        @elseif($poa->estado == 'Ejecucion') bg-green-100 text-green-800 
                                        @elseif($poa->estado == 'Cerrado') bg-slate-100 text-slate-800 
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $poa->estado }}
                                    </span>
                                </div>
                                
                                <div class="mb-2 flex justify-between text-sm">
                                    <span class="text-slate-600 font-medium">Progreso Anual</span>
                                    <span class="text-slate-800 font-bold">{{ $porcentaje }}%</span>
                                </div>
                                
                                <div class="w-full bg-slate-200 rounded-full h-2.5 mb-4">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $porcentaje }}%"></div>
                                </div>

                                <div class="text-xs text-slate-500 flex justify-between border-t pt-3 border-slate-100">
                                    <span>Ejecutadas: {{ $totalEjecutada }}</span>
                                    <span>Meta: {{ $totalMeta }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-8 text-center text-slate-500">
                                No hay Planes Operativos Anuales registrados.
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
