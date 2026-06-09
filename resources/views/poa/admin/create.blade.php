<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Crear Nuevo POA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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

                    <form method="POST" action="{{ route('poa.store') }}" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Año de Planificación</label>
                                <input type="number" name="anio" required value="{{ old('anio', date('Y')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Estado Inicial</label>
                                <select name="estado" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="Aprobado">Aprobado</option>
                                    <option value="Ejecucion">En Ejecución</option>
                                    <option value="Cerrado">Cerrado</option>
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

                        <div class="flex items-center justify-end pt-4 border-t border-slate-200">
                            <a href="{{ route('poa.index') }}" class="mr-4 text-sm font-medium text-slate-600 hover:text-slate-500">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                                Guardar POA
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
