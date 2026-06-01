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

                    <form action="{{ route('roles.store') }}" method="POST" autocomplete="off">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nombre del Rol <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm" value="{{ old('name') }}" required autofocus placeholder="Ej: Administrador, Editor, Lector...">
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-200">Matriz de Permisos</h3>
                            <div class="overflow-x-auto rounded-lg border border-slate-200">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-slate-800 text-white border-b border-slate-300">
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
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="rounded border-slate-300 text-slate-800 shadow-sm focus:ring-slate-800 w-5 h-5 cursor-pointer transition-colors">
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
                            <a href="{{ route('roles.index') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-200 hover:bg-slate-300 text-slate-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                            <button type="submit" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-800 hover:bg-slate-900 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Guardar Rol
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>