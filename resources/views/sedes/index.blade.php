<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ __('Sedes (Edificios)') }}
            </h2>
            <a href="{{ route('sedes.create') }}" class="btn btn-primary">
                Nueva Sede
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
                                    <th>Nombre</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sedes as $sede)
                                    <tr>
                                        <td class="font-medium text-slate-800">{{ $sede->nombre }}</td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('sedes.edit', $sede) }}" class="btn btn-warning btn-sm">Editar</a>
                                                <form action="{{ route('sedes.destroy', $sede) }}" method="POST" class="inline-block"
                                                    onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Inhabilitar sede?', text: 'La sede dejará de estar disponible para ubicar gacetas.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, inhabilitar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
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
