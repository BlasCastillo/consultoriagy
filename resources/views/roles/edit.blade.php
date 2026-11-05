<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Editar Rol:') }} <span class="font-normal">{{ $role->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
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

                    <form action="{{ route('roles.update', $role) }}" method="POST" autocomplete="off">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nombre del Rol <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" value="{{ old('name', $role->name) }}" required>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-200">Matriz de Permisos</h3>
                            <div class="overflow-x-auto rounded-lg border border-slate-200">
                                <table class="table-corporate">
                                    <thead>
                                        <tr>
                                            <th class="py-3 px-4 text-left text-sm font-semibold uppercase tracking-wider">Módulo / Modelo</th>
                                            <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Crear</th>
                                            <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Leer (Ver)</th>
                                            <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Actualizar</th>
                                            <th class="py-3 px-4 text-center text-sm font-semibold uppercase tracking-wider">Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($matrix as $model => $permissions)
                                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                <td class="py-3 px-4 font-bold text-sm text-slate-800 capitalize">{{ $model }}</td>
                                                
                                                @php
                                                    $orderedPerms = [
                                                        "create {$model}",
                                                        "read {$model}",
                                                        "update {$model}",
                                                        "delete {$model}"
                                                    ];
                                                @endphp

                                                @foreach($orderedPerms as $perm)
                                                    <td class="py-3 px-4 text-center">
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" 
                                                                {{ in_array($perm, $rolePermissions) ? 'checked' : '' }}
                                                                class="rounded border-slate-300 text-slate-800 shadow-sm focus:ring-slate-800 w-5 h-5 cursor-pointer transition-colors">
                                                        </label>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-neutral">
                                Actualizar Rol
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>