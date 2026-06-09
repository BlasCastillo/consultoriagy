@if($evidencias->isEmpty())
    <div class="p-6 text-center text-slate-500 italic bg-slate-50 rounded-md border border-slate-200 shadow-inner">
        No hay evidencias registradas para este trimestre. Sube la primera evidencia utilizando el formulario superior.
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($evidencias as $evidencia)
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm flex flex-col relative overflow-hidden group">
                <div class="p-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        <i class="fa-solid fa-tag mr-1 text-slate-400"></i> {{ $evidencia->tipo }}
                    </span>
                    <button onclick="eliminarEvidencia({{ $evidencia->id }})" class="text-red-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100" title="Eliminar Evidencia">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
                <div class="p-4 flex-grow">
                    <div class="flex items-start">
                        <div class="mr-4 flex-shrink-0">
                            @if(Str::endsWith($evidencia->archivo_path, '.pdf'))
                                <div class="w-16 h-16 bg-red-50 flex items-center justify-center rounded border border-red-100">
                                    <i class="fa-solid fa-file-pdf text-4xl text-red-500"></i>
                                </div>
                            @else
                                <div class="w-16 h-16 bg-slate-200 rounded overflow-hidden border border-slate-300 shadow-sm">
                                    <img src="{{ Storage::url($evidencia->archivo_path) }}" alt="Evidencia" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm text-slate-700 mb-1 leading-tight break-words">{{ $evidencia->descripcion ?? 'Sin descripción proporcionada.' }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">Subido: {{ $evidencia->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-3 border-t border-slate-100 bg-slate-50 text-center">
                    <a href="{{ Storage::url($evidencia->archivo_path) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 tracking-wide uppercase transition">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Abrir Archivo
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
