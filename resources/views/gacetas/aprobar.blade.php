<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Aprobar Gaceta para Publicación') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body text-slate-900">
                    <h3 class="mb-6 border-b border-slate-200 pb-2 text-lg font-bold text-slate-800">Revisión Final</h3>

                    <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="mb-2 text-xl font-bold text-slate-800">Gaceta Nro. {{ str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="mb-1 text-sm text-slate-600"><strong>Tipo:</strong> {{ $gaceta->tipo }}</p>
                            <p class="text-sm text-slate-600"><strong>Gobernador:</strong> {{ $gaceta->gobernador->nombres }} {{ $gaceta->gobernador->apellidos }}</p>
                        </div>

                        <div class="flex flex-col items-center justify-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-bold text-slate-700">Archivo Escaneado Subido:</p>
                            <a href="{{ route('gacetas.preview', $gaceta->id) }}" target="_blank"
                                class="btn btn-primary w-full">
                                Ver Previsualización Final
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-warning rounded-r-lg">
                        <p class="text-sm font-medium text-amber-800">
                            Revise detalladamente el PDF escaneado. Si aprueba, el sistema unirá automáticamente la portada/sumario con este archivo físico y publicará la gaceta oficial.
                        </p>
                    </div>

                    <form id="approval-form" action="{{ route('gacetas.publicar_oficial', $gaceta->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="accion" id="accion-input" value="">

                        <div class="mt-8 flex flex-col items-center justify-between gap-4 border-t border-slate-200 pt-4 sm:flex-row">
                            <a href="{{ route('gacetas.index') }}" class="btn btn-back w-full sm:w-auto">
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver
                            </a>

                            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                                <button type="button" onclick="confirmarRechazo()" class="btn btn-danger w-full sm:w-auto">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i> Rechazar y Devolver
                                </button>

                                <button type="button" onclick="confirmarAprobacion()" class="btn btn-primary w-full sm:w-auto">
                                    <i class="fa-solid fa-check" aria-hidden="true"></i> Aprobar y Publicar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmarRechazo() {
            Swal.fire({
                title: '¿Rechazar Escaneo?',
                text: 'Esta gaceta será devuelta al digitalizador.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('accion-input').value = 'rechazar';
                    document.getElementById('approval-form').submit();
                }
            });
        }

        function confirmarAprobacion() {
            Swal.fire({
                title: '¿Publicar Gaceta?',
                text: 'El sistema unirá los archivos y publicará la gaceta oficialmente.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#1e293b',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, publicar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('accion-input').value = 'aprobar';
                    document.getElementById('approval-form').submit();
                }
            });
        }
    </script>
</x-app-layout>
