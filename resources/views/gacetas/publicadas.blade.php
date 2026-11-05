<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Biblioteca de Gacetas Oficiales') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Panel de Búsqueda Avanzada (Legacy UI) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Búsqueda Avanzada
                    </h3>
                    
                    <form action="{{ route('gacetas.publicadas') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                            
                            {{-- Filtros Directos de Gaceta --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Año</label>
                                <input type="number" name="anio" value="{{ request('anio') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ej: {{ date('Y') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Número</label>
                                <input type="text" name="numero" value="{{ request('numero') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="N° de Gaceta">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Edición</label>
                                <select name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Todas</option>
                                    <option value="Ordinaria" {{ request('tipo') == 'Ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                                    <option value="Extraordinaria" {{ request('tipo') == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                                </select>
                            </div>
                            
                            {{-- Filtros Relacionales (Solicitudes e Instituciones) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Institución Emisora</label>
                                <select name="institucion_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Todas las Instituciones</option>
                                    @foreach($institutions as $inst)
                                        <option value="{{ $inst->id }}" {{ request('institucion_id') == $inst->id ? 'selected' : '' }}>{{ $inst->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtros Relacionales Profundos (Actos) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Acto</label>
                                <select name="tipo_acto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Todos los Actos</option>
                                    <option value="Decreto" {{ request('tipo_acto') == 'Decreto' ? 'selected' : '' }}>Decreto</option>
                                    <option value="Resolución" {{ request('tipo_acto') == 'Resolución' ? 'selected' : '' }}>Resolución</option>
                                    <option value="Providencia Administrativa" {{ request('tipo_acto') == 'Providencia Administrativa' ? 'selected' : '' }}>Providencia Administrativa</option>
                                    <option value="Acuerdo" {{ request('tipo_acto') == 'Acuerdo' ? 'selected' : '' }}>Acuerdo</option>
                                    <option value="Ley" {{ request('tipo_acto') == 'Ley' ? 'selected' : '' }}>Ley</option>
                                    <option value="Sentencia" {{ request('tipo_acto') == 'Sentencia' ? 'selected' : '' }}>Sentencia</option>
                                    <option value="Otro" {{ request('tipo_acto') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                            </div>
                            <div class="md:col-span-2 lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Palabras Clave (Sumario o Nro de Acto)</label>
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Buscar contenido dentro de la Gaceta...">
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-4 border-t pt-4">
                            <a href="{{ route('gacetas.publicadas') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Limpiar Filtros
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 focus:bg-slate-700 active:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @auth
                @if(auth()->user()->hasAnyRole(['Jefe de Digitalización', 'Super Admin', 'Súper Administrador']) || auth()->user()->roles->count() === 0)
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('solicitudes.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Registrar Solicitud
                        </a>
                    </div>
                @endif
            @endauth

            {{-- Tabla de Resultados Públicos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número/Año</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Publicación</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($gacetas as $g)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $g->numero }}</div>
                                        <div class="text-sm text-gray-500">{{ $g->anio }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $g->tipo }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ \Carbon\Carbon::parse($g->fecha_publicacion)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center items-center space-x-2">
                                            {{-- Botón Ver Detalles (Abre el sumario) --}}
                                            <a href="{{ route('gacetas.show', $g->id) }}" class="bg-slate-500 hover:bg-slate-600 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Ver Sumario de Actos">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        
                                            {{-- Botón Descargar --}}
                                            @if($g->pdf_final || $g->archivo_pdf_completo || $g->ruta_archivo)
                                                <a href="{{ route('gacetas.descargar', $g->id) }}" download class="bg-slate-800 hover:bg-slate-900 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Descargar PDF">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        No se encontraron gacetas que coincidan con su búsqueda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-6">
                        {{ $gacetas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
