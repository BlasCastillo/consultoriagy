<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel del Digitalizador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4 text-blue-900 border-b pb-2">Gacetas Asignadas para Escaneo</h3>
                    
                    @if(session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if($gacetas->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($gacetas as $gaceta)
                                <div class="border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                                    <div class="bg-blue-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                        <span class="font-bold text-blue-900">Gaceta Nro. {{ str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) }}</span>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-yellow-400">En Escaneo</span>
                                    </div>
                                    <div class="p-4 bg-white text-sm">
                                        <p class="mb-1"><span class="font-semibold text-gray-600">Año:</span> {{ $gaceta->anio }}</p>
                                        <p class="mb-1"><span class="font-semibold text-gray-600">Tipo:</span> {{ $gaceta->tipo }}</p>
                                        <p class="mb-4"><span class="font-semibold text-gray-600">Asignada el:</span> {{ $gaceta->updated_at->format('d/m/Y') }}</p>
                                        
                                        <a href="{{ route('gacetas.upload_pdf', $gaceta->id) }}" class="block w-full text-center bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-800 transition">
                                            <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Subir PDF Escaneado
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No tienes gacetas asignadas pendientes por escanear.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
