<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Gestión de Instituciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-4 flex justify-between items-center">
                        <div>
                            @if(request('trashed'))
                                <a href="{{ route('institutions.index') }}" class="text-slate-600 hover:text-slate-900 underline text-sm flex items-center">
                                    <i class="fa-solid fa-arrow-left mr-1"></i>
                                    Volver a Activas
                                </a>
                            @else
                                <a href="{{ route('institutions.index', ['trashed' => 'true']) }}" class="text-slate-600 hover:text-slate-900 underline text-sm flex items-center">
                                    <i class="fa-solid fa-trash mr-1"></i>
                                    Ver Eliminadas
                                </a>
                            @endif
                        </div>
                        <a href="{{ route('institutions.create') }}" class="bg-[#1e293b] hover:bg-[#0f172a] text-white font-bold py-2 px-4 rounded transition duration-150 shadow-sm" style="background-color: #1e293b; color: white;">
                            Registrar Nueva Institución
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-slate-300">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-300">
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">Nombre</th>
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">RIF</th>
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700">Tipo</th>
                                    <th class="py-3 px-4 text-center font-semibold text-slate-700">Estatus</th>
                                    <th class="py-3 px-4 text-left font-semibold text-slate-700" width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($institutions as $institution)
                                    <tr class="border-b border-slate-200 hover:bg-slate-50">
                                        <td class="py-3 px-4">{{ $institution->name }}</td>
                                        <td class="py-3 px-4">{{ $institution->rif ?? 'N/A' }}</td>
                                        <td class="py-3 px-4">
                                            @if($institution->type === 'consultoria')
                                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Consultoría Jurídica</span>
                                            @else
                                                <span class="bg-slate-200 text-slate-800 text-xs px-2 py-1 rounded">Ente Adscrito</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($institution->trashed())
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded border border-red-200 uppercase tracking-widest">
                                                    Eliminado
                                                </span>
                                            @elseif($institution->status === 'active')
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded border border-green-200">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded border border-red-200">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex space-x-3 items-center">
                                                @if($institution->trashed())
                                                    <form action="{{ route('institutions.restore', $institution) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas restaurar?');" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-green-600 hover:text-green-900 transition-colors" title="Restaurar">
                                                            <i class="fa-solid fa-rotate-left"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('institutions.edit', $institution) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="Editar">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <form action="{{ route('institutions.destroy', $institution) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" title="Eliminar">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-slate-500">No hay instituciones registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $institutions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
