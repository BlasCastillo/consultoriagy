<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Asignar Digitalizador') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body text-slate-900">
                    <h3 class="mb-4 border-b border-slate-200 pb-2 text-lg font-bold text-slate-800">Recepción Física y Asignación</h3>

                    <div class="mb-6 flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
                        <div>
                            <p class="text-2xl font-bold text-blue-900">Gaceta Nro. {{ str_pad($gaceta->numero, 4, '0', STR_PAD_LEFT) }}</p>
                            <p><strong>Año:</strong> {{ $gaceta->anio }}</p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-warning">Reservada</span>
                        </div>
                    </div>

                    <form action="{{ route('gacetas.asignar.save', $gaceta->id) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="fecha_recepcion_fisica" class="block text-sm font-medium text-slate-700">Fecha de Recepción Física <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_recepcion_fisica" id="fecha_recepcion_fisica" required value="{{ date('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                            </div>

                            <div>
                                <label for="digitalizador_id" class="block text-sm font-medium text-slate-700">Seleccionar Digitalizador <span class="text-red-500">*</span></label>
                                <select name="digitalizador_id" id="digitalizador_id" required
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-800 focus:ring-blue-800 sm:text-sm">
                                    <option value="">-- Seleccione Digitalizador --</option>
                                    @foreach($digitalizadores as $digi)
                                        <option value="{{ $digi->id }}">{{ $digi->name }} ({{ $digi->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-2">
                            <a href="{{ route('gacetas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Confirmar Recepción y Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
