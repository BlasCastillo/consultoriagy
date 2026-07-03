<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fast-Track: Subir Gaceta Histórica') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('archivo.retroactiva.store') }}" method="POST" enctype="multipart/form-data" id="historicoForm">
                        @csrf
                        
                        {{-- BLOQUE SUPERIOR: METADATOS DE LA GACETA --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">1. Metadatos de la Gaceta</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Número de Gaceta <span class="text-red-500">*</span></label>
                                    <input type="text" name="numero" value="{{ old('numero') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo <span class="text-red-500">*</span></label>
                                    <select name="tipo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="Ordinaria" {{ old('tipo') == 'Ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                                        <option value="Extraordinaria" {{ old('tipo') == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Publicación <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_publicacion" value="{{ old('fecha_publicacion') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Gobernador <span class="text-red-500">*</span></label>
                                    <select name="gobernador_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($gobernadores as $gobernador)
                                            <option value="{{ $gobernador->id }}" {{ old('gobernador_id') == $gobernador->id ? 'selected' : '' }}>
                                                {{ $gobernador->nombres }} {{ $gobernador->apellidos }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Año Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="ano_politico" value="{{ old('ano_politico') }}" required placeholder="Ej: 164°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mes Político <span class="text-red-500">*</span></label>
                                    <input type="text" name="mes_politico" value="{{ old('mes_politico') }}" required placeholder="Ej: 164°" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700">Archivo PDF Escaneado (Max 20MB) <span class="text-red-500">*</span></label>
                                    <input type="file" name="archivo_pdf_completo" accept="application/pdf" required class="mt-1 block w-full text-sm text-slate-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 file:text-indigo-700
                                        hover:file:bg-indigo-100 border border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        {{-- BLOQUE INFERIOR: SUMARIO DINAMICO (ACTOS) --}}
                        <div class="mb-6">
                            <div class="flex justify-between items-center border-b pb-2 mb-4">
                                <h3 class="text-lg font-bold text-gray-800">2. Sumario de Actos</h3>
                                <button type="button" onclick="agregarActo()" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-1 rounded shadow-sm text-sm font-medium transition">
                                    <i class="fa-solid fa-plus mr-1"></i> Agregar otro Acto
                                </button>
                            </div>

                            <div id="actosContainer" class="space-y-6">
                                <!-- Plantilla Inicial del Acto (Index 0) -->
                                <div class="acto-row bg-slate-50 border border-slate-200 p-4 rounded-md relative">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-semibold text-gray-600 uppercase">Institución Emisora</label>
                                            <select name="actos[0][institucion_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Seleccione Institución...</option>
                                                @foreach($instituciones as $inst)
                                                    <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-semibold text-gray-600 uppercase">Tipo de Acto</label>
                                            <select name="actos[0][tipo_acto]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">Seleccione...</option>
                                                <option value="Decreto">Decreto</option>
                                                <option value="Resolución">Resolución</option>
                                                <option value="Providencia">Providencia</option>
                                                <option value="Acuerdo">Acuerdo</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-semibold text-gray-600 uppercase">Número de Acto</label>
                                            <input type="text" name="actos[0][numero]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </div>
                                        <div class="md:col-span-1 flex items-end justify-center pb-1">
                                            <!-- Botón eliminar (oculto en el primero) -->
                                            <button type="button" onclick="eliminarActo(this)" class="hidden text-red-500 hover:text-red-700 transition" title="Eliminar Acto">
                                                <i class="fa-solid fa-trash-can text-lg"></i>
                                            </button>
                                        </div>
                                        <div class="md:col-span-12">
                                            <label class="block text-xs font-semibold text-gray-600 uppercase">Sumario / Extracto</label>
                                            <textarea name="actos[0][sumario]" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <a href="{{ route('archivo.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded shadow-sm hover:bg-gray-50 font-medium mr-3 transition">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded shadow-sm font-medium transition" onclick="return confirm('¿Está seguro de procesar esta Gaceta Histórica? La indexación será permanente.')">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Procesar e Indexar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        let actoIndex = 1; // Contador para los índices de los arrays

        function agregarActo() {
            const container = document.getElementById('actosContainer');
            
            // Crear el nuevo div
            const newRow = document.createElement('div');
            newRow.className = 'acto-row bg-slate-50 border border-slate-200 p-4 rounded-md relative mt-4';
            
            // HTML base copiado y adaptado para inyectar el nuevo índice
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Institución Emisora</label>
                        <select name="actos[${actoIndex}][institucion_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Seleccione Institución...</option>
                            @foreach($instituciones as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Tipo de Acto</label>
                        <select name="actos[${actoIndex}][tipo_acto]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Seleccione...</option>
                            <option value="Decreto">Decreto</option>
                            <option value="Resolución">Resolución</option>
                            <option value="Providencia">Providencia</option>
                            <option value="Acuerdo">Acuerdo</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Número de Acto</label>
                        <input type="text" name="actos[${actoIndex}][numero]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="md:col-span-1 flex items-end justify-center pb-1">
                        <button type="button" onclick="eliminarActo(this)" class="text-red-500 hover:text-red-700 transition" title="Eliminar Acto">
                            <i class="fa-solid fa-trash-can text-lg"></i>
                        </button>
                    </div>
                    <div class="md:col-span-12">
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Sumario / Extracto</label>
                        <textarea name="actos[${actoIndex}][sumario]" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
            `;
            
            container.appendChild(newRow);
            actoIndex++;
        }

        function eliminarActo(button) {
            const row = button.closest('.acto-row');
            if(document.querySelectorAll('.acto-row').length > 1) {
                row.remove();
            }
        }
    </script>
</x-app-layout>
