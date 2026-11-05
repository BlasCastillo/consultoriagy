<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Mesa de Control: Gacetas Oficiales') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Tipo</th>
                                    <th>Fecha Ensamblaje</th>
                                    <th>Estado</th>
                                    <th>Digitalizador</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gacetas as $gaceta)
                                    <tr>
                                        <td class="font-medium text-slate-900">
                                            {{ $gaceta->numero }}
                                        </td>
                                        <td>
                                            {{ $gaceta->tipo }}
                                        </td>
                                        <td>
                                            {{ $gaceta->created_at->format('d/m/Y h:i A') }}
                                        </td>
                                        <td>
                                            @if($gaceta->estado == 'Publicada')
                                                <span class="badge badge-success">Publicada</span>
                                            @elseif($gaceta->estado == 'Devuelta')
                                                <span class="badge badge-danger" title="{{ $gaceta->motivo_devolucion }}">
                                                    Devuelta
                                                </span>
                                            @else
                                                <span class="badge badge-warning">{{ $gaceta->estado }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $gaceta->digitalizador->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                <x-action-button :href="route('gacetas.show', $gaceta->id)"
                                                    action="Ver detalles de la gaceta"
                                                    icon="fa-solid fa-eye"
                                                    tone="neutral" />

                                                @if($gaceta->estado === 'En Revisión Final')
                                                    <x-action-button :href="route('gacetas.revision', $gaceta->id)"
                                                        action="Revisar PDF final"
                                                        icon="fa-solid fa-magnifying-glass-chart"
                                                        tone="brand" />
                                                @endif

                                                @if($gaceta->estado === 'Devuelta')
                                                    <x-action-button :href="route('gacetas.edit', $gaceta->id)"
                                                        action="Subsanar / editar gaceta"
                                                        icon="fa-solid fa-pen-to-square"
                                                        tone="warning" />
                                                @endif

                                                @if($gaceta->estado !== 'Publicada')
                                                    <form id="form-reasignar-{{ $gaceta->id }}" action="{{ route('gacetas.reasignar', $gaceta->id) }}" method="POST" class="inline-flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="digitalizador_id" class="rounded-md border-slate-300 py-1.5 text-xs text-slate-600 shadow-sm focus:border-blue-800 focus:ring-blue-800" data-original="{{ $gaceta->digitalizador_id }}" onchange="confirmarReasignacion(this, {{ $gaceta->id }})">
                                                            <option value="">Reasignar a...</option>
                                                            @foreach($digitalizadores as $dig)
                                                                <option value="{{ $dig->id }}" {{ $gaceta->digitalizador_id == $dig->id ? 'selected' : '' }}>
                                                                    {{ $dig->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-500">
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
                confirmButtonColor: '#1e3a8a',
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
