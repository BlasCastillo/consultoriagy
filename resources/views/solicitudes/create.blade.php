<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Nueva Solicitud') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <form action="{{ route('solicitudes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="titulo_acto" class="block font-medium text-sm text-slate-700 mb-2">Título del Acto <span class="text-red-500">*</span></label>
                            <input type="text" name="titulo_acto" id="titulo_acto" required class="block w-full border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm sm:text-sm" placeholder="Ej. Decreto Nro 001-2026..." value="{{ old('titulo_acto') }}">
                            @error('titulo_acto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-6">
                            <label for="archivo_word" class="block font-medium text-sm text-slate-700 mb-2">Borrador (Archivo Word .doc, .docx) <span class="text-red-500">*</span></label>
                            <input type="file" name="archivo_word" id="archivo_word" required accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-slate-500">Sube el documento preliminar que será evaluado por la Consultoría Jurídica.</p>
                            @error('archivo_word') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-200 gap-3">
                            <a href="{{ route('solicitudes.index') }}" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center font-medium text-sm bg-slate-200 hover:bg-slate-300 text-slate-700">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2.5 rounded-lg shadow-sm transition-all duration-300 flex items-center justify-center font-medium text-sm bg-indigo-600 hover:bg-indigo-700 text-white">
                                Subir Solicitud
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>