<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ __('Estantes (Nivel 2)') }}
            </h2>
            <a href="{{ route('estantes.create') }}" class="btn btn-primary">
                Nuevo Estante
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: 'success', title: "{!! addslashes(session('success')) !!}" });
                    });
                </script>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Sede (Nivel Superior)</th>
                                    <th>Nombre de Estante</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estantes as $estante)
                                    <tr>
                                        <td>{{ $estante->sede->nombre }}</td>
                                        <td class="font-medium text-slate-800">{{ $estante->nombre }}</td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2">
                                                <x-action-button :href="route('estantes.edit', $estante)"
                                                    action="Editar estante"
                                                    icon="fa-solid fa-pen-to-square"
                                                    tone="edit" />
                                                <form action="{{ route('estantes.destroy', $estante) }}" method="POST" class="inline-flex"
                                                    onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Inhabilitar estante?', text: 'El estante dejará de estar disponible para ubicar gacetas.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, inhabilitar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });">
                                                    @csrf @method('DELETE')
                                                    <x-action-button type="submit"
                                                        action="Inhabilitar estante"
                                                        icon="fa-solid fa-trash-can"
                                                        tone="delete" />
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
