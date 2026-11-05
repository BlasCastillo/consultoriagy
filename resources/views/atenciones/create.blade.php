<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registrar Atención al Ciudadano') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
                    
                    @if(session('success'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: 'success', title: "{!! addslashes(session('success')) !!}" });
                            });
                        </script>
                    @endif

                    <form method="POST" action="{{ route('atenciones.store') }}" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Fila 1 -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Cédula</label>
                                <input type="text" name="cedula" required value="{{ old('cedula') }}" class="val-numerico mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Correo Electrónico (Opcional)</label>
                                <input type="email" name="correo" value="{{ old('correo') }}" class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Fila 2 -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Nombre</label>
                                <input type="text" name="nombre" required value="{{ old('nombre') }}" class="val-solo-letras mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Apellido</label>
                                <input type="text" name="apellido" required value="{{ old('apellido') }}" class="val-solo-letras mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Fila 3 -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Teléfono (Opcional)</label>
                                <input type="text" name="telefono" value="{{ old('telefono') }}" class="val-numerico mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Institución (Opcional)</label>
                                <input type="text" name="institucion" value="{{ old('institucion') }}" class="val-texto-limpio mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Fila 4 -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700">Jefatura de Destino</label>
                                <select name="jefatura_destino_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione una jefatura...</option>
                                    @foreach($jefaturas as $jefatura)
                                        <option value="{{ $jefatura->id }}" {{ old('jefatura_destino_id') == $jefatura->id ? 'selected' : '' }}>
                                            {{ $jefatura->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Fila 5 -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700">Motivo</label>
                                <select name="motivo" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione un motivo...</option>
                                    <option value="asesoria" {{ old('motivo') == 'asesoria' ? 'selected' : '' }}>Asesoría</option>
                                    <option value="consulta" {{ old('motivo') == 'consulta' ? 'selected' : '' }}>Consulta</option>
                                    <option value="solicitud de documentos" {{ old('motivo') == 'solicitud de documentos' ? 'selected' : '' }}>Solicitud de Documentos</option>
                                    <option value="particulares" {{ old('motivo') == 'particulares' ? 'selected' : '' }}>Particulares</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="btn btn-neutral">
                                Guardar Atención
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
