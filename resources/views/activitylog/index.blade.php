<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Bitácora de Actividades') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">

                    <p class="mb-6 text-slate-600">Registro de auditoría de los movimientos realizados en el sistema.</p>

                    <!-- Panel de Filtros -->
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 mb-6">
                        <form method="GET" action="{{ route('bitacora.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha Fin</label>
                                <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Usuario</label>
                                <select name="usuario_id" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm">
                                    <option value="">Todos</option>
                                    @foreach($usuarios ?? [] as $u)
                                        <option value="{{ $u->id }}" {{ request('usuario_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Módulo / Modelo</label>
                                <input type="text" name="modulo" value="{{ request('modulo') }}" placeholder="Ej: Gaceta, Ficha" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Evento</label>
                                <select name="evento" class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-sm">
                                    <option value="">Todos</option>
                                    <option value="created" {{ request('evento') == 'created' ? 'selected' : '' }}>Creado</option>
                                    <option value="updated" {{ request('evento') == 'updated' ? 'selected' : '' }}>Actualizado</option>
                                    <option value="deleted" {{ request('evento') == 'deleted' ? 'selected' : '' }}>Eliminado</option>
                                </select>
                            </div>
                            <div class="md:col-span-5 flex justify-end gap-2 mt-2">
                                <a href="{{ route('bitacora.index') }}" class="btn btn-secondary">Limpiar</a>
                                <button type="submit" class="btn btn-neutral">Filtrar</button>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Modelo</th>
                                    <th class="text-center">Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td class="whitespace-nowrap text-slate-600">
                                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="font-medium text-slate-600">
                                            {{ $activity->causer ? $activity->causer->name : 'Sistema / Automático' }}
                                        </td>
                                        <td class="text-slate-600">
                                            @php
                                                $actionBadge = 'badge-neutral';
                                                switch($activity->event) {
                                                    case 'created': $actionBadge = 'badge-success'; break;
                                                    case 'updated': $actionBadge = 'badge-info'; break;
                                                    case 'deleted': $actionBadge = 'badge-danger'; break;
                                                    case 'restored': $actionBadge = 'badge-info'; break;
                                                }
                                            @endphp
                                            <span class="badge {{ $actionBadge }} uppercase tracking-wide">
                                                {{ $activity->event ?? 'Acción' }}
                                            </span>
                                        </td>
                                        <td class="text-slate-600">
                                            @if($activity->subject_type)
                                                <span class="font-medium">{{ class_basename($activity->subject_type) }}</span> <span class="text-slate-500">#{{ $activity->subject_id }}</span>
                                            @else
                                                <span class="italic text-slate-400">Genérico</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-slate-600" x-data="{ open: false }">
                                            <x-action-button type="button"
                                                action="Ver detalles de auditoría"
                                                icon="fa-solid fa-eye"
                                                tone="view"
                                                size="sm"
                                                x-on:click="open = true" />
                                            
                                            <!-- Modal Alpine -->
                                            <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 bg-slate-900/50 backdrop-blur-sm">
                                                <div @click.away="open = false" class="card-xl w-full max-w-3xl overflow-hidden text-left">
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
                                                        <button @click="open = false" class="btn btn-secondary">
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
