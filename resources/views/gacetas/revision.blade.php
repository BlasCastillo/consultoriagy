<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Revisión Final de Gaceta') }} Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <a href="{{ route('gacetas.index') }}"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold uppercase rounded shadow-sm hover:shadow transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Volver a Mesa de Control
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Bloque A: Previsualización del PDF -->
                <div class="w-full lg:w-2/3">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 h-full">
                        <h3 class="text-lg font-bold text-blue-900 mb-4 border-b pb-2">
                            <i class="fa-solid fa-file-pdf mr-2"></i> Archivo Escaneado por Digitalizador
                        </h3>
                        
                        <div class="w-full bg-gray-100 rounded flex items-center justify-center border" style="height: 600px;">
                            @if($gaceta->archivo_pdf_completo)
                                <iframe src="{{ asset('storage/' . $gaceta->archivo_pdf_completo) }}" class="w-full h-full rounded"></iframe>
                            @else
                                <p class="text-gray-500">No hay archivo PDF disponible para revisión.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Bloque B: Acciones de Jefatura -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 h-full flex flex-col justify-start">
                        
                        <h3 class="text-lg font-bold text-blue-900 mb-4 border-b pb-2">
                            <i class="fa-solid fa-gavel mr-2"></i> Dictamen de Revisión
                        </h3>

                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 rounded shadow-sm">
                            <p class="text-sm">Por favor, revise minuciosamente el documento escaneado. Verifique la calidad de imagen, la legibilidad y que corresponda al orden correcto de los actos.</p>
                        </div>

                        <!-- Acción Aprobar -->
                        <div class="mb-8">
                            <form method="POST" action="{{ route('gacetas.publicar_oficial', $gaceta->id) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('¿Está seguro de que desea APROBAR y PUBLICAR oficialmente esta Gaceta? Esta acción es irreversible, detendrá los relojes de auditoría y notificará a todas las instituciones.')"
                                    class="w-full inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-md text-sm font-bold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                    <i class="fa-solid fa-check-double mr-2"></i> Aprobar y Publicar Oficialmente
                                </button>
                            </form>
                            <p class="text-xs text-gray-500 mt-2 text-center">La gaceta pasará a estado 'Publicada' y será visible para todas las partes.</p>
                        </div>

                        <hr class="mb-8">

                        <!-- Acción Rechazar -->
                        <div>
                            <h4 class="font-bold text-red-700 mb-3"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Rechazar Escaneo</h4>
                            <form method="POST" action="{{ route('gacetas.rechazar_escaneo', $gaceta->id) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Motivo de Rechazo (Obligatorio) <span class="text-red-500">*</span></label>
                                    <textarea name="motivo_rechazo_escaneo" rows="4" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Indique claramente los errores encontrados (ej. página 3 borrosa, falta firma final, orden incorrecto...)"></textarea>
                                </div>
                                <button type="submit" onclick="return confirm('¿Devolver al digitalizador? El archivo PDF actual será descartado para obligar a una nueva subida.')"
                                    class="w-full inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-bold rounded-md text-red-600 bg-white hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-rotate-left mr-2"></i> Devolver a Digitalizador
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
