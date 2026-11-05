<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Vista Previa Legal: Gaceta {{ $gaceta->numero }} / {{ $gaceta->anio }}
            </h2>
            <div class="flex space-x-3">
                @if($gaceta->pdf_final || $gaceta->archivo_pdf_completo || $gaceta->ruta_archivo)
                    <a href="{{ route('gacetas.descargar', $gaceta->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white font-bold text-xs uppercase rounded transition-colors shadow-md">
                        <i class="fa-solid fa-download mr-2"></i> Descargar PDF Oficial
                    </a>
                @endif
                <a href="{{ route('gacetas.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded transition-colors shadow-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Volver al listado
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $pdfPreviewUrl = null;

        if ($gaceta->archivo_pdf_completo) {
            $pdfPreviewUrl = asset('storage/' . $gaceta->archivo_pdf_completo);
        } elseif ($gaceta->ruta_archivo && file_exists(public_path('gacetas_pdf/' . $gaceta->ruta_archivo))) {
            $pdfPreviewUrl = asset('gacetas_pdf/' . $gaceta->ruta_archivo);
        }
    @endphp

    <div class="py-12 bg-gray-200">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-5 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="space-y-8 w-full flex flex-col items-center">

                @include('gacetas.partials.portada')

                @if($pdfPreviewUrl)
                    <div id="pdf-pages-container" class="space-y-8 w-full flex flex-col items-center">
                        <div id="pdf-loading" class="text-center text-gray-500 font-bold py-10">
                            <i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i><br>
                            Cargando las páginas de la Gaceta...
                        </div>
                    </div>
                @else
                    <div class="bg-white shadow-2xl mx-auto relative text-black"
                        style="width: 100%; max-width: 850px; padding: 3rem 4rem;">
                        <div class="w-full py-16 bg-gray-50 border-2 border-dashed border-gray-300 text-center text-gray-500">
                            <i class="fa-solid fa-file-pdf text-4xl mb-3 text-gray-300"></i>
                            <p>Las páginas del documento aparecerán aquí cuando el PDF sea subido.</p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @if($pdfPreviewUrl)
        @include('gacetas.partials.preview-pdf', ['pdfUrl' => $pdfPreviewUrl])
    @endif

    {{-- Al publicar, se descarga automáticamente el PDF final (sumario + actos). --}}
    @if(session('descargar_pdf') && ($gaceta->pdf_final || $gaceta->archivo_pdf_completo || $gaceta->ruta_archivo))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = "{{ route('gacetas.descargar', $gaceta->id) }}";
                document.body.appendChild(iframe);
            });
        </script>
    @endif
</x-app-layout>
