<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Panel del Digitalizador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body text-slate-900">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Gacetas Asignadas para Escaneo</h3>
                    
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if($gacetas->isEmpty())
                        <div class="py-8 text-center text-slate-500">
                            <p>No tienes gacetas asignadas pendientes por escanear.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="table-corporate">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Tipo</th>
                                        <th>Fecha Asignación</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gacetas as $gaceta)
                                        <tr>
                                            <td class="whitespace-nowrap font-bold text-slate-900">{{ $gaceta->numero }}</td>
                                            <td class="whitespace-nowrap">{{ $gaceta->tipo }}</td>
                                            <td class="whitespace-nowrap">{{ $gaceta->created_at->format('d/m/Y h:i A') }}</td>
                                            <td>
                                                <div class="flex justify-center">
                                                    <x-action-button :href="route('gacetas.upload_pdf', $gaceta->id)"
                                                        action="Iniciar escaneo"
                                                        icon="fa-solid fa-file-pdf"
                                                        tone="brand" />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
