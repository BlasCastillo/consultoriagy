<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                <i class="fa-solid fa-magnifying-glass-chart mr-2 text-blue-700"></i>
                Revisión Final: Gaceta Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <a href="{{ route('gacetas.index') }}"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold uppercase rounded shadow-sm hover:shadow transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Volver a Mesa de Control
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col xl:flex-row gap-6 items-start">

                {{-- ══════════════════════════════════════════════════════
                COLUMNA IZQUIERDA: VISTA PREVIA LEGAL COMPLETA
                (Idéntica a show.blade.php — portada + sumario + PDF)
                ══════════════════════════════════════════════════════ --}}
                <div class="w-full xl:w-2/3">
                    <div class="mb-3 flex items-center gap-2">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <i class="fa-solid fa-eye mr-1.5"></i> Vista Previa del Producto Final
                        </span>
                        <span class="text-xs text-gray-500">Así se verá publicada la Gaceta.</span>
                    </div>

                    {{-- ── HOJA PORTADA (HTML) ── --}}
                    <div class="bg-gray-200 py-8 px-4 rounded-lg">
                        <div class="space-y-8 w-full flex flex-col items-center">

                            @include('gacetas.partials.portada')

                            {{-- ── PDF ESCANEADO (páginas siguientes a la portada) ── --}}
                            @if($gaceta->archivo_pdf_completo)
                                <div id="pdf-pages-container" class="space-y-8 w-full flex flex-col items-center">
                                    <div id="pdf-loading" class="text-center text-gray-500 font-bold py-10">
                                        <i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i><br>
                                        Cargando páginas del documento escaneado...
                                    </div>
                                </div>
                            @else
                                <div class="bg-white shadow-2xl mx-auto relative text-black"
                                    style="width: 100%; max-width: 850px; padding: 3rem 4rem;">
                                    <div
                                        class="w-full py-16 bg-amber-50 border-2 border-dashed border-amber-300 text-center text-amber-700">
                                        <i class="fa-solid fa-triangle-exclamation text-4xl mb-3"></i>
                                        <p class="font-bold">El digitalizador aún no ha subido el documento escaneado.</p>
                                        <p class="text-sm mt-1 text-amber-600">Las páginas del acto aparecerán aquí cuando
                                            el PDF sea subido.</p>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>{{-- /bg-gray-200 --}}
                </div>

                {{-- ══════════════════════════════════════════════════════
                COLUMNA DERECHA: DICTAMEN DE JEFATURA (sticky)
                ══════════════════════════════════════════════════════ --}}
                <div class="w-full xl:w-1/3">
                    <div class="sticky top-6">
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">

                            {{-- Header del panel --}}
                            <div class="bg-blue-900 text-white px-6 py-4">
                                <h3 class="text-lg font-bold">
                                    <i class="fa-solid fa-gavel mr-2"></i> Dictamen de Revisión
                                </h3>
                                <p class="text-blue-200 text-xs mt-0.5">Gaceta Nro. {{ $gaceta->numero }} /
                                    {{ $gaceta->anio }}</p>
                            </div>

                            <div class="p-6 space-y-6">

                                {{-- Aviso --}}
                                <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 rounded text-sm">
                                    <p class="font-bold mb-1"><i class="fa-solid fa-circle-info mr-1"></i> Instrucciones
                                    </p>
                                    <p>Revise la portada, el sumario y las páginas escaneadas a la izquierda. Verifique
                                        que el contenido y el orden de los actos sean correctos antes de aprobar.</p>
                                </div>

                                {{-- Resumen de apartados --}}
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        Instituciones en esta edición</p>
                                    <ul class="space-y-1.5">
                                        @php
                                            $instResumen = $gaceta->solicitudes->groupBy(fn($s) => $s->institucion?->nombre_sumario ?? 'N/A');
                                        @endphp
                                        @forelse($instResumen as $nombre => $sols)
                                            <li
                                                class="flex items-center justify-between text-sm bg-gray-50 rounded px-3 py-1.5 border border-gray-100">
                                                <span class="font-medium text-gray-700 truncate pr-2">{{ $nombre }}</span>
                                                <span
                                                    class="flex-shrink-0 text-xs font-bold text-indigo-700 bg-indigo-100 rounded-full px-2 py-0.5">
                                                    {{ $sols->count() }} apt.
                                                </span>
                                            </li>
                                        @empty
                                            <li class="text-sm text-gray-400 italic">Sin apartados.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <hr>

                                {{-- ── APROBAR Y PUBLICAR ── --}}
                                <div>
                                    <form method="POST" action="{{ route('gacetas.publicar_oficial', $gaceta->id) }}">
                                        @csrf
                                        <button type="submit"
                                            onclick="event.preventDefault(); const form = this.closest('form'); Swal.fire({ title: '¿Aprobar y publicar oficialmente?', text: 'Esta acción es irreversible y notificará a todas las instituciones.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, aprobar y publicar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                            class="w-full inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-md text-sm font-bold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                            <i class="fa-solid fa-check-double mr-2"></i> Aprobar y Publicar
                                            Oficialmente
                                        </button>
                                    </form>
                                    <p class="text-xs text-gray-500 mt-2 text-center">La gaceta pasará a estado
                                        <strong>Publicada</strong> y será visible para todas las partes.</p>
                                </div>

                                <hr>

                                {{-- ── RECHAZAR / DEVOLVER ── --}}
                                <div>
                                    <h4 class="font-bold text-red-700 mb-3 text-sm">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Rechazar y Devolver
                                    </h4>
                                    <form method="POST" action="{{ route('gacetas.rechazar_escaneo', $gaceta->id) }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                                Motivo de Rechazo <span class="text-red-500">*</span>
                                            </label>
                                            <textarea name="motivo_rechazo_escaneo" rows="4" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                placeholder="Indique claramente los errores (ej. página 3 borrosa, falta firma final, orden incorrecto...)"></textarea>
                                        </div>
                                        <button type="submit"
                                            onclick="event.preventDefault(); const form = this.closest('form'); if (!form.reportValidity()) return; Swal.fire({ title: '¿Devolver al digitalizador?', text: 'El archivo PDF actual será descartado y el motivo se enviará al digitalizador.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, devolver', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                            class="w-full inline-flex justify-center items-center py-2 px-4 border border-red-300 shadow-sm text-sm font-bold rounded-md text-red-600 bg-white hover:bg-red-50 transition-colors">
                                            <i class="fa-solid fa-rotate-left mr-2"></i> Devolver a Digitalizador
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
    PDF.JS: misma vista previa que upload_pdf (páginas completas).
    ══════════════════════════════════════════════════════════════════ --}}
    @if($gaceta->archivo_pdf_completo)
        @include('gacetas.partials.preview-pdf', ['pdfUrl' => asset('storage/' . $gaceta->archivo_pdf_completo)])
    @endif

</x-app-layout>