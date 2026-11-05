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

            <div class="card p-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div class="flex flex-col">
                        <h3 class="text-lg font-bold text-slate-800">Seguimiento de Documentos</h3>
                        <span class="text-sm text-slate-500 mt-1">Institución: <strong class="text-slate-800">{{ auth()->user()->institucion->nombre ?? 'Sin Institución Asignada' }}</strong></span>
                    </div>
                    <a href="{{ route('solicitudes.create') }}" class="btn btn-primary">
                        Nuevo Apartado
                    </a>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th>Fecha Envío</th>
                                <th>Tipo y N° de Acto</th>
                                <th>Descripción</th>
                                <th>Gaceta Asignada</th>
                                <th class="text-center">Estado de Gaceta</th>
                                <th class="text-center" width="120">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                                <tr>
                                    <td class="whitespace-nowrap font-medium text-slate-600">
                                        {{ $solicitud->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="font-medium text-slate-800">
                                        <ul class="list-disc pl-4">
                                        @foreach($solicitud->actos as $acto)
                                            <li>{{ $acto->tipo_acto }} <span class="font-normal text-slate-500">N° {{ $acto->numero }}</span></li>
                                        @endforeach
                                        </ul>
                                    </td>
                                    <td class="max-w-xs truncate text-slate-600" title="{{ $solicitud->titulo_acto }}">
                                        {{ Str::limit($solicitud->titulo_acto, 50) }}
                                    </td>
                                    <td class="text-slate-600">
                                        @if($solicitud->gaceta_id && $solicitud->gaceta && $solicitud->gaceta->numero > 0)
                                            <span class="font-bold text-slate-800">Nro. {{ str_pad($solicitud->gaceta->numero, 4, '0', STR_PAD_LEFT) }} / {{ $solicitud->gaceta->anio }}</span>
                                        @else
                                            <span class="italic text-slate-400">Por Asignar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($solicitud->estado == 'Aprobada')
                                            <span class="badge badge-success">
                                                Aprobada
                                            </span>
                                        @elseif($solicitud->estado == 'Rechazada')
                                            <span class="badge badge-danger">
                                                Rechazada
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="btn btn-primary btn-sm">
                                                Ver Detalles
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500">No tienes actos administrativos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>