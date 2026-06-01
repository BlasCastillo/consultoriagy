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

                        <input style="display:none" type="email" name="fakeusernameremembered"/>
                        <input style="display:none" type="password" name="fakepasswordremembered"/>

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-slate-700">Nombre Completo</label>
                            <input id="name" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="off" />
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block font-medium text-sm text-slate-700">Correo Electrónico</label>
                            <input id="email" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required autocomplete="off" />
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block font-medium text-sm text-slate-700">Contraseña</label>
                            <input id="password" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="password" name="password" required autocomplete="new-password" />
                            @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-6">
                            <label for="password_confirmation" class="block font-medium text-sm text-slate-700">Confirmar Contraseña</label>
                            <input id="password_confirmation" class="block mt-1 w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
                        </div>

                        <hr class="my-6 border-slate-200">

                        <h3 class="text-lg font-bold text-slate-800 mb-4">Institución a la que pertenece</h3>
                        <div class="mb-6">
                            <label for="institution_id" class="block font-medium text-sm text-slate-700 mb-2">Selecciona la institución <span class="text-red-500">*</span></label>
                            <select id="institution_id" name="institution_id" required class="block w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm">
                                <option value="" disabled selected>-- Seleccione una Institución --</option>
                                @foreach($institutions as $institution)
                                    <option value="{{ $institution->id }}" {{ old('institution_id') == $institution->id ? 'selected' : '' }}>
                                        {{ $institution->name }} ({{ $institution->type == 'consultoria' ? 'Consultoría' : 'Ente Adscrito' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('institution_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-6 border-slate-200">

                        <h3 class="text-lg font-bold text-slate-800 mb-4">Roles Asignados</h3>
                        
                        <div class="mb-6">
                            <label for="roles" class="block font-medium text-sm text-slate-700 mb-2">Selecciona uno o más roles <span class="text-slate-400 font-normal">(Ctrl+Click para selección múltiple)</span></label>
                            <select id="roles" name="roles[]" multiple class="block w-full border-slate-300 focus:border-slate-800 focus:ring-slate-800 rounded-md shadow-sm min-h-[120px]">
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

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-200 hover:bg-slate-300 text-slate-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                            <button type="submit" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-800 hover:bg-slate-900 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Registrar Usuario
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>