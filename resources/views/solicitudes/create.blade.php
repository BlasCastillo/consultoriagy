<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Nueva Solicitud de Publicación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <h3 class="text-lg font-bold mb-4 text-slate-800 border-b border-slate-200 pb-2">Datos del Sumario a Publicar</h3>
                    <p class="mb-6 text-sm text-slate-600">Complete los datos correspondientes a los actos administrativos que desea remitir para su publicación en Gaceta Oficial.</p>

                    <div class="bg-slate-50 p-4 rounded-md mb-6 border border-slate-200">
                        <p class="text-sm font-semibold text-slate-700">Institución Remitente:</p>
                        <p class="text-lg font-bold text-slate-800">{{ $institution->name }}</p>
                    </div>

                    <form action="{{ route('solicitudes.store') }}" method="POST" id="solicitudForm" onsubmit="confirmarEnvio(event)" autocomplete="off">
                        @csrf
                        
                        <div id="sumarios-container" class="space-y-4">
                            <div class="sumario-item border border-slate-200 p-4 rounded-md bg-white shadow-sm relative transition-all">
                                <h4 class="text-sm font-bold text-slate-700 mb-3 border-b border-slate-100 pb-1">Acto Administrativo #1</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Tipo de Acto <span class="text-red-500">*</span></label>
                                        <select name="sumarios[0][tipo_acto]" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                                            <option value="">Seleccione...</option>
                                            <option value="Providencia Administrativa">Providencia Administrativa</option>
                                            <option value="Resolución">Resolución</option>
                                            <option value="Acuerdo">Acuerdo</option>
                                            <option value="Auto">Auto</option>
                                            <option value="Aviso Oficial">Aviso Oficial</option>
                                            <option value="Cartel de Notificación">Cartel de Notificación</option>
                                            <option value="Acta">Acta</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Número de Acto <span class="text-red-500">*</span></label>
                                        <input type="text" name="sumarios[0][numero_acto]" required placeholder="Ej: 001-2026" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-slate-700">Descripción Corta / Asunto <span class="text-red-500">*</span></label>
                                        <textarea name="sumarios[0][descripcion]" required rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" placeholder="Resumen del contenido del acto"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-start">
                            <button type="button" id="add-sumario" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-emerald-600 hover:bg-emerald-700 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Añadir Otro Acto a esta misma solicitud
                            </button>
                        </div>

                        <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                            <a href="{{ route('mis-solicitudes.index') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-200 hover:bg-slate-300 text-slate-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-800 hover:bg-slate-900 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Enviar Solicitud a Consultoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // SweetAlert para confirmación de envío
        function confirmarEnvio(event) {
            event.preventDefault();
            const form = event.target;
            
            Swal.fire({
                title: '¿Enviar solicitud?',
                text: 'Los actos administrativos serán remitidos a Consultoría Jurídica para su evaluación y posterior publicación.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e293b', // slate-800
                cancelButtonColor: '#64748b',  // slate-500
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Revisar datos'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let sumarioCount = 1;
            const container = document.getElementById('sumarios-container');
            const addButton = document.getElementById('add-sumario');

            addButton.addEventListener('click', function() {
                const template = `
                    <div class="sumario-item border border-slate-200 p-4 rounded-md bg-white shadow-sm relative mt-4 transition-all">
                        <button type="button" class="remove-sumario absolute top-3 right-3 p-2 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center font-medium bg-red-600 hover:bg-red-700 text-white" title="Eliminar este acto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        <h4 class="text-sm font-bold text-slate-700 mb-3 border-b border-slate-100 pb-1">Acto Administrativo #${sumarioCount + 1}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tipo de Acto <span class="text-red-500">*</span></label>
                                <select name="sumarios[${sumarioCount}][tipo_acto]" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="Providencia Administrativa">Providencia Administrativa</option>
                                    <option value="Resolución">Resolución</option>
                                    <option value="Acuerdo">Acuerdo</option>
                                    <option value="Auto">Auto</option>
                                    <option value="Aviso Oficial">Aviso Oficial</option>
                                    <option value="Cartel de Notificación">Cartel de Notificación</option>
                                    <option value="Acta">Acta</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Número de Acto <span class="text-red-500">*</span></label>
                                <input type="text" name="sumarios[${sumarioCount}][numero_acto]" required placeholder="Ej: 001-2026" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700">Descripción Corta / Asunto <span class="text-red-500">*</span></label>
                                <textarea name="sumarios[${sumarioCount}][descripcion]" required rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" placeholder="Resumen del contenido del acto"></textarea>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', template);
                sumarioCount++;
            });

            container.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-sumario');
                if (removeBtn) {
                    const itemToRemove = removeBtn.closest('.sumario-item');
                    
                    // Alerta de confirmación antes de eliminar la fila
                    Swal.fire({
                        title: '¿Eliminar este acto?',
                        text: "Se removerá de la solicitud actual.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626', // red-600
                        cancelButtonColor: '#64748b',  // slate-500
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            itemToRemove.remove();
                        }
                    });
                }
            });
        });
    </script>
</x-app-layout>