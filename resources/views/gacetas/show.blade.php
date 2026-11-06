<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Vista Previa Legal: Gaceta {{ $gaceta->numero }} / {{ $gaceta->anio }}
            </h2>
            <div class="flex items-center gap-2">
                @if($gaceta->pdf_final || $gaceta->archivo_pdf_completo || $gaceta->ruta_archivo)
                    <x-action-button :href="route('gacetas.descargar', $gaceta->id)"
                        action="Descargar PDF oficial"
                        icon="fa-solid fa-download"
                        tone="export"
                        align="end" />
                @endif
                <x-action-button :href="route('gacetas.index')"
                    action="Volver al listado"
                    icon="fa-solid fa-arrow-left"
                    tone="back"
                    align="end" />
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

    <div class="bg-slate-200 py-12">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-8 w-full flex flex-col items-center">

                @include('gacetas.partials.portada')

                @if($pdfPreviewUrl)
                    <div id="pdf-pages-container" class="space-y-8 w-full flex flex-col items-center">
                        <div id="pdf-loading" class="bg-slate-50 py-10 text-center font-bold text-slate-500">
                            <i class="fa-solid fa-spinner fa-spin mb-3 text-3xl"></i><br>
                            Cargando las páginas de la Gaceta...
                        </div>
                    </div>
                @else
                    <div class="relative mx-auto bg-white text-black shadow-2xl"
                        style="width: 100%; max-width: 850px; padding: 3rem 4rem;">
                        <div class="w-full border-2 border-dashed border-slate-300 bg-slate-50 py-16 text-center text-slate-500">
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
