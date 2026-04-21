<x-guest-layout>
    <div class="fixed inset-0 z-[-1] bg-login-image"></div>

    <div class="min-h-screen flex flex-col items-center justify-center p-6 relative z-10">

        <div class="card">
            <div class="card2">
                <form method="POST" action="{{ route('login') }}" autocomplete="off" class="form">
                    @csrf

                    <div class="mb-4">
                        <img src="{{ asset('img/logo-gobernacion.svg') }}" alt="Logo Gobernación"
                            class="h-20 w-auto mx-auto filter drop-shadow-lg">
                    </div>

                    <div id="heading">
                        <h2 class="text-2xl font-bold text-white">SGCJ</h2>
                        <p class="text-[10px] text-white uppercase tracking-[0.2em] mt-1">Consultoría Jurídica</p>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <div class="mb-2">
                        <div class="field">
                            <svg viewBox="0 0 16 16" fill="white" height="16" width="16"
                                xmlns="http://www.w3.org/2000/svg" class="input-icon">
                                <path
                                    d="M13.106 7.222c0-2.967-2.249-5.032-5.482-5.032-3.35 0-5.646 2.318-5.646 5.702 0 3.493 2.235 5.708 5.762 5.708.862 0 1.689-.123 2.304-.335v-.862c-.43.199-1.354.328-2.29.328-2.926 0-4.813-1.88-4.813-4.798 0-2.844 1.921-4.881 4.594-4.881 2.735 0 4.608 1.688 4.608 4.156 0 1.682-.554 2.769-1.416 2.769-.492 0-.772-.28-.772-.76V5.206H8.923v.834h-.11c-.266-.595-.881-.964-1.6-.964-1.4 0-2.378 1.162-2.378 2.823 0 1.737.957 2.906 2.379 2.906.8 0 1.415-.39 1.709-1.087h.11c.081.67.703 1.148 1.503 1.148 1.572 0 2.57-1.415 2.57-3.643zm-7.177.704c0-1.197.54-1.907 1.456-1.907.93 0 1.524.738 1.524 1.907S8.308 9.84 7.371 9.84c-.895 0-1.442-.725-1.442-1.914z">
                                </path>
                            </svg>
                            <input type="email" id="email" name="email" class="input-field"
                                placeholder="Correo Electrónico" required />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-400 text-[11px] pl-4" />
                    </div>

                    <div class="mb-2">
                        <div class="field">
                            <svg viewBox="0 0 16 16" fill="white" height="16" width="16"
                                xmlns="http://www.w3.org/2000/svg" class="input-icon">
                                <path
                                    d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z">
                                </path>
                            </svg>
                            <input type="password" id="password" name="password" class="input-field"
                                placeholder="Contraseña" required />
                        </div>
                        <x-input-error :messages="$errors->get('password')"
                            class="mt-1 text-red-400 text-[11px] pl-4" />
                    </div>

                    <div class="flex items-center justify-center mt-2">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="rounded border-white/20 bg-transparent text-blue-600 focus:ring-0">
                            <span class="ms-2 text-[12px] text-white">Recordarme</span>
                        </label>
                    </div>

                    <div class="btn mt-6">
                        <button type="submit" class="button1">INICIAR SESIÓN</button>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="button3 text-center block mt-4">
                            ¿Olvidó su contraseña?
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>