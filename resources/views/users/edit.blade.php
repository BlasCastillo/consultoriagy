<x-app-layout>
    <x-slot name="header">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Editar Usuario') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card overflow-hidden">
                <div class="p-6 text-slate-900">
                    
                    <form method="POST" action="{{ route('users.update', $user) }}" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <input style="display:none" type="email" name="fakeusernameremembered"/>
                        <input style="display:none" type="password" name="fakepasswordremembered"/>

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-slate-700">Nombre Completo</label>
                            <input id="name" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="off" />
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block font-medium text-sm text-slate-700">Correo Electrónico</label>
                            <input id="email" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="off" />
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4 pl-1">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="sr-only peer" {{ old('status', $user->status) ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                <span class="ms-3 text-sm font-medium text-slate-700">Cuenta Activa</span>
                            </label>
                            @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block font-medium text-sm text-slate-700 cursor-pointer select-none" onclick="document.getElementById('password_fields').classList.toggle('hidden')">
                                Contraseña <span class="text-blue-600 hover:text-blue-800 transition-colors text-xs ml-2 underline">(Clic para cambiar)</span>
                            </label>
                            
                            <div id="password_fields" class="mt-2 hidden p-4 border border-slate-200 bg-slate-50 rounded-lg">
                                <p class="text-xs text-slate-500 mb-3 font-medium">Deja esto en blanco si no deseas cambiar la contraseña actual del usuario.</p>
                                
                                <label for="password_input" class="block font-medium text-sm text-slate-700">Nueva Contraseña</label>
                                <input id="password_input" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="password" name="password" autocomplete="new-password" />
                                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                                <label for="password_confirmation" class="block mt-3 font-medium text-sm text-slate-700">Confirmar Nueva Contraseña</label>
                                <input id="password_confirmation" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="password" name="password_confirmation" autocomplete="new-password" />
                            </div>
                        </div>

                        <hr class="my-6 border-slate-200">

                        <h3 class="text-lg font-bold text-slate-800 mb-4">Entidad a la que pertenece</h3>
                        
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-slate-700 mb-2">Tipo de Perfil</label>
                            <div class="flex items-center gap-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="profile_type" value="interno" class="form-radio text-slate-800 focus:ring-slate-800" {{ old('profile_type', $user->jefatura_id ? 'interno' : '') == 'interno' ? 'checked' : '' }}>
                                    <span class="ml-2">Personal Interno (Consultoría Jurídica)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="profile_type" value="externo" class="form-radio text-slate-800 focus:ring-slate-800" {{ old('profile_type', $user->institucion_id ? 'externo' : '') == 'externo' ? 'checked' : '' }}>
                                    <span class="ml-2">Representante Institucional (Ente Externo)</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Select de Institución -->
                            <div id="div_institucion" style="display: none;">
                                <label for="institucion_id" class="block font-medium text-sm text-slate-700 mb-2">Institución (Entes Adscritos)</label>
                                <select name="institucion_id" id="institucion_id" class="w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                                    <option value="">-- Ninguna --</option>
                                    @foreach($instituciones as $institucion)
                                        <option value="{{ $institucion->id }}" {{ old('institucion_id', $user->institucion_id) == $institucion->id ? 'selected' : '' }}>
                                            {{ $institucion->nombre }} {{ $institucion->siglas ? '('.$institucion->siglas.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select de Jefatura -->
                            <div id="div_jefatura" style="display: none;">
                                <label for="jefatura_id" class="block font-medium text-sm text-slate-700 mb-2">Jefatura (Estructura Interna)</label>
                                <select name="jefatura_id" id="jefatura_id" class="w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800 sm:text-sm">
                                    <option value="">-- Ninguna --</option>
                                    @foreach($jefaturas as $jefatura)
                                        <option value="{{ $jefatura->id }}" {{ old('jefatura_id', $user->jefatura_id) == $jefatura->id ? 'selected' : '' }}>
                                            {{ $jefatura->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-6 border-slate-200">

                        <h3 class="text-lg font-bold text-slate-800 mb-4">Roles Asignados</h3>
                        
                        <div class="mb-6">
                            <label for="roles" class="block font-medium text-sm text-slate-700 mb-2">Selecciona uno o más roles <span class="text-slate-400 font-normal">(Ctrl+Click para selección múltiple)</span></label>
                            <select id="roles" name="roles[]" multiple class="block w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm min-h-[120px]">
                                @forelse($roles as $role)
                                    <option value="{{ $role->name }}" {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @empty
                                    <option value="" disabled>No hay roles activos disponibles.</option>
                                @endforelse
                            </select>
                            @error('roles') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                Volver
                            </a>
                            <button type="submit" class="btn btn-neutral">
                                Actualizar Usuario
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let tsInstitucion = new TomSelect("#institucion_id", { create: false, placeholder: "Escriba para buscar..." });
            let tsJefatura = new TomSelect("#jefatura_id", { create: false, placeholder: "Escriba para buscar..." });

            const radios = document.querySelectorAll('input[name="profile_type"]');
            const divInstitucion = document.getElementById('div_institucion');
            const divJefatura = document.getElementById('div_jefatura');
            const selectInstitucion = document.getElementById('institucion_id');
            const selectJefatura = document.getElementById('jefatura_id');

            function toggleUbicacion() {
                let selected = document.querySelector('input[name="profile_type"]:checked');
                if (!selected) return;

                if (selected.value === 'interno') {
                    divJefatura.style.display = 'block';
                    selectJefatura.required = true;

                    divInstitucion.style.display = 'none';
                    selectInstitucion.required = false;
                    tsInstitucion.clear();
                } else if (selected.value === 'externo') {
                    divInstitucion.style.display = 'block';
                    selectInstitucion.required = true;

                    divJefatura.style.display = 'none';
                    selectJefatura.required = false;
                    tsJefatura.clear();
                }
            }

            radios.forEach(radio => radio.addEventListener('change', toggleUbicacion));
            
            toggleUbicacion();
        });
    </script>
</x-app-layout>