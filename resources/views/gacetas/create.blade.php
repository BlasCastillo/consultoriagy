<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mesa de Ensamblaje: Nueva Gaceta Oficial') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form id="ensamblaje-form" action="{{ route('gacetas.store') }}" method="POST">
                @csrf
                <div class="flex flex-col lg:flex-row gap-6">
                    
                    {{-- Columna Izquierda: Metadata --}}
                    <div class="w-full lg:w-1/3">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Metadatos de la Gaceta</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Número de Gaceta <span class="text-red-500">*</span></label>
                                    <input type="text" name="numero" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo de Edición <span class="text-red-500">*</span></label>
                                    <select name="tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="Ordinaria">Ordinaria</option>
                                        <option value="Extraordinaria">Extraordinaria</option>
                                    </select>
                                </div>

                                <div class="mb-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Año Político <span class="text-red-500">*</span></label>
                                        <input type="text" name="ano_politico" required placeholder="Ej: 168°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Mes Político <span class="text-red-500">*</span></label>
                                        <input type="text" name="mes_politico" required placeholder="Ej: 120°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Publicación <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_publicacion" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Gobernador <span class="text-red-500">*</span></label>
                                    <select name="gobernador_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($gobernadores as $gob)
                                            <option value="{{ $gob->id }}">{{ $gob->nombres ?? 'Sin Nombre' }} {{ $gob->apellidos ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Digitalizador Responsable <span class="text-red-500">*</span></label>
                                    <select name="digitalizador_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($digitalizadores as $digitalizador)
                                            <option value="{{ $digitalizador->id }}">{{ $digitalizador->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pt-6">
                                    <button type="button" onclick="confirmarEnsamblaje()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-md shadow-sm transition">
                                        Ensamblar Gaceta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Columna Derecha: Selección de Apartados --}}
                    <div class="w-full lg:w-2/3">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 h-full">
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-4 border-b pb-2 gap-2">
                                <h3 class="text-lg font-bold text-gray-900">Apartados Aprobados</h3>
                                <span id="contador-apartados" class="bg-indigo-100 text-indigo-800 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">0 Apartados seleccionados</span>
                            </div>

                            @if($apartadosAprobados->isEmpty())
                                <div class="text-center py-12 text-gray-500">
                                    No hay apartados aprobados esperando por ensamblaje en este momento.
                                </div>
                            @else
                                <div class="overflow-y-auto pr-2 space-y-3" style="max-height: 600px;">
                                    @foreach($apartadosAprobados as $apartado)
                                        <label class="block cursor-pointer">
                                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-indigo-50 transition-colors flex items-start gap-4">
                                                <div class="pt-1">
                                                    <input type="checkbox" name="apartados[]" value="{{ $apartado->id }}" class="apartado-checkbox h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-800">{{ $apartado->institucion->nombre ?? 'N/A' }}</h4>
                                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2" title="{{ $apartado->titulo_acto }}">{{ $apartado->titulo_acto }}</p>
                                                    <div class="mt-3 flex justify-end">
                                                        <a href="{{ route('solicitudes.show', $apartado->id) }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1" onclick="event.stopPropagation();">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                            Ver Sumario de Actos
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.apartado-checkbox');
            const contador = document.getElementById('contador-apartados');

            function actualizarContador() {
                const seleccionados = document.querySelectorAll('.apartado-checkbox:checked').length;
                contador.textContent = seleccionados === 1 
                    ? '1 Apartado seleccionado para esta edición'
                    : seleccionados + ' Apartados seleccionados para esta edición';
                    
                if(seleccionados > 0) {
                    contador.classList.replace('bg-indigo-100', 'bg-emerald-100');
                    contador.classList.replace('text-indigo-800', 'text-emerald-800');
                } else {
                    contador.classList.replace('bg-emerald-100', 'bg-indigo-100');
                    contador.classList.replace('text-emerald-800', 'text-indigo-800');
                }
            }

            checkboxes.forEach(chk => {
                chk.addEventListener('change', actualizarContador);
            });
            
            actualizarContador();
        });

        function confirmarEnsamblaje() {
            const seleccionados = document.querySelectorAll('.apartado-checkbox:checked').length;
            
            if(seleccionados === 0) {
                Swal.fire('Atención', 'Debe seleccionar al menos un apartado para ensamblar la gaceta.', 'warning');
                return;
            }

            const form = document.getElementById('ensamblaje-form');
            if(!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            Swal.fire({
                title: '¿Confirmar Ensamblaje?',
                text: `Se agruparán ${seleccionados} apartados en esta Gaceta y se enviará al proceso de digitalización.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669', // emerald-600
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, Ensamblar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>