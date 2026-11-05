<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Biblioteca de Gacetas Oficiales') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Panel de Búsqueda Avanzada --}}
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4 text-lg font-bold text-slate-800">
                        Búsqueda Avanzada
                    </h3>

                    <form action="{{ route('gacetas.publicadas') }}" method="GET">
                        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4">

                            {{-- Filtros Directos de Gaceta --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Año</label>
                                <input type="number" name="anio" value="{{ request('anio') }}"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm"
                                    placeholder="Ej: {{ date('Y') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Número</label>
                                <input type="text" name="numero" value="{{ request('numero') }}"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm"
                                    placeholder="N° de Gaceta">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tipo de Edición</label>
                                <select name="tipo"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                    <option value="">Todas</option>
                                    <option value="Ordinaria" {{ request('tipo') == 'Ordinaria' ? 'selected' : '' }}>Ordinaria</option>
                                    <option value="Extraordinaria" {{ request('tipo') == 'Extraordinaria' ? 'selected' : '' }}>Extraordinaria</option>
                                </select>
                            </div>

                            {{-- Filtros Relacionales (Solicitudes e Instituciones) --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Institución Emisora</label>
                                <select name="institucion_id"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                    <option value="">Todas las Instituciones</option>
                                    @foreach($institutions as $inst)
                                        <option value="{{ $inst->id }}" {{ request('institucion_id') == $inst->id ? 'selected' : '' }}>{{ $inst->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtros Relacionales Profundos (Actos) --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tipo de Acto</label>
                                <select name="tipo_acto"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
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
                                <label class="block text-sm font-medium text-slate-700">Palabras Clave (Sumario o Nro de Acto)</label>
                                <input type="text" name="keyword" value="{{ request('keyword') }}"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm"
                                    placeholder="Buscar contenido dentro de la Gaceta...">
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                            <a href="{{ route('gacetas.publicadas') }}" class="btn btn-secondary">
                                Limpiar Filtros
                            </a>
                            <button type="submit" class="btn btn-neutral">
                                Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @auth
                @if(auth()->user()->hasAnyRole(['Jefe de Digitalización', 'Super Admin', 'Súper Administrador']) || auth()->user()->roles->count() === 0)
                    <div class="flex justify-end">
                        <a href="{{ route('solicitudes.create') }}" class="btn btn-primary">
                            Registrar Solicitud
                        </a>
                    </div>
                @endif
            @endauth

            {{-- Tabla de Resultados Públicos --}}
            <div class="card">
                <div class="card-body">
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="table-corporate">
                            <thead>
                                <tr>
                                    <th>Número/Año</th>
                                    <th>Tipo</th>
                                    <th>Fecha de Publicación</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gacetas as $g)
                                    <tr>
                                        <td class="whitespace-nowrap">
                                            <div class="font-bold text-slate-900">{{ $g->numero }}</div>
                                            <div class="text-sm text-slate-500">{{ $g->anio }}</div>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            {{ $g->tipo }}
                                        </td>
                                        <td class="whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($g->fecha_publicacion)->format('d/m/Y') }}
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-2">
                                                {{-- Ver Detalles (Abre el sumario) --}}
                                                <x-action-button :href="route('gacetas.show', $g->id)"
                                                    action="Ver sumario de actos"
                                                    icon="fa-solid fa-eye"
                                                    tone="neutral" />

                                                {{-- Descargar --}}
                                                @if($g->pdf_final || $g->archivo_pdf_completo || $g->ruta_archivo)
                                                    <x-action-button :href="route('gacetas.descargar', $g->id)"
                                                        action="Descargar PDF"
                                                        icon="fa-solid fa-download"
                                                        tone="dark"
                                                        download />
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-500">
                                            No se encontraron gacetas que coincidan con su búsqueda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $gacetas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
