<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Crear Nuevo Rol') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nombre del Rol <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-slate-800 mb-4 border-b pb-2">Asignar Permisos</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-slate-200">
                                    <thead class="bg-slate-50 border-b border-slate-200">
                                        <tr>
                                            <th class="py-3 px-4 text-left font-semibold text-slate-700">Modelo</th>
                                            <th class="py-3 px-4 text-center font-semibold text-slate-700">Crear</th>
                                            <th class="py-3 px-4 text-center font-semibold text-slate-700">Leer (Ver)</th>
                                            <th class="py-3 px-4 text-center font-semibold text-slate-700">Actualizar (Editar)</th>
                                            <th class="py-3 px-4 text-center font-semibold text-slate-700">Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($matrix as $model => $permissions)
                                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                                <td class="py-3 px-4 font-medium text-slate-800">{{ $model }}</td>
                                                
                                                @php
                                                    // Map the order: create, read, update, delete
                                                    $orderedPerms = [
                                                        "create {$model}",
                                                        "read {$model}",
                                                        "update {$model}",
                                                        "delete {$model}"
                                                    ];
                                                @endphp

                                                @foreach($orderedPerms as $perm)
                                                    <td class="py-3 px-4 text-center">
                                                        <label class="inline-flex items-center">
                                                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-900">
                                                        </label>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-6 border-t pt-4">
                            <a href="{{ route('roles.index') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-medium py-2 px-4 border border-slate-300 rounded shadow-sm">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-4 rounded shadow-sm">
                                Guardar Rol
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
