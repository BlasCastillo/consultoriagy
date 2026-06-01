<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Mis Solicitudes y Actos Administrativos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SweetAlert2 Toasts --}}
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
                            icon: 'success', title: "{!! addslashes(session('success')) !!}"
                        });
                    });
                </script>
            @endif
            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
                            icon: 'error', title: "{!! addslashes(session('error')) !!}"
                        });
                    });
                </script>
            @endif

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div class="flex flex-col">
                        <h3 class="text-lg font-bold text-slate-800">Seguimiento de Documentos</h3>
                        <span class="text-sm text-slate-500 mt-1">Institución: <strong class="text-slate-800">{{ auth()->user()->institution->name ?? 'N/A' }}</strong></span>
                    </div>
                    <a href="{{ route('solicitudes.create') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nueva Solicitud
                    </a>
                </div>

                <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                    <table class="min-w-full">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Fecha Envío</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Tipo y N° de Acto</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Descripción</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Gaceta Asignada</th>
                                <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Estado de Gaceta</th>
                                <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider" width="120">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sumarios as $sumario)
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 align-middle text-sm font-medium text-slate-600">
                                        {{ $sumario->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm font-medium text-slate-800">
                                        {{ $sumario->tipo_acto }} <br>
                                        <span class="text-slate-500 font-normal">{{ $sumario->numero_acto }}</span>
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm text-slate-600 max-w-xs truncate" title="{{ $sumario->descripcion }}">
                                        {{ Str::limit($sumario->descripcion, 50) }}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm text-slate-600">
                                        @if($sumario->gaceta->numero > 0)
                                            <span class="font-bold text-slate-800">Nro. {{ str_pad($sumario->gaceta->numero, 4, '0', STR_PAD_LEFT) }} / {{ $sumario->gaceta->anio }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Por Asignar</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-middle text-center text-sm">
                                        @if($sumario->gaceta->estado === 'Publicada')
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Publicada (Disponible)
                                            </span>
                                        @elseif($sumario->gaceta->dias_retraso > 0)
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200" title="Retraso de {{ $sumario->gaceta->dias_retraso }} días">
                                                Retrasada
                                            </span>
                                        @elseif(in_array($sumario->gaceta->estado, ['Reservada', 'En Firma Física', 'Recibida Física', 'En Escaneo', 'Por Aprobar']))
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                                En Proceso ({{ $sumario->gaceta->estado }})
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                {{ $sumario->gaceta->estado }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-middle text-center">
                                        <div class="flex justify-center items-center">
                                            @if($sumario->gaceta->estado === 'Publicada' && $sumario->gaceta->ruta_archivo)
                                                <a href="{{ asset('gacetas_pdf/' . $sumario->gaceta->ruta_archivo) }}" target="_blank" download class="bg-slate-800 hover:bg-slate-900 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Descargar PDF">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-xs font-medium italic">Pendiente</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-sm text-slate-500">No tienes actos administrativos registrados.</td>
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