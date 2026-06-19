<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Subir PDF de Gaceta') }} Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <a href="{{ route('gacetas.digitalizador') }}"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold uppercase rounded shadow-sm hover:shadow transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Cancelar y Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Bloque A: Sumario de Escaneo -->
                <div class="w-full lg:w-1/2">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 h-full">
                        <h3 class="text-lg font-bold text-blue-900 mb-4 border-b pb-2">
                            <i class="fa-solid fa-list-ol mr-2"></i> Orden Estricto de Escaneo (Sumario)
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Asegúrese de escanear los documentos físicos exactamente en el orden listado a continuación para mantener la correlatividad de la Gaceta.</p>
                        
                        <div class="overflow-y-auto pr-2" style="max-height: 500px;">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Institución</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Acto / Nro</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Sumario</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($gaceta->solicitudes as $solicitud)
                                        @foreach($solicitud->actos as $acto)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3 text-gray-800 font-bold text-xs">{{ $solicitud->institucion->nombre ?? 'N/A' }}</td>
                                            <td class="px-3 py-3 text-gray-600 text-xs">
                                                {{ $acto->tipo_acto }} <br>
                                                <span class="font-bold text-indigo-600">#{{ $acto->numero_acto }}</span>
                                            </td>
                                            <td class="px-3 py-3 text-gray-600 text-xs" title="{{ $acto->sumario }}">
                                                {{ \Illuminate\Support\Str::limit($acto->sumario, 80) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-4 text-center text-gray-500">No hay actos registrados en este sumario.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Bloque B: Carga del Archivo Final -->
                <div class="w-full lg:w-1/2">
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 h-full flex flex-col justify-between">
                        
                        <div>
                            <h3 class="text-lg font-bold text-blue-900 mb-4 border-b pb-2">
                                <i class="fa-solid fa-file-pdf mr-2"></i> Subir Archivo Maestro
                            </h3>

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

                            @if($gaceta->estado === 'Devuelta a Digitalizador')
                                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                                    <div class="flex items-start">
                                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg mt-0.5"></i>
                                        <div class="ml-3">
                                            <p class="font-bold">Gaceta Rechazada por Jefatura</p>
                                            <p class="text-sm mt-1"><strong>Motivo:</strong> {{ $gaceta->motivo_devolucion }}</p>
                                            <p class="text-xs mt-2">Por favor, corrija los errores indicados, escanee nuevamente y suba el archivo corregido.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm">
                                <div class="flex items-start">
                                    <i class="fa-solid fa-circle-info text-emerald-500 text-lg mt-0.5"></i>
                                    <div class="ml-3">
                                        <p class="text-sm leading-relaxed">
                                            Al subir el <strong>PDF Consolidado</strong>, la Gaceta será enviada a <strong>Jefatura</strong> para su revisión final. No se publicará oficialmente hasta que el Coordinador la apruebe.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('gacetas.enviar_revision', $gaceta->id) }}" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-6">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Archivo PDF Maestro (Gaceta Final) <span class="text-red-500">*</span></label>
                                    <input type="file" name="archivo_pdf_completo" accept=".pdf" required
                                        class="block w-full text-sm text-gray-500 
                                            file:mr-4 file:py-2.5 file:px-4 
                                            file:rounded-md file:border-0 
                                            file:text-sm file:font-bold 
                                            file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 
                                            border border-gray-300 rounded-md p-2 transition-colors focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                    <p class="text-xs text-gray-500 mt-2"><i class="fa-solid fa-shield-halved mr-1"></i> Formato requerido: PDF. Límite: 20MB.</p>
                                </div>

                                <div class="mt-8 flex justify-end gap-4 border-t pt-6">
                                    <button type="button" onclick="document.getElementById('devolucion-box').classList.toggle('hidden')" class="inline-flex justify-center items-center py-3 px-6 border border-gray-300 shadow-sm text-sm font-bold rounded-md text-red-600 bg-white hover:bg-red-50 transition-colors">
                                        <i class="fa-solid fa-rotate-left mr-2"></i> Devolver a Jefatura
                                    </button>
                                    <button type="submit" onclick="return confirm('¿Confirma que el escaneo es correcto y desea enviarlo a revisión final?')"
                                        class="inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-md text-sm font-bold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                        <i class="fa-solid fa-paper-plane mr-2"></i> Subir PDF y Enviar a Jefatura para Revisión
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div id="devolucion-box" class="hidden mt-6 bg-red-50 p-4 rounded-md border border-red-200">
                            <form action="{{ route('gacetas.devolver', $gaceta->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm font-bold text-red-900 mb-2">Motivo de la Devolución *</label>
                                <textarea name="motivo_devolucion" rows="3" required class="w-full rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Ej: Faltan firmas, escaneo ilegible, no corresponde a la gaceta asignada..."></textarea>
                                <div class="mt-3 flex justify-end">
                                    <button type="submit" onclick="return confirm('¿Está seguro de devolver esta Gaceta? El Jefe de Publicaciones deberá reasignarla.')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm transition">
                                        Confirmar Devolución
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>