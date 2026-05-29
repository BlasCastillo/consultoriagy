<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registro y Control de Gacetas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-6">
                <h3 class="text-lg font-medium text-slate-800 mb-4 border-b pb-2">Búsqueda Avanzada</h3>
                <form method="GET" action="{{ route('gacetas.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Año</label>
                            <input type="number" name="anio" value="{{ request('anio') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Número</label>
                            <input type="number" name="numero" value="{{ request('numero') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="tipo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                <option value="">Todos</option>
                                <option value="Ordinaria" {{ request('tipo') == 'Ordinaria' ? 'selected' : '' }}>Ordinaria
                                </option>
                                <option value="Extraordinaria" {{ request('tipo') == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Institución</label>
                            <select name="institucion_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                <option value="">Todas</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->id }}" {{ request('institucion_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de Acto</label>
                            <select name="tipo_acto"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                                <option value="">Todos</option>
                                <option value="Ley" {{ request('tipo_acto') == 'Ley' ? 'selected' : '' }}>Ley</option>
                                <option value="Decreto" {{ request('tipo_acto') == 'Decreto' ? 'selected' : '' }}>Decreto
                                </option>
                                <option value="Resolución" {{ request('tipo_acto') == 'Resolución' ? 'selected' : '' }}>
                                    Resolución</option>
                                <option value="Acuerdo" {{ request('tipo_acto') == 'Acuerdo' ? 'selected' : '' }}>Acuerdo
                                </option>
                                <option value="Providencia" {{ request('tipo_acto') == 'Providencia' ? 'selected' : '' }}>
                                    Providencia</option>
                                <option value="Aviso Oficial" {{ request('tipo_acto') == 'Aviso Oficial' ? 'selected' : '' }}>Aviso Oficial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Palabras Clave (Sumario)</label>
                            <input type="text" name="keyword" value="{{ request('keyword') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        <a href="{{ route('gacetas.index') }}"
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-bold rounded-md text-gray-700 bg-white hover:bg-gray-100 transition-colors">Limpiar</a>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-md text-sm font-bold rounded-md text-white bg-blue-900 hover:bg-blue-800 transition-colors">Buscar</button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-slate-800">Resultados</h3>
                    @if(auth()->user()->hasAnyRole(['Institucion', 'Institucional', 'Jefe de Digitalización', 'Super Admin', 'Super Administrador']) || auth()->user()->roles->count() === 0)
                        <a href="{{ route('gacetas.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest shadow-md hover:bg-blue-800 transition-colors">
                            Registrar Solicitud
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Número/Año</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo</th>
                                @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado</th>
                                @endif
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Emisión</th>
                                @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        SLA / Retraso</th>
                                @endif
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($gacetas as $g)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $g->numero }} / {{ $g->anio }}
                                        @if($g->corregida_de_id)
                                            <span
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                                title="Art. 5">Corrección</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $g->tipo }}</td>
                                    @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $g->estado === 'Publicada' ? 'bg-green-100 text-green-800' : ($g->estado === 'Reservada' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ str_replace('_', ' ', $g->estado) }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $g->fecha_emision?->format('d/m/Y') ?? 'N/A' }}</td>
                                    @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($g->dias_retraso > 0)
                                                <span class="text-red-600 font-bold flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Retraso: {{ $g->dias_retraso }} días
                                                </span>
                                            @else
                                                <span class="text-green-600">En tiempo</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('gacetas.show', $g->id) }}"
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold uppercase rounded shadow-sm hover:shadow transition-colors">
                                            <i class="fa-regular fa-eye mr-1"></i> Ver
                                        </a>

                                        @if($g->estado === 'Publicada' && $g->ruta_archivo)
                                            <a href="{{ asset('gacetas_pdf/' . $g->ruta_archivo) }}" target="_blank" download
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold uppercase rounded shadow-md hover:shadow-lg transition-colors">
                                                <i class="fa-solid fa-download mr-1"></i> Descargar
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasAnyRole(['Jefe de Digitalización', 'Super Admin', 'Super Administrador']) || auth()->user()->roles->count() === 0)
                                            @if($g->estado === 'Solicitada')
                                                <a href="{{ route('gacetas.checklist', $g->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase rounded shadow-md hover:shadow-lg transition-colors">
                                                    <i class="fa-solid fa-list-check mr-1"></i> Validar
                                                </a>
                                            @elseif($g->estado === 'Reservada')
                                                <a href="{{ route('gacetas.asignar', $g->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-bold uppercase rounded shadow-md hover:shadow-lg transition-colors">
                                                    <i class="fa-solid fa-user-check mr-1"></i> Asignar
                                                </a>
                                            @elseif($g->estado === 'Por Aprobar')
                                                <a href="{{ route('gacetas.aprobar', $g->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase rounded shadow-md hover:shadow-lg transition-colors">
                                                    <i class="fa-solid fa-check-double mr-1"></i> Revisar
                                                </a>
                                            @endif
                                        @endif
                                        
                                        @if(auth()->user()->hasAnyRole(['Digitalizador', 'Super Admin', 'Super Administrador']) || auth()->user()->roles->count() === 0)
                                            @if($g->estado === 'En Escaneo')
                                                <a href="{{ route('gacetas.upload_pdf', $g->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase rounded shadow-md hover:shadow-lg transition-colors">
                                                    <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Subir PDF
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->hasAnyRole(['Institucion', 'Institucional']) ? 4 : 6 }}"
                                        class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron gacetas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $gacetas->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>