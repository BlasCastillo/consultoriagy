<nav x-data="{ open: false }" class="bg-slate-900 border-b border-slate-700">
    <div class="max-w-10xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-1 text-white font-bold text-xl tracking-wider transition-transform duration-200 hover:scale-[1.02]">
                        <img src="{{ asset('img/logo-gobernacion.svg') }}" alt="Logo Yaracuy" class="h-14 w-auto">
                        <span>SGCJ</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center">

                    {{-- 1. Panel de Control --}}
                    @can('actualizar Gaceta')
                        <x-nav-link :href="url('/gacetas/panel-digitalizador')"
                            :active="request()->is('gacetas/panel-digitalizador')">
                            {{ __('Panel Digitalizador') }}
                        </x-nav-link>
                    @endcan
                    @can('leer Dashboard')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endcan

                    {{-- 4. Planificación (POA) --}}
                    @can('leer Poa')
                        <div class="hidden sm:flex sm:items-center sm:ms-2">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="nav-trigger">
                                        <i class="fa-solid fa-chart-pie mr-2"></i>
                                        <div>{{ __('Planificación (POA)') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('poa.dashboard')">
                                        {{ __('Dashboard POA') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('poa.index')">
                                        {{ __('Gestionar POAs') }}
                                    </x-dropdown-link>
                                    @canany(['crear FichaActividad', 'leer FichaActividad', 'crear ActividadPoa'])
                                        <x-dropdown-link :href="route('fichas.create')">
                                            {{ __('Registrar Actividad') }}
                                        </x-dropdown-link>
                                    @endcanany
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcan

                    {{-- C) Para Administradores y Procesadores --}}
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador']) || auth()->user()->canAny(['leer Solicitud', 'leer Gaceta', 'leer PrestamoArchivo']))

                        {{-- Dropdown Procesos --}}
                        <div class="hidden sm:flex sm:items-center sm:ms-2">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="nav-trigger">
                                        <i class="fa-solid fa-gears mr-2"></i>
                                        <div>{{ __('Procesos') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('leer Solicitud')
                                        <x-dropdown-link :href="route('solicitudes.index')">
                                            {{ __('Gestión de Solicitudes') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('leer Gaceta')
                                        <x-dropdown-link :href="route('gacetas.publicadas')">
                                            {{ __('Histórico de Gacetas') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('crear Gaceta')
                                        <x-dropdown-link :href="route('gacetas.index')">
                                            {{ __('Mesa de Control (Gacetas)') }}
                                        </x-dropdown-link>
                                        <x-dropdown-link :href="route('gacetas.create')">
                                            {{ __('Ensamblar Nueva Gaceta') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador']) || auth()->user()->can('leer PrestamoArchivo'))
                                        <x-dropdown-link :href="route('archivo.index')">
                                            {{ __('Módulo de Archivo') }}
                                        </x-dropdown-link>
                                    @endif
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador']) || auth()->user()->canAny(['leer Institucion', 'leer TipoInstitucion', 'leer Gobernador', 'leer Titulo', 'leer Jefatura', 'leer Sede', 'leer Estante', 'leer Gaveta', 'leer Atencion']))

                        {{-- Dropdown Registros Maestros --}}
                        <div class="hidden sm:flex sm:items-center sm:ms-2">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="nav-trigger">
                                        <i class="fa-solid fa-database mr-2"></i>
                                        <div>{{ __('Registros Maestros') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('leer Institucion')
                                        <x-dropdown-link :href="route('institutions.index')">
                                            {{ __('Instituciones') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('leer TipoInstitucion')
                                        <x-dropdown-link :href="route('tipo-instituciones.index')">
                                            {{ __('Tipos de Institución') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('leer Jefatura')
                                        <x-dropdown-link :href="route('jefaturas.index')">
                                            {{ __('Estructura Interna') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('leer Gobernador')
                                        <x-dropdown-link :href="route('gobernadores.index')">
                                            {{ __('Gobernadores') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('leer Titulo')
                                        <x-dropdown-link :href="route('titulos.index')">
                                            {{ __('Títulos') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador']) || auth()->user()->can('leer Sede'))
                                        <x-dropdown-link :href="route('sedes.index')">
                                            {{ __('Sedes (Edificios)') }}
                                        </x-dropdown-link>
                                    @endif
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador']) || auth()->user()->can('leer Estante'))
                                        <x-dropdown-link :href="route('estantes.index')">
                                            {{ __('Estantes') }}
                                        </x-dropdown-link>
                                    @endif
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador']) || auth()->user()->can('leer Gaveta'))
                                        <x-dropdown-link :href="route('gavetas.index')">
                                            {{ __('Gavetas') }}
                                        </x-dropdown-link>
                                    @endif
                                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Super Administrador', 'Súper Administrador']) || auth()->user()->can('leer Atencion'))
                                        <x-dropdown-link :href="route('atenciones.index')">
                                            {{ __('Atención al Ciudadano') }}
                                        </x-dropdown-link>
                                    @endif
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    {{-- Dropdown Reportes --}}
                    @can('leer Bitacora')
                        <div class="hidden sm:flex sm:items-center sm:ms-2">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="nav-trigger">
                                        <i class="fa-solid fa-chart-bar mr-2"></i>
                                        <div>{{ __('Reportes') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('bitacora.index')">
                                        {{ __('Bitácora') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcan

                    {{-- Dropdown Configuración --}}
                    @canany(['leer Usuario', 'leer Rol', 'leer Permiso', 'leer Respaldo'])
                        <div class="hidden sm:flex sm:items-center sm:ms-2">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="nav-trigger">
                                        <i class="fa-solid fa-screwdriver-wrench mr-2"></i>
                                        <div>{{ __('Configuración') }}</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @can('leer Usuario')
                                        <x-dropdown-link :href="route('users.index')">
                                            {{ __('Usuarios') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('leer Rol')
                                        <x-dropdown-link :href="route('roles.index')">
                                            {{ __('Roles y Permisos') }}
                                        </x-dropdown-link>
                                    @endcan
                                    @can('leer Respaldo')
                                        <x-dropdown-link :href="route('backups.index')">
                                            {{ __('Respaldos') }}
                                        </x-dropdown-link>
                                    @endcan
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcanany
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center rounded-md border border-transparent bg-slate-800 px-3 py-2 text-sm font-medium leading-4 text-slate-200 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-slate-700 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 active:translate-y-0">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Mi Perfil') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                    {{ __('Cerrar Sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>

                    <div class="relative ml-5" x-data="{
                                unreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }},
                                notifications: {{ auth()->user()->unreadNotifications->take(5)->toJson() ?? '[]' }},
                                showDropdown: false
                            }" @click.away="showDropdown = false" x-init="
                                if (typeof Echo !== 'undefined') {
                                    Echo.private('App.Models.User.' + {{ auth()->id() }})
                                        .notification((notification) => {
                                            // 1. Alpine actualiza el contador de la campana en automático
                                            unreadCount++;

                                            // 2. Ocultar el mensaje de vacío si existe
                                            let emptyMsg = document.getElementById('empty-notifications');
                                            if (emptyMsg) emptyMsg.style.display = 'none';

                                            // 3. Inyectar el nuevo HTML en la lista
                                            let list = document.getElementById('notifications-list');
                                            if (list) {
                                                let msg = notification.mensaje || 'Tienes una nueva notificación';
                                                let id = notification.id || ''; 
                                                // Preparamos la URL. Si no hay ID en el broadcast, mandamos un #
                                                let url = id ? `/notificaciones/${id}/leer` : '#';

                                                let html = `
                                                    <a href='${url}' class='block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100 border-b border-slate-100 bg-slate-50 transition-colors'>
                                                        <div class='text-sm text-slate-800 font-semibold'>${msg}</div>
                                                        <div class='text-xs text-slate-400 mt-1'>Justo ahora</div>
                                                    </a>
                                                `;
                                                list.insertAdjacentHTML('afterbegin', html);
                                            }
                                        });
                                }
                            ">
                        <button @click="showDropdown = !showDropdown" class="text-slate-400 hover:text-white transition">
                            <div class="relative inline-block">
                                <i class="fa-solid fa-bell text-xl"></i>
                                <span x-show="unreadCount > 0" x-text="unreadCount"
                                    class="absolute -top-1 -right-2 inline-flex items-center justify-center px-1.5 py-0.5 text-[0.65rem] font-bold leading-none text-white bg-red-600 rounded-full"
                                    style="display: none;"></span>
                            </div>
                        </button>

                        <div x-show="showDropdown"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-slate-200"
                            style="display: none;">
                            <div class="px-4 py-2 border-b border-slate-100 font-semibold text-slate-700">Notificaciones</div>

                            <div id="notifications-list" class="max-h-64 overflow-y-auto">
                                @forelse(auth()->user()->unreadNotifications as $notificacion)
                                    <a href="{{ route('notificaciones.leer', $notificacion->id) }}"
                                        class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-100 border-b border-slate-100">
                                        <div class="text-sm text-slate-800">
                                            {{ $notificacion->data['mensaje'] ?? 'Tienes una nueva notificación' }}
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1">{{ $notificacion->created_at->diffForHumans() }}
                                        </div>
                                    </a>
                                @empty
                                    <div id="empty-notifications" class="px-4 py-3 text-sm text-slate-500 text-center">No tienes
                                        notificaciones nuevas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm font-bold text-slate-700 hover:text-blue-600 bg-slate-100 px-4 py-2 rounded-md shadow-sm">
                        Iniciar Sesión (Funcionarios)
                    </a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            {{-- 1. Panel de Control --}}
            @can('actualizar Gaceta')
                <x-responsive-nav-link :href="url('/gacetas/panel-digitalizador')"
                    :active="request()->is('gacetas/panel-digitalizador')">
                    <i class="fa-solid fa-house w-5 h-5 mr-1"></i>
                    {{ __('Panel Digitalizador') }}
                </x-responsive-nav-link>
            @endcan
            @can('leer Dashboard')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i class="fa-solid fa-house w-5 h-5 mr-1"></i>
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endcan

            {{-- 4. Planificación (POA) --}}
            @can('leer Poa')
                <div class="px-4 py-2 mt-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Planificación (POA)
                </div>
                <x-responsive-nav-link :href="route('poa.dashboard')" :active="request()->routeIs('poa.dashboard')">
                    <i class="fa-solid fa-chart-pie w-5 h-5 mr-1"></i>
                    {{ __('Dashboard POA') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('poa.index')" :active="request()->routeIs('poa.index')">
                    <i class="fa-solid fa-list-check w-5 h-5 mr-1"></i>
                    {{ __('Gestionar POAs') }}
                </x-responsive-nav-link>
            @endcan

            @canany(['crear FichaActividad', 'leer FichaActividad', 'crear ActividadPoa'])
                <x-responsive-nav-link :href="route('fichas.create')" :active="request()->routeIs('fichas.create')">
                    <i class="fa-solid fa-clipboard-check w-5 h-5 mr-1"></i>
                    {{ __('Registrar Actividad') }}
                </x-responsive-nav-link>
            @endcanany

            @canany(['leer Solicitud', 'leer Gaceta', 'leer PrestamoArchivo'])
                <div class="px-4 py-2 mt-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Procesos</div>

                @can('leer Solicitud')
                    <x-responsive-nav-link :href="route('solicitudes.index')" :active="request()->routeIs(['solicitudes.index', 'solicitudes.create', 'solicitudes.edit', 'solicitudes.show'])">
                        <i class="fa-solid fa-file-lines w-5 h-5 mr-1"></i>
                        {{ __('Gestión de Solicitudes') }}
                    </x-responsive-nav-link>
                @endcan

                @can('leer Gaceta')
                    <x-responsive-nav-link :href="route('gacetas.publicadas')"
                        :active="request()->routeIs(['gacetas.publicadas'])">
                        <i class="fa-solid fa-book w-5 h-5 mr-1"></i>
                        {{ __('Histórico de Gacetas') }}
                    </x-responsive-nav-link>
                @endcan
                @can('crear Gaceta')
                    <x-responsive-nav-link :href="route('gacetas.index')" :active="request()->routeIs(['gacetas.index'])">
                        <i class="fa-solid fa-table-columns w-5 h-5 mr-1"></i>
                        {{ __('Mesa de Control (Gacetas)') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('gacetas.create')" :active="request()->routeIs(['gacetas.create'])">
                        <i class="fa-solid fa-layer-group w-5 h-5 mr-1"></i>
                        {{ __('Ensamblar Nueva Gaceta') }}
                    </x-responsive-nav-link>
                @endcan

                @can('leer PrestamoArchivo')
                    <x-responsive-nav-link :href="route('archivo.index')" :active="request()->routeIs(['archivo.index'])">
                        <i class="fa-solid fa-folder w-5 h-5 mr-1"></i>
                        {{ __('Módulo de Archivo') }}
                    </x-responsive-nav-link>
                @endcan
            @endcanany

            @canany(['leer Institucion', 'leer TipoInstitucion', 'leer Gobernador', 'leer Titulo', 'leer Jefatura', 'leer Sede', 'leer Estante', 'leer Gaveta', 'leer Atencion'])

                <div class="px-4 py-2 mt-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Registros Maestros
                </div>
                @can('leer Institucion')
                    <x-responsive-nav-link :href="route('institutions.index')" :active="request()->routeIs('institutions.*')">
                        {{ __('Instituciones') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer TipoInstitucion')
                    <x-responsive-nav-link :href="route('tipo-instituciones.index')"
                        :active="request()->routeIs('tipo-instituciones.*')">
                        {{ __('Tipos de Institución') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Jefatura')
                    <x-responsive-nav-link :href="route('jefaturas.index')" :active="request()->routeIs('jefaturas.*')">
                        {{ __('Estructura Interna') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Gobernador')
                    <x-responsive-nav-link :href="route('gobernadores.index')" :active="request()->routeIs('gobernadores.*')">
                        {{ __('Gobernadores') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Titulo')
                    <x-responsive-nav-link :href="route('titulos.index')" :active="request()->routeIs('titulos.*')">
                        {{ __('Títulos') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Sede')
                    <x-responsive-nav-link :href="route('sedes.index')" :active="request()->routeIs('sedes.*')">
                        <i class="fa-solid fa-building w-5 h-5 mr-1"></i> {{ __('Sedes') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Estante')
                    <x-responsive-nav-link :href="route('estantes.index')" :active="request()->routeIs('estantes.*')">
                        <i class="fa-solid fa-server w-5 h-5 mr-1"></i> {{ __('Estantes') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Gaveta')
                    <x-responsive-nav-link :href="route('gavetas.index')" :active="request()->routeIs('gavetas.*')">
                        <i class="fa-solid fa-box-archive w-5 h-5 mr-1"></i> {{ __('Gavetas') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Atencion')
                    <x-responsive-nav-link :href="route('atenciones.index')" :active="request()->routeIs('atenciones.*')">
                        {{ __('Atención al Ciudadano') }}
                    </x-responsive-nav-link>
                @endcan

            @endcanany

            @can('leer Bitacora')
                <div class="px-4 py-2 mt-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Reportes</div>
                <x-responsive-nav-link :href="route('bitacora.index')" :active="request()->routeIs('bitacora.*')">
                    {{ __('Bitácora') }}
                </x-responsive-nav-link>
            @endcan

            @canany(['leer Usuario', 'leer Rol', 'leer Permiso', 'leer Respaldo'])
                <div class="px-4 py-2 mt-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Configuración</div>
                @can('leer Usuario')
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Usuarios') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Rol')
                    <x-responsive-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">
                        {{ __('Roles y Permisos') }}
                    </x-responsive-nav-link>
                @endcan
                @can('leer Respaldo')
                    <x-responsive-nav-link :href="route('backups.index')" :active="request()->routeIs('backups.*')">
                        {{ __('Respaldos') }}
                    </x-responsive-nav-link>
                @endcan
            @endcanany
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-slate-200">
                <div class="px-4">
                    <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        <i class="fa-regular fa-circle-user"></i>
                        {{ __('Mi Perfil') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            {{ __('Cerrar Sesión') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-slate-200">
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        {{ __('Iniciar Sesión (Funcionarios)') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endauth
    </div>
</nav>
