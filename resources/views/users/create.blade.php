<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registrar Nuevo Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    <form method="POST" action="{{ route('users.store') }}" autocomplete="off">
                        @csrf

                        <!-- Evitar auto fill engañoso de navegadores -->
                        <input style="display:none" type="email" name="fakeusernameremembered"/>
                        <input style="display:none" type="password" name="fakepasswordremembered"/>

                        <!-- Nombre -->
                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-slate-700">Nombre Completo</label>
                            <input id="name" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="off" />
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block font-medium text-sm text-slate-700">Correo Electrónico</label>
                            <input id="email" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required autocomplete="off" />
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="block font-medium text-sm text-slate-700">Contraseña</label>
                            <input id="password" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="password" name="password" required autocomplete="new-password" />
                            @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-6">
                            <label for="password_confirmation" class="block font-medium text-sm text-slate-700">Confirmar Contraseña</label>
                            <input id="password_confirmation" class="block mt-1 w-full border-slate-300 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
                        </div>

                        <hr class="my-6 border-slate-200">

                        <h3 class="text-lg font-medium text-slate-800 mb-4">Roles Asignados</h3>
                        
                        <div class="mb-6">
                            <label for="roles" class="block font-medium text-sm text-slate-700 mb-2">Selecciona uno o más roles (Ctrl+Click para selección múltiple)</label>
                            <select id="roles" name="roles[]" multiple class="block w-full border-slate-300 focus:border-[#1e293b] focus:ring-[#1e293b] rounded-md shadow-sm min-h-[120px]">
                                @forelse($roles as $role)
                                    <option value="{{ $role->name }}" {{ in_array($role->name, old('roles', [])) ? 'selected' : '' }}>
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
                                Registrar Usuario
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
