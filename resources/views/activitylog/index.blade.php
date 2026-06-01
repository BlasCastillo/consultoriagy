<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Bitácora de Actividades') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">

                    <p class="mb-6 text-slate-600">Registro de auditoría de los movimientos realizados en el sistema.</p>

                    <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                        <table class="min-w-full">
                            <thead class="bg-slate-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Fecha y Hora</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Usuario</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Acción</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Modelo</th>
                                    <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-sm text-slate-600 whitespace-nowrap">
                                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600 font-medium">
                                            {{ $activity->causer ? $activity->causer->name : 'Sistema / Automático' }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            @php
                                                $actionColor = 'bg-slate-200 text-slate-800';
                                                switch($activity->event) {
                                                    case 'created': $actionColor = 'bg-green-100 text-green-800 border-green-300'; break;
                                                    case 'updated': $actionColor = 'bg-blue-100 text-blue-800 border-blue-300'; break;
                                                    case 'deleted': $actionColor = 'bg-red-100 text-red-800 border-red-300'; break;
                                                    case 'restored': $actionColor = 'bg-teal-100 text-teal-800 border-teal-300'; break;
                                                }
                                            @endphp
                                            <span class="px-2 py-1 rounded text-xs border {{ $actionColor }} uppercase font-bold tracking-wide">
                                                {{ $activity->event ?? 'Acción' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600">
                                            @if($activity->subject_type)
                                                <span class="font-medium">{{ class_basename($activity->subject_type) }}</span> <span class="text-slate-500">#{{ $activity->subject_id }}</span>
                                            @else
                                                <span class="text-slate-400 italic">Genérico</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-sm text-slate-600 text-center" x-data="{ open: false }">
                                            <button @click="open = true" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-500 hover:bg-slate-600 text-white">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Ver Detalles
                                            </button>
                                            
                                            <!-- Modal Alpine -->
                                            <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 bg-slate-900/50 backdrop-blur-sm">
                                                <div @click.away="open = false" class="bg-white rounded-lg shadow-xl w-full max-w-3xl overflow-hidden text-left">
                                                    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                                                        <h3 class="text-lg font-bold text-slate-800">Detalles de Auditoría</h3>
                                                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                                                        <div class="mb-4 bg-slate-50 p-4 rounded border border-slate-200">
                                                            <p class="text-sm text-slate-700 mb-1"><strong>Ejecutor:</strong> {{ $activity->causer ? $activity->causer->name . ' (' . $activity->causer->email . ')' : 'Sistema' }}</p>
                                                            <p class="text-sm text-slate-700 mb-1"><strong>Descripción:</strong> {{ $activity->description }}</p>
                                                            <p class="text-sm text-slate-700 mb-1"><strong>IP:</strong> {{ $activity->properties['ip'] ?? 'No registrada' }}</p>
                                                            <p class="text-sm text-slate-700"><strong>User Agent:</strong> {{ $activity->properties['user_agent'] ?? 'No registrado' }}</p>
                                                        </div>
                                                        <h4 class="font-semibold text-slate-700 mb-3 flex items-center">
                                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                                            Atributos Modificados (JSON)
                                                        </h4>
                                                        <div class="bg-[#0f172a] rounded-md p-4 overflow-x-auto shadow-inner">
                                                            <pre class="text-sm text-green-400 font-mono">@php
                                                                $props = $activity->properties->except(['ip', 'user_agent']);
                                                                if($props->isEmpty()){
                                                                    echo "No se registraron cambios en atributos.\n";
                                                                } else {
                                                                    echo json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                                                }
                                                            @endphp</pre>
                                                        </div>
                                                    </div>
                                                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                                                        <button @click="open = false" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-200 hover:bg-slate-300 text-slate-700">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                                            Cerrar Detalle
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-500 font-medium">No hay registros de actividad todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $activities->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
