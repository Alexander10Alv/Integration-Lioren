<style>
    .navbar-custom {
        background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
        border-bottom: 2px solid rgba(248, 184, 0, 0.3);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
    }
    .nav-logo {
        height: 40px;
        width: auto;
        transition: transform 0.3s ease;
    }
    .nav-logo:hover {
        transform: scale(1.05);
    }
    .nav-link-custom {
        color: #d1d5db;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .nav-link-custom:hover {
        color: #FFC107;
        background: rgba(248, 184, 0, 0.1);
    }
    .nav-link-custom.active {
        color: #FFC107;
        background: rgba(248, 184, 0, 0.15);
        border-bottom: 2px solid #FFC107;
    }
    .user-dropdown-btn {
        color: #d1d5db;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.05);
    }
    .user-dropdown-btn:hover {
        color: #FFC107;
        background: rgba(248, 184, 0, 0.1);
    }
    .hamburger-btn {
        color: #d1d5db;
        transition: color 0.3s ease;
    }
    .hamburger-btn:hover {
        color: #FFC107;
    }
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.8;
            transform: scale(1.05);
        }
    }
</style>

<nav x-data="{ open: false }" class="navbar-custom">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ Auth::user()->hasRole('admin') ? route('dashboard') : route('cliente.dashboard') }}">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="nav-logo">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    @role('admin')
                        <!-- Menú para Admin -->
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('integracion.index')" :active="request()->routeIs('integracion.*')">
                            {{ __('Solicitudes') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.chats')" :active="request()->routeIs('admin.chats')" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            {{ __('Chats') }}
                            <span id="unreadBadge" style="display: none; background: #ef4444; color: white; border-radius: 9999px; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 700; min-width: 20px; text-align: center; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5); animation: pulse 2s infinite;">
                            </span>
                        </x-nav-link>

                        <x-nav-link :href="route('planes.index')" :active="request()->routeIs('planes.*')">
                            {{ __('Planes') }}
                        </x-nav-link>

                        <x-nav-link :href="route('boletas.index')" :active="request()->routeIs('boletas.*')">
                            {{ __('Boletas') }}
                        </x-nav-link>
                    @endrole
                    
                    @role('cliente')
                        <!-- Menú para Cliente -->
                        <x-nav-link :href="route('cliente.dashboard')" :active="request()->routeIs('cliente.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('cliente.planes')" :active="request()->routeIs('cliente.planes')">
                            {{ __('Planes') }}
                        </x-nav-link>

                        <x-nav-link :href="route('cliente.chats')" :active="request()->routeIs('cliente.chats')">
                            {{ __('Chats') }}
                        </x-nav-link>

                        <x-nav-link :href="route('cliente.estados-solicitud')" :active="request()->routeIs('cliente.estados-solicitud')">
                            {{ __('Estados de solicitud') }}
                        </x-nav-link>

                        <x-nav-link :href="route('cliente.planes-activos')" :active="request()->routeIs('cliente.planes-activos')">
                            {{ __('Planes activos') }}
                        </x-nav-link>

                        <x-nav-link :href="route('cliente.facturas')" :active="request()->routeIs('cliente.facturas')">
                            {{ __('Facturas') }}
                        </x-nav-link>
                    @endrole
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="user-dropdown-btn inline-flex items-center border border-transparent text-sm leading-4 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="hamburger-btn inline-flex items-center justify-center p-2 rounded-md focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden" style="background: rgba(0, 0, 0, 0.95);">
        <div class="pt-2 pb-3 space-y-1">
            @role('admin')
                <!-- Menú Responsive para Admin -->
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('integracion.index')" :active="request()->routeIs('integracion.*')">
                    {{ __('Solicitudes') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.chats')" :active="request()->routeIs('admin.chats')">
                    {{ __('Chats') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('planes.index')" :active="request()->routeIs('planes.*')">
                    {{ __('Planes') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('boletas.index')" :active="request()->routeIs('boletas.*')">
                    {{ __('Boletas') }}
                </x-responsive-nav-link>
            @endrole
            
            @role('cliente')
                <!-- Menú Responsive para Cliente -->
                <x-responsive-nav-link :href="route('cliente.dashboard')" :active="request()->routeIs('cliente.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('cliente.planes')" :active="request()->routeIs('cliente.planes')">
                    {{ __('Planes') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('cliente.estados-solicitud')" :active="request()->routeIs('cliente.estados-solicitud')">
                    {{ __('Estados de solicitud') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('cliente.planes-activos')" :active="request()->routeIs('cliente.planes-activos')">
                    {{ __('Planes activos') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('cliente.facturas')" :active="request()->routeIs('cliente.facturas')">
                    {{ __('Facturas') }}
                </x-responsive-nav-link>
            @endrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-yellow-500/30">
            <div class="px-4">
                <div class="font-medium text-base text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
