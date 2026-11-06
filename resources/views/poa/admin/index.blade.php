<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Gestión de Planes Operativos Anuales (POA)') }}
            </h2>
            @hasanyrole(['Super Admin', 'Coordinador de Seguimiento y Control de Políticas Públicas'])
            <a href="{{ route('poa.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Nuevo POA
            </a>
            @else
            @can('crear_poa')
            <a href="{{ route('poa.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Nuevo POA
            </a>
            @endcan
            @endhasanyrole
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="card overflow-hidden">
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
                                                @elseif($poa->estado == 'En Ejecución') bg-green-100 text-green-800
                                                @elseif($poa->estado == 'Cerrado') bg-red-100 text-red-800
                                                @elseif($poa->estado == 'Borrador') bg-slate-100 text-slate-800
                                                @else bg-slate-100 text-slate-800 @endif">
                                                {{ $poa->estado }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center">
                                                <x-action-button :href="route('poa.show', $poa->id)"
                                                    action="Ver detalle del POA"
                                                    icon="fa-solid fa-eye"
                                                    tone="view" />
                                            </div>
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
