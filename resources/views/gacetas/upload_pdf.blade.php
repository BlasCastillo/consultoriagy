<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Subir PDF de Gaceta') }} Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <a href="{{ route('gacetas.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Cancelar y Volver</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                
                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6 p-4 bg-blue-50 text-blue-800 rounded-md border border-blue-200">
                    <p class="text-sm">Al subir el documento físico escaneado (PDF), la gaceta cambiará automáticamente de su estado actual (<strong>{{ $gaceta->estado }}</strong>) a <strong>Publicada</strong>, haciéndola visible y descargable para el resto del sistema.</p>
                </div>

                <form method="POST" action="{{ route('gacetas.save_pdf', $gaceta->id) }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Archivo PDF Firmado *</label>
                        <input type="file" name="ruta_archivo" accept=".pdf" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md p-2">
                        <p class="text-xs text-gray-500 mt-2">Formatos permitidos: PDF. Tamaño máximo: 10MB.</p>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Subir y Publicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
