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

                    {{-- Columna Derecha: Selección y Orden de Apartados --}}
                    <div class="w-full lg:w-2/3">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 h-full">
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2 border-b pb-2 gap-2">
                                <h3 class="text-lg font-bold text-gray-900">Apartados Aprobados</h3>
                                <span id="contador-apartados" class="bg-indigo-100 text-indigo-800 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">0 Apartados seleccionados</span>
                            </div>
                            <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                                <svg class="w-4 h-4 inline text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                Marque los apartados a incluir y <strong class="text-indigo-600 mx-1">arrástrelos</strong> para definir el orden en que aparecerán en la Gaceta.
                            </p>

                            @if($apartadosAprobados->isEmpty())
                                <div class="text-center py-12 text-gray-500">
                                    No hay apartados aprobados esperando por ensamblaje en este momento.
                                </div>
                            @else
                                {{-- Lista reordenable --}}
                                <ul id="lista-apartados" class="overflow-y-auto pr-2 space-y-2" style="max-height: 600px;">
                                    @foreach($apartadosAprobados as $apartado)
                                        <li class="apartado-item" data-id="{{ $apartado->id }}">
                                            <div class="border border-gray-200 rounded-lg p-3 bg-white hover:bg-indigo-50 transition-colors flex items-start gap-3 select-none">
                                                {{-- Handle de arrastre --}}
                                                <div class="drag-handle pt-1 cursor-grab text-gray-300 hover:text-indigo-400 flex-shrink-0" title="Arrastrar para reordenar">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 8a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zm-6 6a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4z"/></svg>
                                                </div>
                                                {{-- Número de posición --}}
                                                <div class="posicion-numero flex-shrink-0 w-7 h-7 rounded-full bg-gray-100 text-gray-500 text-xs font-bold flex items-center justify-center mt-0.5">
                                                    {{ $loop->iteration }}
                                                </div>
                                                {{-- Checkbox --}}
                                                <div class="pt-1 flex-shrink-0">
                                                    <input type="checkbox"
                                                           class="apartado-checkbox h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                           data-id="{{ $apartado->id }}">
                                                </div>
                                                {{-- Contenido --}}
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-bold text-gray-800 text-sm">{{ $apartado->institucion->nombre ?? 'N/A' }}</h4>
                                                    <p class="text-xs text-gray-600 mt-0.5 line-clamp-2" title="{{ $apartado->titulo_acto }}">{{ $apartado->titulo_acto }}</p>
                                                    <div class="mt-2 flex justify-end">
                                                        <a href="{{ route('solicitudes.show', $apartado->id) }}" target="_blank"
                                                           class="text-xs text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1"
                                                           onclick="event.stopPropagation();">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                            Ver Sumario de Actos
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- SortableJS para drag & drop de apartados (defer: no bloquea el parseo) --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lista = document.getElementById('lista-apartados');
            const contador = document.getElementById('contador-apartados');

            // ─── DRAG & DROP ───────────────────────────────────────────
            if (lista) {
                Sortable.create(lista, {
                    handle: '.drag-handle',       // Solo arrastra desde el icono de puntos
                    animation: 150,
                    ghostClass: 'bg-indigo-100',  // Color fantasma al arrastrar
                    onEnd: function () {
                        actualizarNumerosDeOrden();
                    }
                });
            }

            // ─── ACTUALIZAR NÚMEROS VISUALES DE POSICIÓN ──────────────
            function actualizarNumerosDeOrden() {
                const items = document.querySelectorAll('#lista-apartados .apartado-item');
                items.forEach(function (item, index) {
                    const badge = item.querySelector('.posicion-numero');
                    if (badge) badge.textContent = index + 1;
                });
            }

            // ─── CONTADOR DE SELECCIONADOS ─────────────────────────────
            function actualizarContador() {
                const seleccionados = document.querySelectorAll('.apartado-checkbox:checked').length;
                contador.textContent = seleccionados === 1
                    ? '1 Apartado seleccionado para esta edición'
                    : seleccionados + ' Apartados seleccionados para esta edición';

                if (seleccionados > 0) {
                    contador.classList.replace('bg-indigo-100', 'bg-emerald-100');
                    contador.classList.replace('text-indigo-800', 'text-emerald-800');
                } else {
                    contador.classList.replace('bg-emerald-100', 'bg-indigo-100');
                    contador.classList.replace('text-emerald-800', 'text-indigo-800');
                }
            }

            // Escuchar cambios en checkboxes
            document.querySelectorAll('.apartado-checkbox').forEach(function (chk) {
                chk.addEventListener('change', actualizarContador);
            });

            actualizarContador();
        });

        // ─── CONSTRUIR CAMPOS HIDDEN ANTES DE ENVIAR ──────────────────
        // Recorre el DOM en su orden visual actual (tras drag & drop)
        // y sólo incluye los que están marcados con checkbox.
        function inyectarOrdenEnForm() {
            const form = document.getElementById('ensamblaje-form');

            // Eliminar campos previos para no duplicar
            form.querySelectorAll('input[name="apartados_ordenados[]"]').forEach(el => el.remove());

            const items = document.querySelectorAll('#lista-apartados .apartado-item');
            let posicion = 1;

            items.forEach(function (item) {
                const chk = item.querySelector('.apartado-checkbox');
                if (chk && chk.checked) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'apartados_ordenados[]';
                    hidden.value = item.dataset.id;
                    form.appendChild(hidden);
                    posicion++;
                }
            });
        }

        // ─── CONFIRMACIÓN Y ENVÍO ──────────────────────────────────────
        function confirmarEnsamblaje() {
            const seleccionados = document.querySelectorAll('.apartado-checkbox:checked').length;

            if (seleccionados === 0) {
                Swal.fire('Atención', 'Debe seleccionar al menos un apartado para ensamblar la gaceta.', 'warning');
                return;
            }

            const form = document.getElementById('ensamblaje-form');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            Swal.fire({
                title: '¿Confirmar Ensamblaje?',
                text: `Se agruparán ${seleccionados} apartados en esta Gaceta en el orden que definió, y se enviará al proceso de digitalización.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, Ensamblar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    inyectarOrdenEnForm(); // 1. Construir campos con el orden correcto
                    form.submit();         // 2. Enviar
                }
            });
        }
    </script>
</x-app-layout>