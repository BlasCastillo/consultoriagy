<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registrar Ficha de Actividad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('fichas.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Meta Trimestral Asociada</label>
                            <select name="meta_trimestral_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                <option value="">Seleccione una meta...</option>
                                @foreach($metas as $meta)
                                    <option value="{{ $meta->id }}">
                                        Trimestre {{ $meta->trimestre }} | {{ $meta->actividadPoa->poa->jefatura->nombre ?? 'General' }} - {{ $meta->actividadPoa->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Fecha de la Actividad</label>
                                <input type="date" name="fecha" required value="{{ old('fecha', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Título de la Actividad</label>
                                <input type="text" name="titulo_actividad" required value="{{ old('titulo_actividad') }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Desarrollada por</label>
                                <input type="text" name="desarrollada_por" required value="{{ old('desarrollada_por') }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Cantidad de Beneficiados</label>
                                <input type="number" name="cantidad_beneficiados" required min="0" value="{{ old('cantidad_beneficiados', 0) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Instituciones Involucradas</label>
                            <textarea name="instituciones_involucradas" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('instituciones_involucradas') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Objetivo Logrado</label>
                            <textarea name="objetivo_logrado" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('objetivo_logrado') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Imágenes (Evidencia)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-md bg-slate-50">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-slate-600 justify-center">
                                        <label for="imagenes" class="relative cursor-pointer bg-white rounded-md font-medium text-slate-600 hover:text-slate-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-slate-500 px-1">
                                            <span>Subir archivos</span>
                                            <input id="imagenes" name="imagenes[]" type="file" multiple accept="image/*" class="sr-only">
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG, GIF hasta 5MB</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-slate-200">
                            <a href="{{ route('poa.dashboard') }}" class="mr-4 text-sm font-medium text-slate-600 hover:text-slate-500">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                                Guardar Ficha
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
