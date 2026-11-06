<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Editar Unidad Interna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">
                    
                    <form method="POST" action="{{ route('jefaturas.update', $jefatura) }}" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="nombre" class="block font-medium text-sm text-slate-700">Nombre de la Unidad</label>
                            <input id="nombre" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800" type="text" name="nombre" value="{{ old('nombre', $jefatura->nombre) }}" required autofocus autocomplete="off" />
                            @error('nombre') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="nivel" class="block font-medium text-sm text-slate-700">Nivel Jerárquico</label>
                            <select id="nivel" name="nivel" required class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800">
                                <option value="" disabled selected>Seleccione el Nivel (1: Consultor, 2: Dirección, etc.)</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('nivel', $jefatura->nivel) == $i ? 'selected' : '' }}>Nivel {{ $i }}</option>
                                @endfor
                            </select>
                            @error('nivel') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4" id="div_parent_id" style="display: none;">
                            <label for="parent_id" class="block font-medium text-sm text-slate-700">Unidad Superior (Dependencia)</label>
                            <select id="parent_id" name="parent_id" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800">
                                <option value="" disabled selected>Seleccione la Unidad Superior</option>
                                @foreach($posiblesPadres as $padre)
                                    <option value="{{ $padre->id }}" data-nivel="{{ $padre->nivel }}" {{ old('parent_id', $jefatura->parent_id) == $padre->id ? 'selected' : '' }}>
                                        {{ $padre->nombre }} (Nivel {{ $padre->nivel }})
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('jefaturas.index') }}" class="btn btn-back">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nivelSelect = document.getElementById('nivel');
            const parentIdDiv = document.getElementById('div_parent_id');
            const parentIdSelect = document.getElementById('parent_id');
            const parentOptions = parentIdSelect.querySelectorAll('option[data-nivel]');

            function handleNivelChange() {
                const nivel = parseInt(nivelSelect.value);

                if (isNaN(nivel)) {
                    parentIdDiv.style.display = 'none';
                    parentIdSelect.disabled = true;
                    parentIdSelect.required = false;
                    return;
                }

                if (nivel === 1) {
                    parentIdDiv.style.display = 'none';
                    parentIdSelect.disabled = true;
                    parentIdSelect.required = false;
                    parentIdSelect.value = ''; // Limpiar seleccion
                } else {
                    parentIdDiv.style.display = 'block';
                    parentIdSelect.disabled = false;
                    parentIdSelect.required = true;

                    // Opcional: Deshabilitar opciones de nivel mayor o igual
                    parentOptions.forEach(option => {
                        const padreNivel = parseInt(option.getAttribute('data-nivel'));
                        if (padreNivel >= nivel) {
                            option.disabled = true;
                            // Si estaba seleccionada una opcion no valida, deseleccionar
                            if (option.selected) {
                                parentIdSelect.value = '';
                            }
                        } else {
                            option.disabled = false;
                        }
                    });
                }
            }

            nivelSelect.addEventListener('change', handleNivelChange);
            
            // Ejecutar al cargar la pagina para inicializar
            handleNivelChange();
        });
    </script>
</x-app-layout>
