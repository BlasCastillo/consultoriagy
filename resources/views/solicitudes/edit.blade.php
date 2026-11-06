<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Corregir Apartado de Publicación y Re-enviar a Jefatura') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('solicitudes.update', $solicitud->id) }}" method="POST" enctype="multipart/form-data" id="solicitudForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label for="titulo_acto" class="block font-medium text-sm text-slate-700 mb-2">Asunto / Número de Oficio de Remisión <span class="text-red-500">*</span></label>
                            <input type="text" name="titulo_acto" id="titulo_acto" maxlength="255" required class="val-texto-limpio block w-full border-slate-300 focus:border-blue-800 focus:ring-blue-800 rounded-md shadow-sm sm:text-sm" placeholder="Ej. Oficio de Remisión Nro 001-2026..." value="{{ old('titulo_acto', $solicitud->titulo_acto) }}">
                            @error('titulo_acto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if(!auth()->user()->institucion_id)
                        <div class="mb-6">
                            <label for="institucion_id" class="block font-medium text-sm text-slate-700 mb-2">Institución <span class="text-red-500">*</span></label>
                            <select name="institucion_id" id="institucion_id" required class="block w-full border-slate-300 focus:border-blue-800 focus:ring-blue-800 rounded-md shadow-sm sm:text-sm">
                                <option value="" disabled selected>Seleccione una institución</option>
                                @foreach($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}" {{ old('institucion_id', $solicitud->institucion_id) == $institucion->id ? 'selected' : '' }}>{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                            @error('institucion_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="mb-6">
                            <label for="archivo_pdf" class="block font-medium text-sm text-slate-700 mb-2">Documento Oficial (Solo PDF) - <span class="text-xs text-slate-500 font-normal">Opcional. Subir un nuevo archivo reemplazará el anterior.</span></label>
                            <input type="file" name="archivo_pdf" id="archivo_pdf" accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="mt-1 text-xs text-slate-500">Documento actual: <a href="{{ Storage::url($solicitud->archivo_pdf_path) }}" target="_blank" class="text-blue-600 underline">Ver Documento</a></p>
                            @error('archivo_pdf') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- SECCION DE ACTOS ADMINISTRATIVOS -->
                        <div class="mb-6 mt-8 p-4 border border-slate-200 rounded-lg bg-slate-50">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-slate-800">Actos Administrativos (Sumario)</h3>
                                <button type="button" id="btnAddActo" class="px-3 py-1.5 bg-emerald-600 text-white text-sm font-medium rounded shadow-sm hover:bg-emerald-700 transition">
                                    + Agregar Acto
                                </button>
                            </div>
                            
                            @error('actos') <span class="text-red-500 text-xs mb-2 block">{{ $message }}</span> @enderror
                            
                            <div id="actosContainer" class="space-y-4">
                                <!-- Filas de actos generadas por JS -->
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('solicitudes.index') }}" class="btn btn-back">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Corregir y Re-enviar a Jefatura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('actosContainer');
            const btnAdd = document.getElementById('btnAddActo');
            let actoIndex = 0;

            function addActoRow(acto = {tipo_acto: '', numero: '', sumario: ''}) {
                const row = document.createElement('div');
                row.className = 'acto-row flex gap-3 items-start bg-white p-3 border border-slate-200 rounded shadow-sm relative';
                row.innerHTML = `
                    <div class="w-1/4">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Tipo de Acto <span class="text-red-500">*</span></label>
                        <select name="actos[${actoIndex}][tipo_acto]" required class="block w-full border-slate-300 focus:border-blue-800 focus:ring-blue-800 rounded-md shadow-sm sm:text-sm">
                            <option value="" disabled ${acto.tipo_acto ? '' : 'selected'}>Seleccione</option>
                            <option value="Decreto" ${acto.tipo_acto === 'Decreto' ? 'selected' : ''}>Decreto</option>
                            <option value="Resolución" ${acto.tipo_acto === 'Resolución' ? 'selected' : ''}>Resolución</option>
                            <option value="Providencia" ${acto.tipo_acto === 'Providencia' ? 'selected' : ''}>Providencia</option>
                            <option value="Ordenanza" ${acto.tipo_acto === 'Ordenanza' ? 'selected' : ''}>Ordenanza</option>
                            <option value="Acuerdo" ${acto.tipo_acto === 'Acuerdo' ? 'selected' : ''}>Acuerdo</option>
                            <option value="Ley" ${acto.tipo_acto === 'Ley' ? 'selected' : ''}>Ley</option>
                            <option value="Otro" ${acto.tipo_acto === 'Otro' ? 'selected' : ''}>Otro</option>
                        </select>
                    </div>
                    <div class="w-1/4">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Número <span class="text-red-500">*</span></label>
                        <input type="text" name="actos[${actoIndex}][numero]" value="${(acto.numero || '').replace(/"/g, '&quot;')}" maxlength="100" placeholder="Ej. 001-2026" required class="val-texto-limpio block w-full border-slate-300 focus:border-blue-800 focus:ring-blue-800 rounded-md shadow-sm sm:text-sm">
                    </div>
                    <div class="w-2/4">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Sumario <span class="text-red-500">*</span></label>
                        <textarea name="actos[${actoIndex}][sumario]" placeholder="Descripción para la gaceta..." required class="val-texto-limpio block w-full border-slate-300 focus:border-blue-800 focus:ring-blue-800 rounded-md shadow-sm sm:text-sm resize-none h-10">${acto.sumario || ''}</textarea>
                    </div>
                    <div class="pt-6">
                        <button type="button" class="btn-remove-acto text-red-500 hover:text-red-700 p-1 bg-red-50 rounded" title="Eliminar Fila">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                `;
                
                // Boton eliminar
                row.querySelector('.btn-remove-acto').addEventListener('click', function() {
                    if (container.querySelectorAll('.acto-row').length > 1) {
                        row.remove();
                    }
                });

                container.appendChild(row);
                actoIndex++;
            }

            btnAdd.addEventListener('click', () => addActoRow());

            // Agregar actos iniciales
            const initialActos = Object.values(@json(old('actos', $solicitud->actos)));
            if (initialActos && initialActos.length > 0) {
                initialActos.forEach(acto => addActoRow(acto));
            } else {
                addActoRow();
            }
        });
    </script>
</x-app-layout>