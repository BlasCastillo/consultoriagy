<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Aprobar Gaceta para Publicación') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <h3 class="text-lg font-bold mb-6 text-slate-800 border-b border-slate-200 pb-2">Revisión Final</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <p class="text-xl font-bold text-slate-800 mb-2">Gaceta Nro. {{ str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-sm text-slate-600 mb-1"><strong>Tipo:</strong> {{ $gaceta->tipo }}</p>
                            <p class="text-sm text-slate-600"><strong>Gobernador:</strong> {{ $gaceta->gobernador->nombres }} {{ $gaceta->gobernador->apellidos }}</p>
                        </div>
                        
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 flex flex-col justify-center items-center gap-3">
                            <p class="font-bold text-slate-700 text-sm">Archivo Escaneado Subido:</p>
                            <a href="{{ route('gacetas.preview', $gaceta->id) }}" target="_blank" 
                                class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-base bg-emerald-600 hover:bg-emerald-700 text-white w-full text-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Ver Previsualización Final
                            </a>
                        </div>
                    </div>

                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded-r-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-amber-800 font-medium">
                                    Revise detalladamente el PDF escaneado. Si aprueba, el sistema unirá automáticamente la portada/sumario con este archivo físico y publicará la gaceta oficial.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form id="approval-form" action="{{ route('gacetas.publicar', $gaceta->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="accion" id="accion-input" value="">

                        <div class="flex flex-col sm:flex-row justify-between items-center mt-8 pt-4 border-t border-slate-200 gap-4">
                            <a href="{{ route('gacetas.index') }}" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-base bg-slate-200 hover:bg-slate-300 text-slate-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                            
                            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                <button type="button" onclick="confirmarRechazo()" 
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-base bg-red-600 hover:bg-red-700 text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Rechazar y Devolver
                                </button>
                                
                                <button type="button" onclick="confirmarAprobacion()" 
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-base bg-slate-800 hover:bg-slate-900 text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Aprobar y Publicar
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
                confirmButtonColor: '#dc2626',
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