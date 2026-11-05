<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Mesa de Ensamblaje: Subsanar Gaceta Oficial #') }}{{ $gaceta->numero }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form id="ensamblaje-form" action="{{ route('gacetas.subsanar', $gaceta->id) }}" method="POST">
                @csrf
                <div class="flex flex-col lg:flex-row gap-6">
                    
                    {{-- Columna Izquierda: Metadata --}}
                    <div class="w-full lg:w-1/3">
                        <div class="card p-6">
                            <h3 class="mb-4 border-b border-slate-200 pb-2 text-lg font-bold text-slate-800">Metadatos de la Gaceta</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Número de Gaceta <span class="text-red-500">*</span></label>
                                    <input type="text" name="numero" required value="{{ old('numero', $gaceta->numero) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Tipo de Edición <span class="text-red-500">*</span></label>
                                    <select name="tipo" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="Ordinaria" {{ old('tipo', $gaceta->tipo) == 'Ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                                        <option value="Extraordinaria" {{ old('tipo', $gaceta->tipo) == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                                    </select>
                                </div>

                                <div class="mb-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Año Político <span class="text-red-500">*</span></label>
                                        <input type="text" name="ano_politico" required value="{{ old('ano_politico', $gaceta->ano_politico) }}" placeholder="Ej: 168°" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Mes Político <span class="text-red-500">*</span></label>
                                        <input type="text" name="mes_politico" required value="{{ old('mes_politico', $gaceta->mes_politico) }}" placeholder="Ej: 120°" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Fecha de Publicación <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_publicacion" required value="{{ old('fecha_publicacion', \Carbon\Carbon::parse($gaceta->fecha_publicacion)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Gobernador <span class="text-red-500">*</span></label>
                                    <select name="gobernador_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($gobernadores as $gob)
                                            <option value="{{ $gob->id }}" {{ old('gobernador_id', $gaceta->gobernador_id) == $gob->id ? 'selected' : '' }}>{{ $gob->nombres ?? 'Sin Nombre' }} {{ $gob->apellidos ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <input type="hidden" name="digitalizador_id_backup" value="{{ $gaceta->digitalizador_id }}">
                                    <label class="block text-sm font-medium text-slate-700">Digitalizador Responsable <span class="text-red-500">*</span></label>
                                    <select name="digitalizador_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($digitalizadores as $digitalizador)
                                            <option value="{{ $digitalizador->id }}" {{ old('digitalizador_id', $gaceta->digitalizador_id) == $digitalizador->id ? 'selected' : '' }}>{{ $digitalizador->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pt-6">
                                    <button type="button" onclick="confirmarEnsamblaje()" class="btn btn-primary w-full">
                                        Guardar Correcciones y Enviar a Digitalización
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Columna Derecha: Selección de Apartados --}}
                    <div class="w-full lg:w-2/3">
                        <div class="card h-full p-6">
                            <div class="mb-4 flex flex-col justify-between gap-2 border-b border-slate-200 pb-2 sm:flex-row sm:items-center">
                                <h3 class="text-lg font-bold text-slate-800">Apartados Aprobados (Disponibles y Actuales)</h3>
                                <span id="contador-apartados" class="badge badge-info whitespace-nowrap">0 Apartados seleccionados</span>
                            </div>

                            @if($apartadosAprobados->isEmpty())
                                <div class="py-12 text-center text-slate-500">
                                    No hay apartados aprobados esperando por ensamblaje en este momento.
                                </div>
                            @else
                                <div class="space-y-3 overflow-y-auto pr-2" style="max-height: 600px;">
                                    @foreach($apartadosAprobados as $apartado)
                                        <label class="block cursor-pointer">
                                            <div class="flex items-start gap-4 rounded-lg border border-slate-200 p-4 transition-colors hover:bg-slate-50">
                                                <div class="pt-1">
                                                    <input type="checkbox" name="apartados[]" value="{{ $apartado->id }}" class="apartado-checkbox h-5 w-5 rounded border-slate-300 text-blue-900 focus:ring-blue-800" {{ in_array($apartado->id, old('apartados', $apartadosAsignadosIds)) ? 'checked' : '' }}>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-slate-800">{{ $apartado->institucion->nombre ?? 'N/A' }}</h4>
                                                    <p class="mt-1 line-clamp-2 text-sm text-slate-600" title="{{ $apartado->titulo_acto }}">{{ $apartado->titulo_acto }}</p>
                                                    <div class="mt-3 flex justify-end">
                                                        <x-action-button :href="route('solicitudes.show', $apartado->id)"
                                                            action="Ver sumario de actos"
                                                            icon="fa-solid fa-eye"
                                                            tone="neutral"
                                                            target="_blank"
                                                            onclick="event.stopPropagation();" />
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
                    contador.classList.replace('badge-info', 'badge-success');
                } else {
                    contador.classList.replace('badge-success', 'badge-info');
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
                title: '¿Confirmar Correcciones?',
                text: `Se actualizarán los datos y los ${seleccionados} apartados en esta Gaceta y se enviará al proceso de digitalización.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669', // emerald-600
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, Guardar y Enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
