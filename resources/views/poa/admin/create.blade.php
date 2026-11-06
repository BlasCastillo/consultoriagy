<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Crear Nuevo POA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
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

                    <form method="POST" action="{{ route('poa.store') }}" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Año de Planificación</label>
                                <input type="number" name="anio" min="2020" max="2099" required value="{{ old('anio', date('Y')) }}" class="val-numerico mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Estado Inicial</label>
                                <select name="estado" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="Borrador" {{ old('estado') == 'Borrador' ? 'selected' : '' }}>Borrador</option>
                                    <option value="Aprobado" {{ old('estado') == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="Ejecucion" {{ old('estado') == 'Ejecucion' ? 'selected' : '' }}>En Ejecución</option>
                                    <option value="Cerrado" {{ old('estado') == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-md">
                            <label class="block text-sm font-medium text-slate-700">Jefatura Asignada</label>
                            <select name="jefatura_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                <option value="">Seleccione una jefatura...</option>
                                @foreach($jefaturas as $jefatura)
                                    <option value="{{ $jefatura->id }}" {{ old('jefatura_id') == $jefatura->id ? 'selected' : '' }}>{{ $jefatura->nombre }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Obligatorio para vincular las actividades de la planificación.</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-4">
                            <a href="{{ route('poa.index') }}" class="btn btn-back">
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar POA
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
