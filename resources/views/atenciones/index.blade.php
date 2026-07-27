<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Atención al Ciudadano') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: 'success', title: "{!! addslashes(session('success')) !!}" });
                    });
                </script>
            @endif

            <div class="mb-4 flex justify-end">
                <a href="{{ route('atenciones.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Registrar Nueva Atención
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cédula</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre y Apellido</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jefatura Destino</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($atenciones as $atencion)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{{ $atencion->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-900">{{ $atencion->cedula }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-900">{{ $atencion->nombre }} {{ $atencion->apellido }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-900">{{ $atencion->jefaturaDestino->nombre ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-900 capitalize">{{ $atencion->motivo }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">No hay atenciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        {{ $atenciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
