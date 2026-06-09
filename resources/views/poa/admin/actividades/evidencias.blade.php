<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Gestión de Evidencias') }}
            </h2>
            <a href="{{ route('poa.show', $actividad->poa_id) }}" class="px-4 py-2 bg-slate-200 border border-transparent rounded-md font-semibold text-xs text-slate-800 uppercase tracking-widest hover:bg-slate-300 transition">
                Volver al POA
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Tarjeta Principal de la Actividad -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Actividad Programada</h3>
                    <p class="text-slate-600 mb-4">{{ $actividad->descripcion }}</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-slate-50 p-4 rounded border border-slate-100">
                        <div>
                            <strong class="text-slate-700 block">Partida Presupuestaria:</strong>
                            <span class="text-slate-600">{{ $actividad->partida_presupuestaria ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <strong class="text-slate-700 block">Indicador:</strong>
                            <span class="text-slate-600">{{ $actividad->indicador_nombre ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grilla de los 4 Trimestres -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @for($i = 1; $i <= 4; $i++)
                    @php
                        $meta = $actividad->metasTrimestrales->where('trimestre', $i)->first();
                    @endphp

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 flex flex-col">
                        <div class="bg-slate-100 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                            <h4 class="font-bold text-slate-800">Trimestre {{ $i }}</h4>
                            @if($meta)
                                @php
                                    $programada = $meta->meta_actual;
                                    $ejecutada = $meta->ejecutada;
                                    $cumplida = $programada > 0 && $ejecutada >= $programada;
                                @endphp
                                <div class="text-sm font-bold {{ $cumplida ? 'text-green-600' : 'text-slate-600' }}">
                                    Ejecutadas: {{ $ejecutada }} / {{ $programada }}
                                </div>
                            @else
                                <span class="text-xs font-semibold text-slate-400 bg-slate-200 px-2 py-1 rounded-full">No programado</span>
                            @endif
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            @if(!$meta)
                                <p class="text-slate-500 text-sm italic text-center py-4">No hay metas para este trimestre.</p>
                            @else
                                <!-- Formulario de Subida -->
                                <div class="mb-6 bg-slate-50 p-4 rounded border border-slate-200">
                                    <form action="{{ route('evidencias.store', $meta->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700">Tipo</label>
                                                <select name="tipo" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 text-sm">
                                                    <option value="Informe">Informe</option>
                                                    <option value="Ficha">Ficha</option>
                                                    <option value="Foto">Foto</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700">Archivo</label>
                                                <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-700">Descripción (Opcional)</label>
                                            <textarea name="descripcion" rows="1" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 text-sm placeholder-slate-400" placeholder="Breve detalle del archivo..."></textarea>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs font-bold uppercase tracking-widest hover:bg-blue-700 transition">
                                                Subir Evidencia
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Listado de Evidencias -->
                                <h5 class="text-sm font-bold text-slate-700 mb-3 border-b pb-2">Archivos Subidos</h5>
                                
                                @if($meta->evidencias->isEmpty())
                                    <p class="text-slate-500 text-sm italic text-center py-2">No hay evidencias subidas.</p>
                                @else
                                    <ul class="space-y-3">
                                        @foreach($meta->evidencias as $evidencia)
                                            <li class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-md hover:bg-slate-50 transition">
                                                <div class="flex items-start space-x-3 overflow-hidden">
                                                    <div class="mt-1">
                                                        @if(in_array(strtolower(pathinfo($evidencia->archivo_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']))
                                                            <i class="fa-solid fa-image text-blue-500 text-xl"></i>
                                                        @else
                                                            <i class="fa-solid fa-file-pdf text-red-500 text-xl"></i>
                                                        @endif
                                                    </div>
                                                    <div class="truncate">
                                                        <a href="{{ Storage::url($evidencia->archivo_path) }}" target="_blank" class="text-sm font-semibold text-blue-600 hover:underline truncate block">
                                                            {{ $evidencia->tipo }}
                                                        </a>
                                                        @if($evidencia->descripcion)
                                                            <p class="text-xs text-slate-500 truncate">{{ $evidencia->descripcion }}</p>
                                                        @endif
                                                        <span class="text-[10px] text-slate-400">{{ $evidencia->created_at->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4 flex-shrink-0">
                                                    <form action="{{ route('evidencias.destroy', $evidencia->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta evidencia permanentemente?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="Eliminar archivo">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>
                    </div>
                @endfor
            </div>

        </div>
    </div>
</x-app-layout>
