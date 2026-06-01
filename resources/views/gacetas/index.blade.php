<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registro y Control de Gacetas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SweetAlert2 Toasts --}}
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
                            icon: 'success', title: "{!! addslashes(session('success')) !!}"
                        });
                    });
                </script>
            @endif
            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
                            icon: 'error', title: "{!! addslashes(session('error')) !!}"
                        });
                    });
                </script>
            @endif

            {{-- Panel de Búsqueda Avanzada --}}
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Búsqueda Avanzada</h3>
                <form method="GET" action="{{ route('gacetas.index') }}" class="space-y-4" autocomplete="off">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Año</label>
                            <input type="number" name="anio" value="{{ request('anio') }}"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Número</label>
                            <input type="number" name="numero" value="{{ request('numero') }}"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tipo</label>
                            <select name="tipo"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                                <option value="">Todos</option>
                                <option value="Ordinaria" {{ request('tipo') == 'Ordinaria' ? 'selected' : '' }}>Ordinaria
                                </option>
                                <option value="Extraordinaria" {{ request('tipo') == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Institución</label>
                            <select name="institucion_id"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                                <option value="">Todas</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->id }}" {{ request('institucion_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tipo de Acto</label>
                            <select name="tipo_acto"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
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
                            <label class="block text-sm font-medium text-slate-700">Palabras Clave (Sumario)</label>
                            <input type="text" name="keyword" value="{{ request('keyword') }}"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        {{-- Botón Limpiar --}}
                        <a href="{{ route('gacetas.index') }}"
                            class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base">
                            Limpiar
                        </a>
                        {{-- Botón Buscar --}}
                        <button type="submit"
                            class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Panel de Resultados --}}
            <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-800">Resultados</h3>
                    
                    {{-- Condición corregida: Ya no incluye a 'Institucion', 'Institucional' --}}
                    @if(auth()->user()->hasAnyRole(['Jefe de Digitalización', 'Super Admin', 'Super Administrador']) || auth()->user()->roles->count() === 0)
                        {{-- Botón Crear/Nuevo --}}
                        <a href="{{ route('gacetas.create') }}"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Registrar Solicitud
                        </a>
                    @endif
                </div>

                {{-- Tabla estandarizada --}}
                <div class="overflow-x-auto bg-white shadow-md sm:rounded-lg border border-slate-200">
                    <table class="min-w-full">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Número/Año</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Tipo</th>
                                @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Estado</th>
                                @endif
                                <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Emisión</th>
                                @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                    <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">SLA / Retraso</th>
                                @endif
                                <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider" width="160">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gacetas as $g)
                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 text-sm font-medium text-slate-800 align-middle">
                                        {{ $g->numero }} / {{ $g->anio }}
                                        @if($g->corregida_de_id)
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200"
                                                title="Art. 5">Corrección</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-slate-600 align-middle">{{ $g->tipo }}</td>
                                    @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                        <td class="py-3 px-4 text-sm align-middle">
                                            <span
                                                class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full border 
                                                    {{ $g->estado === 'Publicada' ? 'bg-green-100 text-green-800 border-green-200' : ($g->estado === 'Reservada' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-blue-100 text-blue-800 border-blue-200') }}">
                                                {{ str_replace('_', ' ', $g->estado) }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="py-3 px-4 text-sm text-slate-600 align-middle">
                                        {{ $g->fecha_emision?->format('d/m/Y') ?? 'N/A' }}</td>
                                    @if(!auth()->user()->hasAnyRole(['Institucion', 'Institucional']))
                                        <td class="py-3 px-4 text-sm align-middle">
                                            @if($g->dias_retraso > 0)
                                                <span class="text-red-600 font-bold flex items-center bg-red-50 px-2 py-1 rounded-md border border-red-100 w-max">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $g->dias_retraso }} días
                                                </span>
                                            @else
                                                <span class="text-emerald-600 font-semibold">En tiempo</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="py-3 px-4 text-sm font-medium align-middle">
                                        <div class="flex justify-center items-center space-x-2">
                                            {{-- Botón Ver --}}
                                            <a href="{{ route('gacetas.show', $g->id) }}"
                                                class="bg-slate-500 hover:bg-slate-600 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Ver detalles">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>

                                            @if($g->estado === 'Publicada' && $g->ruta_archivo)
                                                {{-- Botón Descargar --}}
                                                <a href="{{ asset('gacetas_pdf/' . $g->ruta_archivo) }}" target="_blank" download
                                                    class="bg-slate-800 hover:bg-slate-900 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Descargar PDF">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                </a>
                                            @endif

                                            @if(auth()->user()->hasAnyRole(['Jefe de Digitalización', 'Super Admin', 'Super Administrador']) || auth()->user()->roles->count() === 0)
                                                @if($g->estado === 'Solicitada')
                                                    {{-- Botón Validar --}}
                                                    <a href="{{ route('gacetas.checklist', $g->id) }}"
                                                        class="bg-slate-800 hover:bg-slate-900 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Validar Checklist">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                                    </a>
                                                @elseif($g->estado === 'Reservada')
                                                    {{-- Botón Asignar --}}
                                                    <a href="{{ route('gacetas.asignar', $g->id) }}"
                                                        class="bg-amber-500 hover:bg-amber-600 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Asignar Gaceta">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </a>
                                                @elseif($g->estado === 'Por Aprobar')
                                                    {{-- Botón Revisar --}}
                                                    <a href="{{ route('gacetas.aprobar', $g->id) }}"
                                                        class="bg-emerald-600 hover:bg-emerald-700 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Revisar y Aprobar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </a>
                                                @endif
                                            @endif
                                            
                                            @if(auth()->user()->hasAnyRole(['Digitalizador', 'Super Admin', 'Super Administrador']) || auth()->user()->roles->count() === 0)
                                                @if($g->estado === 'En Escaneo')
                                                    {{-- Botón Subir PDF --}}
                                                    <a href="{{ route('gacetas.upload_pdf', $g->id) }}"
                                                        class="bg-slate-800 hover:bg-slate-900 text-white p-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center" title="Subir archivo PDF">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->hasAnyRole(['Institucion', 'Institucional']) ? 4 : 6 }}"
                                        class="py-6 text-center text-sm text-slate-500">No se encontraron gacetas que coincidan con los criterios de búsqueda.</td>
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