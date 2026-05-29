<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aprobar Gaceta para Publicación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4 text-blue-900 border-b pb-2">Revisión Final</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-md text-sm border border-gray-200">
                            <p class="text-xl font-bold text-blue-900 mb-2">Gaceta Nro. {{ str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) }}</p>
                            <p><strong>Tipo:</strong> {{ $gaceta->tipo }}</p>
                            <p><strong>Gobernador:</strong> {{ $gaceta->gobernador->nombres }} {{ $gaceta->gobernador->apellidos }}</p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-md text-sm border border-gray-200 flex flex-col justify-center items-center">
                            <p class="mb-2 font-bold text-gray-700">Archivo Escaneado Subido:</p>
                            <a href="{{ asset('storage/SGCJ/' . $gaceta->ruta_archivo) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition w-full text-center">
                                <i class="fa-solid fa-file-pdf mr-2"></i> Ver PDF Escaneado
                            </a>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-triangle-exclamation text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Revise detalladamente el PDF escaneado. Si aprueba, el sistema unirá automáticamente la portada/sumario con este archivo físico y publicará la gaceta oficial.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('gacetas.publicar', $gaceta->id) }}" method="POST">
                        @csrf
                        <div class="flex justify-between items-center mt-8 pt-4 border-t border-gray-200">
                            <a href="{{ route('gacetas.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">Volver</a>
                            
                            <div class="flex gap-2">
                                <button type="submit" name="accion" value="rechazar" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition" onclick="return confirm('¿Está seguro de rechazar el escaneo y devolverlo al digitalizador?');">
                                    <i class="fa-solid fa-xmark mr-1"></i> Rechazar y Devolver
                                </button>
                                <button type="submit" name="accion" value="aprobar" class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-800 transition shadow-lg font-bold">
                                    <i class="fa-solid fa-check-double mr-1"></i> Aprobar y Publicar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
