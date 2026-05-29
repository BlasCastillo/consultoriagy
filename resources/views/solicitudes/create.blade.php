<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva Solicitud de Publicación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4 text-blue-900 border-b pb-2">Datos del Sumario a Publicar</h3>
                    <p class="mb-6 text-sm text-gray-600">Complete los datos correspondientes a los actos administrativos que desea remitir para su publicación en Gaceta Oficial.</p>

                    <div class="bg-gray-50 p-4 rounded-md mb-6 border border-gray-200">
                        <p class="text-sm font-semibold text-gray-700">Institución Remitente:</p>
                        <p class="text-lg text-blue-900">{{ $institution->name }}</p>
                    </div>

                    <form action="{{ route('solicitudes.store') }}" method="POST">
                        @csrf
                        
                        <div id="sumarios-container" class="space-y-4">
                            <div class="sumario-item border border-gray-200 p-4 rounded-md bg-white shadow-sm relative">
                                <h4 class="text-sm font-bold text-gray-700 mb-3 border-b pb-1">Acto Administrativo #1</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo de Acto <span class="text-red-500">*</span></label>
                                        <select name="sumarios[0][tipo_acto]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
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
                                        <label class="block text-sm font-medium text-gray-700">Número de Acto <span class="text-red-500">*</span></label>
                                        <input type="text" name="sumarios[0][numero_acto]" required placeholder="Ej: 001-2026" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Descripción Corta / Asunto <span class="text-red-500">*</span></label>
                                        <textarea name="sumarios[0][descripcion]" required rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm" placeholder="Resumen del contenido del acto"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-between">
                            <button type="button" id="add-sumario" class="text-sm px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                <i class="fa-solid fa-plus mr-1"></i> Añadir Otro Acto a esta misma solicitud
                            </button>
                        </div>

                        <div class="mt-8 flex justify-end gap-2 border-t pt-4">
                            <a href="{{ route('mis-solicitudes.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-800 transition font-bold shadow">
                                <i class="fa-solid fa-paper-plane mr-1"></i> Enviar Solicitud a Consultoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let sumarioCount = 1;
            const container = document.getElementById('sumarios-container');
            const addButton = document.getElementById('add-sumario');

            addButton.addEventListener('click', function() {
                const template = `
                    <div class="sumario-item border border-gray-200 p-4 rounded-md bg-white shadow-sm relative mt-4">
                        <button type="button" class="remove-sumario absolute top-2 right-2 text-red-500 hover:text-red-700">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <h4 class="text-sm font-bold text-gray-700 mb-3 border-b pb-1">Acto Administrativo #${sumarioCount + 1}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Acto <span class="text-red-500">*</span></label>
                                <select name="sumarios[${sumarioCount}][tipo_acto]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
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
                                <label class="block text-sm font-medium text-gray-700">Número de Acto <span class="text-red-500">*</span></label>
                                <input type="text" name="sumarios[${sumarioCount}][numero_acto]" required placeholder="Ej: 001-2026" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Descripción Corta / Asunto <span class="text-red-500">*</span></label>
                                <textarea name="sumarios[${sumarioCount}][descripcion]" required rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm" placeholder="Resumen del contenido del acto"></textarea>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', template);
                sumarioCount++;
            });

            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-sumario')) {
                    e.target.closest('.sumario-item').remove();
                }
            });
        });
    </script>
</x-app-layout>
