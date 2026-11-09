<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Mesa de Ensamblaje: Nueva Gaceta Oficial') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="ensamblaje-form" action="{{ route('gacetas.store') }}" method="POST">
                @csrf
                <div class="flex flex-col lg:flex-row gap-6">
                    
                    {{-- Columna Izquierda: Metadata --}}
                    <div class="w-full lg:w-1/3">
                        <div class="card p-6">
                            <h3 class="mb-4 border-b border-slate-200 pb-2 text-lg font-bold text-slate-800">Metadatos de la Gaceta</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Número de Gaceta <span class="text-red-500">*</span></label>
                                    <input type="text" inputmode="numeric" name="numero" value="{{ old('numero') }}" maxlength="10" required class="val-numerico mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Tipo de Edición <span class="text-red-500">*</span></label>
                                    <select name="tipo" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="Ordinaria" {{ old('tipo') == 'Ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                                        <option value="Extraordinaria" {{ old('tipo') == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                                    </select>
                                </div>

                                <div class="mb-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Año Político <span class="text-red-500">*</span></label>
                                        <input type="text" name="ano_politico" value="{{ old('ano_politico') }}" maxlength="100" required placeholder="Ej: 118°" class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <p class="mt-1 text-xs text-slate-500">Escriba el año en números (ej. 118); se guardará como CXVIII.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Mes Político <span class="text-red-500">*</span></label>
                                        <input type="text" name="mes_politico" value="{{ old('mes_politico') }}" maxlength="100" required placeholder="Ej: 120°" class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Fecha de Publicación <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_publicacion" value="{{ old('fecha_publicacion') }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Gobernador <span class="text-red-500">*</span></label>
                                    <select name="gobernador_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($gobernadores as $gob)
                                            <option value="{{ $gob->id }}" {{ old('gobernador_id') == $gob->id ? 'selected' : '' }}>{{ $gob->nombres ?? 'Sin Nombre' }} {{ $gob->apellidos ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Digitalizador Responsable <span class="text-red-500">*</span></label>
                                    <select name="digitalizador_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($digitalizadores as $digitalizador)
                                            <option value="{{ $digitalizador->id }}" {{ old('digitalizador_id') == $digitalizador->id ? 'selected' : '' }}>{{ $digitalizador->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pt-6">
                                    <button type="button" onclick="confirmarEnsamblaje()" class="btn btn-primary w-full">
                                        Ensamblar Gaceta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Columna Derecha: Selección y Orden de Apartados --}}
                    <div class="w-full lg:w-2/3">
                        <div class="card h-full p-6">
                            <div class="mb-2 flex flex-col justify-between gap-2 border-b border-slate-200 pb-2 sm:flex-row sm:items-center">
                                <h3 class="text-lg font-bold text-slate-800">Apartados Aprobados</h3>
                                <span id="contador-apartados" class="badge badge-info whitespace-nowrap">0 Apartados seleccionados</span>
                            </div>
                            <p class="mb-4 flex items-center gap-1 text-xs text-slate-500">
                                Marque los apartados a incluir y <strong class="mx-1 text-blue-800">arrástrelos</strong> para definir el orden en que aparecerán en la Gaceta.
                            </p>

                            @if($apartadosAprobados->isEmpty())
                                <div class="py-12 text-center text-slate-500">
                                    No hay apartados aprobados esperando por ensamblaje en este momento.
                                </div>
                            @else
                                {{-- Lista reordenable --}}
                                <ul id="lista-apartados" class="space-y-2 overflow-y-auto pr-2" style="max-height: 600px;">
                                    @foreach($apartadosAprobados as $apartado)
                                        <li class="apartado-item" data-id="{{ $apartado->id }}">
                                            <div class="flex select-none items-start gap-3 rounded-lg border border-slate-200 bg-white p-3 transition-colors hover:bg-slate-50">
                                                {{-- Handle de arrastre --}}
                                                <div class="drag-handle mt-1 flex-shrink-0 cursor-grab pt-1 text-slate-300 hover:text-blue-700" title="Arrastrar para reordenar">
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 8a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zm-6 6a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4z"/></svg>
                                                </div>
                                                {{-- Número de posición --}}
                                                <div class="posicion-numero mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">
                                                    {{ $loop->iteration }}
                                                </div>
                                                {{-- Checkbox --}}
                                                <div class="flex-shrink-0 pt-1">
                                                    <input type="checkbox" name="apartados[]" value="{{ $apartado->id }}"
                                                           class="apartado-checkbox h-5 w-5 rounded border-slate-300 text-blue-900 focus:ring-blue-800"
                                                           data-id="{{ $apartado->id }}"
                                                           {{ in_array($apartado->id, old('apartados_ordenados', old('apartados', []))) ? 'checked' : '' }}>
                                                </div>
                                                {{-- Contenido --}}
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="text-sm font-bold text-slate-800">{{ $apartado->institucion->nombre ?? 'N/A' }}</h4>
                                                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-600" title="{{ $apartado->titulo_acto }}">{{ $apartado->titulo_acto }}</p>
                                                    <div class="mt-2 flex justify-end">
                                                        <x-action-button :href="route('solicitudes.show', $apartado->id)"
                                                            action="Ver sumario de actos"
                                                            icon="fa-solid fa-eye"
                                                            tone="view"
                                                            target="_blank"
                                                            onclick="event.stopPropagation();" />
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
    <script src="{{ asset('assets/vendor/js/Sortable.min.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lista = document.getElementById('lista-apartados');
            const contador = document.getElementById('contador-apartados');

            // ─── DRAG & DROP ───────────────────────────────────────────
            if (lista) {
                Sortable.create(lista, {
                    handle: '.drag-handle',       // Solo arrastra desde el icono de puntos
                    animation: 150,
                    ghostClass: 'bg-emerald-100',  // Color fantasma al arrastrar
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
                    contador.classList.replace('badge-info', 'badge-success');
                } else {
                    contador.classList.replace('badge-success', 'badge-info');
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
            if (!window.validarFormularioSGCJ(form)) return;

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