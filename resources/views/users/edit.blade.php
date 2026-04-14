<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Editar Usuario') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    <form method="POST" action="{{ route('users.update', $user) }}" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <!-- Evitar auto fill engañoso de navegadores -->
                        <input style="display:none" type="email" name="fakeusernameremembered"/>
                        <input style="display:none" type="password" name="fakepasswordremembered"/>

                        <!-- Nombre -->
                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-slate-700">Nombre Completo</label>
                            <input id="name" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="off" />
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block font-medium text-sm text-slate-700">Correo Electrónico</label>
                            <input id="email" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="off" />
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Estatus -->
                        <div class="mb-4 pl-1">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="sr-only peer" {{ old('status', $user->status) ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ms-3 text-sm font-medium text-slate-700">Cuenta Activa</span>
                            </label>
                            @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="block font-medium text-sm text-slate-700 cursor-pointer" onclick="document.getElementById('password_fields').classList.toggle('hidden')">
                                Contraseña <span class="text-blue-600 text-xs ml-2">(Clic para cambiar)</span>
                            </label>
                            
                            <div id="password_fields" class="mt-2 hidden p-4 border border-slate-200 bg-slate-50 rounded">
                                <p class="text-xs text-slate-500 mb-3">Deja esto en blanco si no deseas cambiar la contraseña actual del usuario.</p>
                                
                                <label for="password_input" class="block font-medium text-sm text-slate-700">Nueva Contraseña</label>
                                <input id="password_input" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="password" name="password" autocomplete="new-password" />
                                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                                <label for="password_confirmation" class="block mt-3 font-medium text-sm text-slate-700">Confirmar Nueva Contraseña</label>
                                <input id="password_confirmation" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="password" name="password_confirmation" autocomplete="new-password" />
                            </div>
                        </div>

                        <hr class="my-6 border-slate-200">

                        <h3 class="text-lg font-medium text-slate-800 mb-4">Roles Asignados</h3>
                        
                        <div class="mb-6">
                            <label for="roles" class="block font-medium text-sm text-slate-700 mb-2">Selecciona uno o más roles (Ctrl+Click para selección múltiple)</label>
                            <select id="roles" name="roles[]" multiple class="block w-full border-slate-300 focus:border-[#1e293b] focus:ring-[#1e293b] rounded-md shadow-sm min-h-[120px]">
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

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('users.index') }}" class="text-slate-600 hover:text-slate-900 underline text-sm mr-4">Cancelar</a>
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-4 rounded transition duration-150 shadow-sm">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
