<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Registrar Nueva Gaceta') }}
            </h2>
            <a href="{{ route('gacetas.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Volver al listado</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                
                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('gacetas.store') }}" enctype="multipart/form-data" id="gacetaForm">
                    @csrf
                    
                    <div class="border-b border-gray-200 pb-6 mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Datos de la Gaceta (Cabecera)</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Año *</label>
                                <input type="number" name="anio" value="{{ old('anio', date('Y')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="text-xs text-gray-500 mt-1">El sistema asignará automáticamente el número siguiente al guardar.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo *</label>
                                <select name="tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="Ordinaria" {{ old('tipo') == 'Ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                                    <option value="Extraordinaria" {{ old('tipo') == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Año Político</label>
                                <input type="text" name="anio_politico" value="{{ old('anio_politico') }}" placeholder="Ej: AÑO CXIV" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mes Político</label>
                                <input type="text" name="mes_politico" value="{{ old('mes_politico') }}" placeholder="Ej: MES IV" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha Emisión</label>
                                <input type="date" name="fecha_emision" value="{{ old('fecha_emision') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha Recepción Física (Checklist)</label>
                                <input type="date" name="fecha_recepcion_fisica" value="{{ old('fecha_recepcion_fisica') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Corregida de (Art. 5) - Opcional</label>
                                <select name="corregida_de_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Ninguna</option>
                                    @foreach($gacetas as $g)
                                        <option value="{{ $g->id }}" {{ old('corregida_de_id') == $g->id ? 'selected' : '' }}>
                                            Gaceta {{ $g->numero }} / {{ $g->anio }} ({{ $g->tipo }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sumarios Dinámicos -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Actos Administrativos (Sumario)</h3>
                            <button type="button" id="addSumarioBtn" class="inline-flex items-center px-3 py-1.5 bg-indigo-100 border border-transparent rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-200">
                                + Añadir Fila
                            </button>
                        </div>

                        <div id="sumariosContainer" class="space-y-4">
                            <!-- Template for row -->
                            <div class="sumario-row bg-gray-50 p-4 rounded-md border border-gray-200 relative">
                                <button type="button" class="remove-row absolute top-2 right-2 text-red-500 hover:text-red-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Institución *</label>
                                        <select name="sumarios[0][institucion_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <option value="">Seleccione...</option>
                                            @foreach($institutions as $inst)
                                                <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Tipo Acto *</label>
                                        <select name="sumarios[0][tipo_acto]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <option value="Decreto">Decreto</option>
                                            <option value="Ley">Ley</option>
                                            <option value="Resolución">Resolución</option>
                                            <option value="Acuerdo">Acuerdo</option>
                                            <option value="Providencia">Providencia</option>
                                            <option value="Aviso Oficial">Aviso Oficial</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Nro Acto *</label>
                                        <input type="text" name="sumarios[0][numero_acto]" required placeholder="Ej: E-123" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-medium text-gray-700">Descripción *</label>
                                        <textarea name="sumarios[0][descripcion]" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Guardar y Reservar Número
                        </button>
                    </div>
                </form>
            </div>
        <    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('sumariosContainer');
            const addBtn = document.getElementById('addSumarioBtn');
            let rowCount = 1;

            const getRowHtml = (index) => `
                <div class="sumario-row bg-gray-50 p-4 rounded-md border border-gray-200 mt-4 flex items-start gap-4">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Institución *</label>
                            <select name="sumarios[${index}][institucion_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Seleccione...</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->id }}">{{ str_replace("'", "\\'", $inst->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Tipo Acto *</label>
                            <select name="sumarios[${index}][tipo_acto]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="Decreto">Decreto</option>
                                <option value="Ley">Ley</option>
                                <option value="Resolución">Resolución</option>
                                <option value="Acuerdo">Acuerdo</option>
                                <option value="Providencia">Providencia</option>
                                <option value="Aviso Oficial">Aviso Oficial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Nro Acto *</label>
                            <input type="text" name="sumarios[${index}][numero_acto]" required placeholder="Ej: E-123" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-700">Descripción *</label>
                            <textarea name="sumarios[${index}][descripcion]" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        </div>
                    </div>
                    <!-- Botón de eliminar alineado a la derecha y sin estorbar -->
                    <div class="flex-none pt-6"> 
                        <button type="button" class="remove-row text-red-500 hover:text-red-700 p-2 border border-red-200 rounded-md bg-white shadow-sm transition-colors" title="Eliminar fila">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            `;

            addBtn.addEventListener('click', function () {
                container.insertAdjacentHTML('beforeend', getRowHtml(rowCount));
                rowCount++;
            });

            container.addEventListener('click', function (e) {
                if (e.target.closest('.remove-row')) {
                    const rows = container.querySelectorAll('.sumario-row');
                    if (rows.length > 1) {
                        e.target.closest('.sumario-row').remove();
                    } else {
                        alert('Debe haber al menos un acto administrativo en el sumario.');
                    }
                }
            });
        });
    </script>
</x-app-layout>
