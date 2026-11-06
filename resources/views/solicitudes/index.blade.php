<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Gestión de Apartados de Publicación') }}
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

            <div class="card p-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-lg font-bold text-slate-800">Listado Global de Apartados de Publicación</h3>
                    @if(auth()->user()->can('crear Solicitud'))
                        <a href="{{ route('solicitudes.create') }}" class="btn btn-primary">
                            Nuevo Apartado
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="table-corporate">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Institución</th>
                                <th>Asunto / Oficio</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                                <tr>
                                    <td class="whitespace-nowrap font-medium text-slate-600">
                                        {{ $solicitud->created_at->format('d/m/Y h:i A') }}
                                    </td>
                                    <td class="font-bold text-slate-600">
                                        {{ $solicitud->institucion->siglas ?? ($solicitud->institucion->nombre ?? 'Súper Admin / Interno') }}
                                    </td>
                                    <td class="max-w-xs truncate text-slate-600" title="{{ $solicitud->titulo_acto }}">
                                        {{ Str::limit($solicitud->titulo_acto, 50) }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $badge = match($solicitud->estado) {
                                                'Pendiente' => 'badge-warning',
                                                'Aprobada' => 'badge-success',
                                                'En Digitalización', 'En Revisión Final' => 'badge-info',
                                                'Publicada' => 'badge-success',
                                                'Rechazada' => 'badge-danger',
                                                default => 'badge-neutral'
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $solicitud->estado }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <x-action-button :href="route('solicitudes.show', $solicitud->id)"
                                                action="Evaluar documento"
                                                icon="fa-solid fa-magnifying-glass-chart"
                                                tone="view" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">No hay solicitudes registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
