<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Registrar Institución') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    
                    <form method="POST" action="{{ route('institutions.store') }}" autocomplete="off">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-slate-700">Nombre de la Institución</label>
                            <input id="name" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800" type="text" name="name" value="{{ old('name') }}" required autofocus />
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="rif" class="block font-medium text-sm text-slate-700">RIF (Opcional)</label>
                            <input id="rif" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800" type="text" name="rif" value="{{ old('rif') }}" />
                            @error('rif') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="type" class="block font-medium text-sm text-slate-700">Tipo de Institución</label>
                            <select id="type" name="type" required class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800">
                                <option value="ente_adscrito" {{ old('type') == 'ente_adscrito' ? 'selected' : '' }}>Ente Adscrito / Externo</option>
                                <option value="consultoria" {{ old('type') == 'consultoria' ? 'selected' : '' }}>Consultoría Jurídica (Ente Rector)</option>
                            </select>
                            @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="address" class="block font-medium text-sm text-slate-700">Dirección (Opcional)</label>
                            <textarea id="address" name="address" rows="2" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800">{{ old('address') }}</textarea>
                            @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="block font-medium text-sm text-slate-700">Teléfono (Opcional)</label>
                            <input id="phone" class="block mt-1 w-full border-slate-300 rounded-md shadow-sm focus:border-slate-800 focus:ring-slate-800" type="text" name="phone" value="{{ old('phone') }}" />
                            @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <input type="hidden" name="status" value="active">

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('institutions.index') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-200 hover:bg-slate-300 text-slate-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Volver
                            </a>
                            <button type="submit" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center gap-2 font-medium text-sm md:text-base bg-slate-800 hover:bg-slate-900 text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Registrar Institución
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>