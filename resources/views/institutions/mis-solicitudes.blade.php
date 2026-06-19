<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Mis Apartados y Actos Administrativos') }}
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
                        <span class="text-sm text-slate-500 mt-1">Institución: <strong class="text-slate-800">{{ auth()->user()->institucion->nombre ?? 'Sin Institución Asignada' }}</strong></span>
                    </div>
                    <a href="{{ route('solicitudes.create') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        + Nuevo Apartado
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
                            @forelse($solicitudes as $solicitud)
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 align-middle text-sm font-medium text-slate-600">
                                        {{ $solicitud->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm font-medium text-slate-800">
                                        <ul class="list-disc pl-4">
                                        @foreach($solicitud->actos as $acto)
                                            <li>{{ $acto->tipo_acto }} <span class="text-slate-500 font-normal">N° {{ $acto->numero }}</span></li>
                                        @endforeach
                                        </ul>
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm text-slate-600 max-w-xs truncate" title="{{ $solicitud->titulo_acto }}">
                                        {{ Str::limit($solicitud->titulo_acto, 50) }}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm text-slate-600">
                                        @if($solicitud->gaceta_id && $solicitud->gaceta && $solicitud->gaceta->numero > 0)
                                            <span class="font-bold text-slate-800">Nro. {{ str_pad($solicitud->gaceta->numero, 4, '0', STR_PAD_LEFT) }} / {{ $solicitud->gaceta->anio }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Por Asignar</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-middle text-center text-sm">
                                        @if($solicitud->estado == 'Aprobada')
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Aprobada
                                            </span>
                                        @elseif($solicitud->estado == 'Rechazada')
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                                Rechazada
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 align-middle text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-1.5 px-3 rounded text-xs shadow-sm transition">Ver Detalles</a>
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
            </div>
        </div>
    </div>
</x-app-layout>