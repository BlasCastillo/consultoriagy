<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mesa de Control: Gacetas Oficiales') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if(session('success'))
                        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-4 text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Ensamblaje</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Digitalizador</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($gacetas as $gaceta)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $gaceta->numero }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $gaceta->tipo }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $gaceta->created_at->format('d/m/Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($gaceta->estado == 'Publicada')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Publicada
                                                </span>
                                            @elseif($gaceta->estado == 'Devuelta')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" title="{{ $gaceta->motivo_devolucion }}">
                                                    Devuelta
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ $gaceta->estado }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $gaceta->digitalizador->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('gacetas.show', $gaceta->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Ver Detalles</a>

                                            @if($gaceta->estado === 'En Revisión Final')
                                                <a href="{{ route('gacetas.revision', $gaceta->id) }}" class="inline-flex items-center px-2 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                                    Revisar PDF Final
                                                </a>
                                            @endif

                                            @if($gaceta->estado === 'Devuelta')
                                                <form action="{{ route('gacetas.subsanar', $gaceta->id) }}" method="POST" class="inline-block mr-3">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('¿Regresar esta gaceta a En Proceso para corregir apartados o reasignar?')" class="inline-flex items-center px-2 py-1 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                        Subsanar/Editar Gaceta
                                                    </button>
                                                </form>
                                            @endif

                                            @if($gaceta->estado !== 'Publicada')
                                                <form id="form-reasignar-{{ $gaceta->id }}" action="{{ route('gacetas.reasignar', $gaceta->id) }}" method="POST" class="inline-flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="digitalizador_id" class="text-xs rounded border-gray-300 py-1" data-original="{{ $gaceta->digitalizador_id }}" onchange="confirmarReasignacion(this, {{ $gaceta->id }})">
                                                        <option value="">Reasignar a...</option>
                                                        @foreach($digitalizadores as $dig)
                                                            <option value="{{ $dig->id }}" {{ $gaceta->digitalizador_id == $dig->id ? 'selected' : '' }}>
                                                                {{ $dig->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @endif

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No hay Gacetas ensambladas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmarReasignacion(selectElement, gacetaId) {
            const nuevoId = selectElement.value;
            const originalId = selectElement.getAttribute('data-original');
            
            // Si elige la opción vacía o la misma que ya tenía, revertir silenciosamente
            if(nuevoId === "" || nuevoId === originalId) {
                selectElement.value = originalId;
                return;
            }

            Swal.fire({
                title: '¿Reasignar Gaceta?',
                text: "El documento pasará inmediatamente a la bandeja del nuevo digitalizador.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, reasignar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-reasignar-' + gacetaId).submit();
                } else {
                    // Si cancela, revertir el select a su valor original
                    selectElement.value = originalId;
                }
            });
        }
    </script>
</x-app-layout>