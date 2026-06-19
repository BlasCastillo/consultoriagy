<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalles y Evaluación de Solicitud') }}
            </h2>
            <span class="px-3 py-1 rounded-full text-sm font-bold 
                {{ $solicitud->estado == 'Pendiente' ? 'bg-yellow-200 text-yellow-800' : ($solicitud->estado == 'Aprobada' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') }}">
                {{ $solicitud->estado }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Información del Oficio Remitente</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div><span class="block font-bold text-gray-600">Institución:</span> {{ $solicitud->institucion->nombre ?? 'N/A' }}</div>
                        <div><span class="block font-bold text-gray-600">Asunto / Número:</span> {{ $solicitud->titulo_acto }}</div>
                        <div><span class="block font-bold text-gray-600">Documento PDF:</span> 
                            <a href="{{ Storage::url($solicitud->archivo_pdf_path) }}" target="_blank" class="text-blue-600 hover:underline">Ver Respaldo Digital</a>
                        </div>
                        <div><span class="block font-bold text-gray-600">Tiempo Transcurrido:</span> {{ $solicitud->tiempo_transcurrido }}</div>
                    </div>
                </div>
            </div>

@if($solicitud->estado == 'Rechazada')
    <div class="bg-red-50 border-l-4 border-red-600 p-4 mb-6 rounded-r-md shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-red-800">Apartado Devuelto a la Institución</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p><strong>Motivo del Rechazo:</strong> {{ $solicitud->motivo_rechazo ?? 'No se especificó un motivo en el sistema.' }}</p>
                </div>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <a href="{{ route('solicitudes.edit', $solicitud->id) }}" class="px-6 py-2 bg-blue-600 text-white font-bold rounded shadow hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Corregir Documento y Re-enviar a Jefatura
            </a>
        </div>
    </div>
@elseif($solicitud->estado == 'Aprobada')
    <div class="bg-green-50 border-l-4 border-green-600 p-4 mb-6 rounded-r-md shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-green-800">Apartado Aprobado y Certificado</h3>
                <div class="mt-2 text-sm text-green-700">
                    <p>Este documento ha superado la revisión de requisitos y se encuentra en la bandeja de espera para ser ensamblado en una Gaceta Oficial.</p>
                </div>
            </div>
        </div>
    </div>
@endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Actos Administrativos Desglosados</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumario</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($solicitud->actos as $acto)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $acto->tipo_acto }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ $acto->numero }}</td>
                                <td class="px-6 py-4 text-sm">{{ $acto->sumario }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No hay actos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @can('evaluar solicitud')
                @if($solicitud->estado == 'Pendiente')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-blue-200">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-blue-900 mb-4 border-b pb-2">Verificación de Requisitos (Evaluación)</h3>
                        
                        <form id="form-aprobar-{{ $solicitud->id }}" action="{{ route('solicitudes.aprobar', $solicitud->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mb-6 bg-blue-50 p-4 rounded-md border border-blue-100 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Año Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="ano_politico" required placeholder="Ej: 168°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mes Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="mes_politico" required placeholder="Ej: 120°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha Recepción Física <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_recepcion_fisica" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                </div>
                            </div>

                            <div class="space-y-4 mb-6">
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="checklist[firma_fisica]" value="1" class="rounded border-gray-300 text-blue-900 shadow-sm">
                                    <span class="text-gray-700">El documento físico tiene la firma autorizada original.</span>
                                </label>
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="checklist[sello_humedo]" value="1" class="rounded border-gray-300 text-blue-900 shadow-sm">
                                    <span class="text-gray-700">El documento físico tiene el sello húmedo de la institución.</span>
                                </label>
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="checklist[formato_correcto]" value="1" class="rounded border-gray-300 text-blue-900 shadow-sm">
                                    <span class="text-gray-700">El formato y la numeración interna del documento son correctos.</span>
                                </label>
                            </div>

                            <div class="mt-6 flex gap-4 border-t pt-4">
                                <button type="button" onclick="confirmarAprobacion({{ $solicitud->id }})" class="px-6 py-2 bg-green-600 text-white font-bold rounded shadow hover:bg-green-700 transition">Aprobar Apartado para Ensamblaje</button>
                        </form>

                                <button type="button" onclick="toggleRechazo()" class="px-6 py-2 bg-red-600 text-white font-bold rounded shadow hover:bg-red-700 transition">Rechazar Documento</button>
                            </div>

                        <div id="zona-rechazo" class="mt-6 border-t border-red-200 pt-6 hidden" style="display: none;">
                            <h4 class="text-md font-bold text-red-700 mb-3">Motivo de Devolución</h4>
                            <form id="form-rechazar-{{ $solicitud->id }}" action="{{ route('solicitudes.rechazar', $solicitud->id) }}" method="POST" class="bg-red-50 p-4 rounded-md border border-red-200">
                                @csrf
                                @method('PATCH')
                                <div class="mb-4">
                                    <textarea name="motivo_rechazo" id="motivo_rechazo" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Especifique el motivo para devolver a la institución..."></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="button" onclick="confirmarRechazo({{ $solicitud->id }})" class="px-6 py-2 bg-red-800 text-white font-bold rounded shadow hover:bg-red-900 transition">Confirmar y Enviar a Institución</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            @endcan
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleRechazo() {
            const zona = document.getElementById('zona-rechazo');
            zona.style.display = zona.style.display === 'none' ? 'block' : 'none';
        }

        function confirmarAprobacion(id) {
            Swal.fire({
                title: '¿Aprobar y certificar?',
                text: "El documento pasará a la bandeja de ensamblaje.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('form-aprobar-' + id).submit(); }
            });
        }

        function confirmarRechazo(id) {
            if(document.getElementById('motivo_rechazo').value.trim() === '') {
                Swal.fire('Atención', 'Debe especificar el motivo del rechazo.', 'warning');
                return;
            }
            Swal.fire({
                title: '¿Rechazar definitivamente?',
                text: "Se notificará a la institución sobre esta decisión.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('form-rechazar-' + id).submit(); }
            });
        }
    </script>
</x-app-layout>
