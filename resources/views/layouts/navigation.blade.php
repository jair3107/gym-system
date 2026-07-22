<nav x-data="{ open: false }" class="bg-ink-850/80 backdrop-blur border-b border-white/[0.06] sticky top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route(auth()->user()->role->dashboard()) }}" class="flex items-center gap-2 group">
                        <x-application-logo class="block h-8 w-8 text-gold-400 transition group-hover:scale-110" />
                        <span class="font-brand text-lg font-bold tracking-[0.25em] text-zinc-100">AURUM</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route(auth()->user()->role->dashboard())" :active="request()->routeIs('*.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if (auth()->user()->hasRole(\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Empleado))
                        <x-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                            {{ auth()->user()->etiquetaClientes() }}
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->puedeGestionarNutricion())
                        <x-nav-link :href="route('dietas.index')" :active="request()->routeIs('dietas.*')">
                            {{ __('Dietas') }}
                        </x-nav-link>
                        <x-nav-link :href="route('seguimientos-nutricion.index')" :active="request()->routeIs('seguimientos-nutricion.*')">
                            {{ __('Seg. nutrición') }}
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->puedeGestionarEntrenamiento())
                        <x-nav-link :href="route('rutinas.index')" :active="request()->routeIs('rutinas.*')">
                            {{ __('Rutinas') }}
                        </x-nav-link>
                        <x-nav-link :href="route('seguimientos-entrenamiento.index')" :active="request()->routeIs('seguimientos-entrenamiento.*')">
                            {{ __('Seg. entrenamiento') }}
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->puedeGestionarVentas() || auth()->user()->puedeGestionarMembresias())
                        <x-nav-link :href="auth()->user()->puedeGestionarVentas() ? route('ventas.index') : route('membresias.index')" :active="request()->routeIs('ventas.*') || request()->routeIs('membresias.*') || request()->routeIs('productos.*') || request()->routeIs('servicios.*')">
                            {{ auth()->user()->puedeGestionarVentas() ? __('Ventas') : __('Membresías') }}
                        </x-nav-link>
                    @endif
                    @if (auth()->user()->puedeGestionarEmpleados())
                        <x-nav-link :href="route('empleados.index')" :active="request()->routeIs('empleados.*') || request()->routeIs('puestos.*')">
                            {{ __('Empleados') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                @php($noLeidas = auth()->user()->unreadNotifications()->count())
                <a href="{{ route('notificaciones.index') }}"
                   class="relative inline-flex items-center justify-center h-9 w-9 rounded-lg text-zinc-300 hover:text-white hover:bg-white/5 transition"
                   title="Notificaciones">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    @if ($noLeidas > 0)
                        <span class="absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-gold-400 text-ink-900 text-[0.65rem] font-bold flex items-center justify-center">
                            {{ $noLeidas > 9 ? '9+' : $noLeidas }}
                        </span>
                    @endif
                </a>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 text-sm leading-4 font-medium rounded-lg text-zinc-300 hover:text-white hover:bg-white/5 focus:outline-none transition ease-in-out duration-150">
                            <span class="flex items-center justify-center h-7 w-7 rounded-full bg-gold-400/15 text-gold-300 text-xs font-brand font-semibold uppercase">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-white/[0.06]">
                            <div class="text-sm font-medium text-zinc-200">{{ Auth::user()->name }}</div>
                            @if ($rol = Auth::user()->rolParaMostrar())
                                <div class="text-xs text-gold-500 uppercase tracking-wider">{{ $rol }}</div>
                            @endif
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-zinc-400 hover:text-white hover:bg-white/5 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route(auth()->user()->role->dashboard())" :active="request()->routeIs('*.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if (auth()->user()->hasRole(\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Empleado))
                <x-responsive-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                    {{ auth()->user()->etiquetaClientes() }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->puedeGestionarNutricion())
                <x-responsive-nav-link :href="route('dietas.index')" :active="request()->routeIs('dietas.*')">
                    {{ __('Dietas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('seguimientos-nutricion.index')" :active="request()->routeIs('seguimientos-nutricion.*')">
                    {{ __('Seg. nutrición') }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->puedeGestionarEntrenamiento())
                <x-responsive-nav-link :href="route('rutinas.index')" :active="request()->routeIs('rutinas.*')">
                    {{ __('Rutinas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('seguimientos-entrenamiento.index')" :active="request()->routeIs('seguimientos-entrenamiento.*')">
                    {{ __('Seg. entrenamiento') }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->puedeGestionarVentas() || auth()->user()->puedeGestionarMembresias())
                <x-responsive-nav-link :href="auth()->user()->puedeGestionarVentas() ? route('ventas.index') : route('membresias.index')" :active="request()->routeIs('ventas.*') || request()->routeIs('membresias.*') || request()->routeIs('productos.*') || request()->routeIs('servicios.*')">
                    {{ auth()->user()->puedeGestionarVentas() ? __('Ventas') : __('Membresías') }}
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->puedeGestionarEmpleados())
                <x-responsive-nav-link :href="route('empleados.index')" :active="request()->routeIs('empleados.*') || request()->routeIs('puestos.*')">
                    {{ __('Empleados') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/[0.06]">
            <div class="px-4">
                <div class="font-medium text-base text-zinc-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-zinc-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('notificaciones.index')" :active="request()->routeIs('notificaciones.*')">
                    {{ __('Notificaciones') }}
                    @if (($noLeidasResp = auth()->user()->unreadNotifications()->count()) > 0)
                        <span class="ms-1 text-xs text-gold-400">({{ $noLeidasResp }})</span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
