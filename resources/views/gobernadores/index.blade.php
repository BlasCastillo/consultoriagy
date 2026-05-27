<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gobernadores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Listado de Gobernadores</h3>
                        <a href="{{ route('gobernadores.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fa-solid fa-plus mr-2"></i> Nuevo Gobernador
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-3 px-6 text-left border-b">ID</th>
                                    <th class="py-3 px-6 text-left border-b">Título</th>
                                    <th class="py-3 px-6 text-left border-b">Nombres</th>
                                    <th class="py-3 px-6 text-left border-b">Apellidos</th>
                                    <th class="py-3 px-6 text-center border-b">Estado</th>
                                    <th class="py-3 px-6 text-center border-b">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                @foreach($gobernadores as $gobernador)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6 text-left">{{ $gobernador->id }}</td>
                                    <td class="py-3 px-6 text-left">{{ $gobernador->titulo->abreviatura ?? 'N/A' }}</td>
                                    <td class="py-3 px-6 text-left font-semibold">{{ $gobernador->nombres }}</td>
                                    <td class="py-3 px-6 text-left font-semibold">{{ $gobernador->apellidos }}</td>
                                    <td class="py-3 px-6 text-center">
                                        @if($gobernador->estado)
                                            <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs font-bold">Activo</span>
                                        @else
                                            <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs font-bold">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center space-x-2">
                                            <a href="{{ route('gobernadores.edit', $gobernador->id) }}" class="w-8 h-8 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center hover:bg-blue-200 hover:scale-110 transition-transform" title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
