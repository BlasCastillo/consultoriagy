<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Mis Solicitudes y Actos Administrativos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-slate-800">Seguimiento de Documentos</h3>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">Institución: <strong>{{ auth()->user()->institution->name ?? 'N/A' }}</strong></span>
                        <a href="{{ route('solicitudes.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-md hover:bg-blue-800 transition-colors">
                            <i class="fa-solid fa-plus mr-1"></i> Nueva Solicitud
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Envío</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo y Número de Acto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaceta Asignada</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado de Gaceta</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sumarios as $sumario)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $sumario->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $sumario->tipo_acto }} <br>
                                        <span class="text-gray-500">{{ $sumario->numero_acto }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $sumario->descripcion }}">
                                        {{ Str::limit($sumario->descripcion, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($sumario->gaceta->numero > 0)
                                            Nro. {{ str_pad($sumario->gaceta->numero, 4, '0', STR_PAD_LEFT) }} / {{ $sumario->gaceta->anio }}
                                        @else
                                            <span class="text-gray-400 italic">Por Asignar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($sumario->gaceta->estado === 'Publicada')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                                <i class="fa-solid fa-check-circle mr-1 mt-0.5"></i> Publicada (Disponible)
                                            </span>
                                        @elseif($sumario->gaceta->dias_retraso > 0)
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200" title="Retraso de {{ $sumario->gaceta->dias_retraso }} días">
                                                <i class="fa-solid fa-triangle-exclamation mr-1 mt-0.5"></i> Retrasada
                                            </span>
                                        @elseif(in_array($sumario->gaceta->estado, ['Reservada', 'En Firma Física', 'Recibida Física', 'En Escaneo', 'Por Aprobar']))
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fa-solid fa-spinner mr-1 mt-0.5"></i> En Proceso ({{ $sumario->gaceta->estado }})
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                <i class="fa-solid fa-clock mr-1 mt-0.5"></i> {{ $sumario->gaceta->estado }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($sumario->gaceta->estado === 'Publicada' && $sumario->gaceta->ruta_archivo)
                                            <a href="{{ asset('storage/' . $sumario->gaceta->ruta_archivo) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Descargar PDF</a>
                                        @else
                                            <span class="text-gray-400">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No tienes actos administrativos registrados en gacetas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $sumarios->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
