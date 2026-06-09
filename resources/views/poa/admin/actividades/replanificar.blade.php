<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Replanificación de Metas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6 pb-4 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Actividad: {{ $actividad->descripcion }}</h3>
                        <p class="text-sm text-slate-500"><strong>Indicador:</strong> {{ $actividad->indicador_nombre }}</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-slate-700 mb-3">Saldos Disponibles por Trimestre</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($actividad->metasTrimestrales as $meta)
                                @php
                                    $saldo = $meta->meta_actual - $meta->ejecutada;
                                @endphp
                                <div class="p-4 rounded-lg border {{ $saldo > 0 ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200' }} text-center shadow-sm">
                                    <p class="text-xs font-bold uppercase text-slate-500 mb-1">Trimestre {{ $meta->trimestre }}</p>
                                    <p class="text-2xl font-bold {{ $saldo > 0 ? 'text-blue-700' : 'text-slate-400' }}">{{ $saldo }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Disp. ({{ $meta->ejecutada }} ejec. de {{ $meta->meta_actual }})</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ route('actividades.storeReplanificacion', $actividad->id) }}" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Trimestre de Origen (Resta)</label>
                                <select name="trimestre_origen" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="">Seleccione...</option>
                                    <option value="1">Trimestre 1</option>
                                    <option value="2">Trimestre 2</option>
                                    <option value="3">Trimestre 3</option>
                                    <option value="4">Trimestre 4</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Trimestre Destino (Suma)</label>
                                <select name="trimestre_destino" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="">Seleccione...</option>
                                    <option value="1">Trimestre 1</option>
                                    <option value="2">Trimestre 2</option>
                                    <option value="3">Trimestre 3</option>
                                    <option value="4">Trimestre 4</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Cantidad a Mover</label>
                                <input type="number" name="cantidad_movida" required min="1" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 text-center font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Justificación Técnica del Traslado</label>
                            <textarea name="justificacion" required rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('justificacion') }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">Debe explicar claramente por qué no se ejecutarán estas metas en su trimestre original.</p>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-slate-200">
                            <a href="{{ route('poa.show', $actividad->poa_id) }}" class="mr-4 text-sm font-medium text-slate-600 hover:text-slate-500">Cancelar</a>
                            <button type="submit" class="px-6 py-2.5 bg-blue-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-sm">
                                Confirmar Replanificación
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
