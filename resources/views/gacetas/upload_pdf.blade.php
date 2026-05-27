<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Subir PDF de Gaceta') }} Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <a href="{{ route('gacetas.index') }}"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold uppercase rounded shadow-sm hover:shadow transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Cancelar y Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                        <p class="font-bold mb-2">Por favor corrija los siguientes errores:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 rounded shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-info text-blue-500 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm leading-relaxed">Al subir el documento físico escaneado (PDF), la gaceta
                                cambiará automáticamente de su estado actual (<strong>{{ $gaceta->estado }}</strong>) a
                                <strong>Publicada</strong>, haciéndola visible y descargable para el resto del sistema.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('gacetas.save_pdf', $gaceta->id) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-blue-900 mb-2">Archivo PDF Firmado *</label>
                        <input type="file" name="ruta_archivo" accept=".pdf" required
                            class="block w-full text-sm text-gray-500 
                                   file:mr-4 file:py-2.5 file:px-4 
                                   file:rounded-md file:border-0 
                                   file:text-sm file:font-bold 
                                   file:bg-blue-50 file:text-blue-900 hover:file:bg-blue-100 
                                   border border-gray-300 rounded-md p-2 transition-colors focus:outline-none focus:border-blue-900 focus:ring-1 focus:ring-blue-900">
                        <p class="text-xs text-gray-500 mt-2"><i class="fa-solid fa-file-pdf mr-1"></i> Formatos
                            permitidos: PDF. Tamaño máximo: 10MB.</p>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit"
                            class="inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-md text-sm font-bold rounded-md text-white bg-blue-900 hover:bg-blue-800 transition-colors">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Subir y Publicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>