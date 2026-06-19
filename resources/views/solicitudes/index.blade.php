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

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-lg font-bold text-slate-800">Listado Global de Apartados de Publicación</h3>
                    @if(auth()->user()->can('crear solicitud'))
                        <a href="{{ route('solicitudes.create') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            + Nuevo Apartado
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                    <table class="min-w-full">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Fecha</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Institución</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Asunto / Oficio</th>
                                <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Estado</th>
                                <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider" width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 align-middle text-sm font-medium text-slate-600">
                                        {{ $solicitud->created_at->format('d/m/Y h:i A') }}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm text-slate-600 font-bold">
                                        {{ $solicitud->institucion->siglas ?? ($solicitud->institucion->nombre ?? 'Súper Admin / Interno') }}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-sm text-slate-600 max-w-xs truncate" title="{{ $solicitud->titulo_acto }}">
                                        {{ Str::limit($solicitud->titulo_acto, 50) }}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-center text-sm">
                                        @php
                                            $color = match($solicitud->estado) {
                                                'Pendiente' => 'bg-yellow-100 text-yellow-800',
                                                'Aprobada' => 'bg-emerald-100 text-emerald-800',
                                                'En Digitalización', 'En Revisión Final' => 'bg-blue-100 text-blue-800',
                                                'Publicada' => 'bg-green-100 text-green-800 font-bold',
                                                'Rechazada' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">{{ $solicitud->estado }}</span>
                                    </td>
                                    <td class="py-3 px-4 align-middle text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="bg-slate-600 hover:bg-slate-700 text-white py-1.5 px-3 rounded text-xs shadow-sm transition">Evaluar Documento</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-slate-500">No hay solicitudes registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
