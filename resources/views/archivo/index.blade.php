<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kardex de Archivo: Inventario y Préstamos') }}
            </h2>
            <div class="space-x-2">
                @can('read PrestamoArchivo')
                <button onclick="abrirModalReporte()" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded shadow transition font-medium text-sm">
                    <i class="fa-solid fa-print mr-1"></i> Imprimir Inventario
                </button>
                @endcan
                @can('create PrestamoArchivo')
                <a href="{{ route('archivo.retroactiva.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-4 rounded shadow transition font-medium text-sm">
                    <i class="fa-solid fa-upload mr-1"></i> Subir Gaceta Histórica
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'inventario' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- SweetAlert2 Toasts --}}
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
                            icon: 'success', title: "{!! addslashes(session('success')) !!}"
                        });
                    });
                </script>
            @endif
            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
                            icon: 'error', title: "{!! addslashes(session('error')) !!}"
                        });
                    });
                </script>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button @click="tab = 'inventario'" :class="{ 'border-slate-800 text-slate-800 font-bold': tab === 'inventario', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'inventario' }" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                            Inventario Físico
                        </button>
                        <button @click="tab = 'prestamos'" :class="{ 'border-slate-800 text-slate-800 font-bold': tab === 'prestamos', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'prestamos' }" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                            Préstamos Activos
                        </button>
                    </nav>
                </div>
            </div>

            {{-- TAB: INVENTARIO FISICO --}}
            <div x-show="tab === 'inventario'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaceta</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Actual</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($gacetas as $gaceta)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $gaceta->numero }} / {{ $gaceta->anio }}
                                        <div class="text-xs text-gray-500 font-normal">{{ $gaceta->tipo }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($gaceta->inventario && $gaceta->inventario->sede)
                                            {{ $gaceta->inventario->sede->nombre }}
                                            @if($gaceta->inventario->estante) &rsaquo; {{ $gaceta->inventario->estante->nombre }} @endif
                                            @if($gaceta->inventario->gaveta) &rsaquo; {{ $gaceta->inventario->gaveta->nombre }} @endif
                                        @else
                                            Sin asignar
                                        @endif
                                        @can('update PrestamoArchivo')
                                        <button onclick="abrirModalUbicacion({{ $gaceta->id }}, '{{ $gaceta->inventario?->sede_id ?? '' }}', '{{ $gaceta->inventario?->estante_id ?? '' }}', '{{ $gaceta->inventario?->gaveta_id ?? '' }}', {{ $gaceta->inventario?->stock ?? 0 }})" class="ml-2 text-indigo-600 hover:text-indigo-900" title="Editar Ubicación y Stock">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        @endcan
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($gaceta->inventario?->stock ?? 0) > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $gaceta->inventario?->stock ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        @can('update PrestamoArchivo')
                                        @if($gaceta->ejemplares_fisicos > 0)
                                            <button onclick="abrirModalPrestar({{ $gaceta->id }}, '{{ $gaceta->numero }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white py-1 px-3 rounded shadow transition">
                                                Registrar Préstamo
                                            </button>
                                        @else
                                            <button disabled class="bg-gray-300 text-gray-500 py-1 px-3 rounded cursor-not-allowed">
                                                No disponible
                                            </button>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB: PRESTAMOS ACTIVOS --}}
            <div x-show="tab === 'prestamos'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaceta N°</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salida / Est. Devolución</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($prestamosActivos as $prestamo)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ $prestamo->gaceta->numero }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $prestamo->solicitante_nombres }}
                                        <div class="text-xs text-gray-500">CI: {{ $prestamo->solicitante_cedula }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <div><span class="font-semibold">S:</span> {{ $prestamo->fecha_salida->format('d/m/Y') }}</div>
                                        <div class="{{ $prestamo->fecha_devolucion_estimada < now() ? 'text-red-600 font-bold' : '' }}"><span class="font-semibold text-gray-700">E:</span> {{ $prestamo->fecha_devolucion_estimada->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $prestamo->telefono ?? 'N/A' }}
                                        <div class="text-xs text-gray-400">{{ $prestamo->correo ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        @can('update PrestamoArchivo')
                                        <form action="{{ route('archivo.devolver', $prestamo->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                onclick="event.preventDefault(); const form = this.closest('form'); Swal.fire({ title: '¿Confirmar devolución?', text: 'Se registrará que la gaceta fue devuelta físicamente al archivo.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, marcar como devuelto', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                                class="bg-emerald-500 hover:bg-emerald-600 text-white py-1.5 px-3 rounded shadow transition mb-1 sm:mb-0">
                                                Marcar como Devuelto
                                            </button>
                                        </form>
                                        <a href="{{ route('archivo.prestamo.pdf', $prestamo->id) }}" target="_blank" class="bg-slate-500 hover:bg-slate-600 text-white py-1.5 px-3 rounded shadow transition inline-block">
                                            Imprimir Acta
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                                        No hay préstamos activos en este momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- MODALES --}}
    
    {{-- Modal Reporte Inventario --}}
    <div id="modalReporteInventario" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModalReporte()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('archivo.reporte.inventario') }}" method="GET" target="_blank">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Reporte de Inventario Físico</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Filtrar por Sede (Opcional)</label>
                            <select name="sede_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Todas las Sedes</option>
                                @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" onclick="cerrarModalReporte()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Generar PDF</button>
                        <button type="button" onclick="cerrarModalReporte()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Modal Ubicacion --}}
    <div id="modalUbicacion" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModalUbicacion()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="formUbicacion" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Actualizar Ubicación e Inventario</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Sede</label>
                            <select name="sede_id" id="inputSede" onchange="cargarEstantes()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">Seleccione una sede...</option>
                                @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Estante</label>
                            <select name="estante_id" id="inputEstante" onchange="cargarGavetas()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Seleccione un estante...</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Gaveta</label>
                            <select name="gaveta_id" id="inputGaveta" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Seleccione una gaveta...</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Ejemplares Físicos (Stock)</label>
                            <input type="number" name="ejemplares_fisicos" id="inputStock" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-white hover:bg-slate-900 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                        <button type="button" onclick="cerrarModalUbicacion()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Prestar --}}
    <div id="modalPrestar" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModalPrestar()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="formPrestar" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Registrar Préstamo: Gaceta <span id="labelGacetaNro"></span></h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 sm:col-span-1 mb-3">
                                <label class="block text-sm font-medium text-gray-700">Cédula del Solicitante *</label>
                                <input type="text" name="solicitante_cedula" required class="val-numerico mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="col-span-2 sm:col-span-1 mb-3">
                                <label class="block text-sm font-medium text-gray-700">Nombres *</label>
                                <input type="text" name="solicitante_nombres" required class="val-texto-limpio mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="col-span-2 sm:col-span-1 mb-3">
                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="text" name="telefono" class="val-numerico mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="col-span-2 sm:col-span-1 mb-3">
                                <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                <input type="email" name="correo" class="val-texto-limpio mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="col-span-2 mb-3">
                                <label class="block text-sm font-medium text-gray-700">Motivo del Uso *</label>
                                <textarea name="motivo_uso" required rows="2" class="val-texto-limpio mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div class="col-span-2 sm:col-span-1 mb-3">
                                <label class="block text-sm font-medium text-gray-700">Fecha de Salida *</label>
                                <input type="date" name="fecha_salida" value="{{ date('Y-m-d') }}" data-target="#fecha_devolucion_estimada" required class="val-fecha-futura val-fecha-rango mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="col-span-2 sm:col-span-1 mb-3">
                                <label class="block text-sm font-medium text-gray-700">Devolución Estimada *</label>
                                <input type="date" name="fecha_devolucion_estimada" id="fecha_devolucion_estimada" required class="val-fecha-futura mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Registrar Préstamo</button>
                        <button type="button" onclick="cerrarModalPrestar()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirModalReporte() {
            document.getElementById('modalReporteInventario').classList.remove('hidden');
        }
        function cerrarModalReporte() {
            document.getElementById('modalReporteInventario').classList.add('hidden');
        }

        const sedesData = @json($sedes);

        function cargarEstantes(selectedEstanteId = '') {
            const sedeId = document.getElementById('inputSede').value;
            const estanteSelect = document.getElementById('inputEstante');
            estanteSelect.innerHTML = '<option value="">Seleccione un estante...</option>';
            document.getElementById('inputGaveta').innerHTML = '<option value="">Seleccione una gaveta...</option>';
            
            if (!sedeId) return;
            
            const sede = sedesData.find(s => s.id == sedeId);
            if (sede && sede.estantes) {
                sede.estantes.forEach(estante => {
                    const option = document.createElement('option');
                    option.value = estante.id;
                    option.textContent = estante.nombre;
                    if (estante.id == selectedEstanteId) option.selected = true;
                    estanteSelect.appendChild(option);
                });
            }
        }

        function cargarGavetas(selectedGavetaId = '') {
            const sedeId = document.getElementById('inputSede').value;
            const estanteId = document.getElementById('inputEstante').value;
            const gavetaSelect = document.getElementById('inputGaveta');
            gavetaSelect.innerHTML = '<option value="">Seleccione una gaveta...</option>';
            
            if (!sedeId || !estanteId) return;
            
            const sede = sedesData.find(s => s.id == sedeId);
            if (sede && sede.estantes) {
                const estante = sede.estantes.find(e => e.id == estanteId);
                if (estante && estante.gavetas) {
                    estante.gavetas.forEach(gaveta => {
                        const option = document.createElement('option');
                        option.value = gaveta.id;
                        option.textContent = gaveta.nombre;
                        if (gaveta.id == selectedGavetaId) option.selected = true;
                        gavetaSelect.appendChild(option);
                    });
                }
            }
        }

        function abrirModalUbicacion(id, sede_id, estante_id, gaveta_id, stock) {
            document.getElementById('formUbicacion').action = `/archivo/gacetas/${id}/inventario`;
            document.getElementById('inputSede').value = sede_id === 'null' ? '' : sede_id;
            cargarEstantes(estante_id === 'null' ? '' : estante_id);
            cargarGavetas(gaveta_id === 'null' ? '' : gaveta_id);
            document.getElementById('inputStock').value = stock;
            document.getElementById('modalUbicacion').classList.remove('hidden');
        }
        function cerrarModalUbicacion() {
            document.getElementById('modalUbicacion').classList.add('hidden');
        }

        function abrirModalPrestar(id, numero) {
            document.getElementById('formPrestar').action = `/archivo/prestar/${id}`;
            document.getElementById('labelGacetaNro').innerText = numero;
            document.getElementById('modalPrestar').classList.remove('hidden');
        }
        function cerrarModalPrestar() {
            document.getElementById('modalPrestar').classList.add('hidden');
        }
    </script>
</x-app-layout>
