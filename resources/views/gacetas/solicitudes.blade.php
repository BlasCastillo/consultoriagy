<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Solicitudes Entrantes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-slate-800">Solicitudes Pendientes de Revisión</h3>
                </div>

                <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                    <table class="min-w-full">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th
                                    class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">
                                    Institución Solicitante</th>
                                <th
                                    class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">
                                    Fecha Solicitud</th>
                                <th
                                    class="py-3 px-4 text-right text-sm font-semibold uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($gacetas as $gaceta)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $gaceta->sumarios->pluck('institucion.name')->filter()->unique()->implode(', ') ?: 'No especificada' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $gaceta->created_at->format('d/m/Y H:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('gacetas.checklist', $gaceta->id) }}"
                                            class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base">
                                            <i class="fa-solid fa-list-check mr-1"></i> Evaluar Checklist
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"
                                        class="px-6 py-4 text-center text-sm text-gray-500">No hay solicitudes entrantes pendientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
