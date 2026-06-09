<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Gestión de Planes Operativos Anuales (POA)') }}
            </h2>
            <a href="{{ route('poa.create') }}" class="px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 shadow-sm">
                Nuevo POA
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Año</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Jefatura</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Estado</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider" width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($poas as $poa)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-sm font-semibold text-slate-800">{{ $poa->anio }}</td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            <i class="fa-solid fa-building text-slate-400 mr-1"></i> {{ $poa->jefatura->nombre ?? 'Jefatura Desconocida' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($poa->estado == 'Aprobado') bg-blue-100 text-blue-800 
                                                @elseif($poa->estado == 'Ejecucion') bg-green-100 text-green-800 
                                                @elseif($poa->estado == 'Cerrado') bg-slate-100 text-slate-800 
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $poa->estado }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="{{ route('poa.show', $poa->id) }}" class="px-3 py-1.5 bg-slate-800 text-white rounded text-xs hover:bg-slate-700 transition font-medium">Ver Detalle</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-500 italic">No hay POAs registrados en el sistema para su vista.</td>
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
