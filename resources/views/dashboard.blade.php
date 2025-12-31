<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <style>
        .dashboard-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            transition: all 0.3s ease;
            overflow: visible;
        }
        .dashboard-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .welcome-card {
            background: linear-gradient(135deg, #FFD54F 0%, #FFCA28 100%);
            color: #1a1a1a;
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 10px 25px -5px rgba(255, 202, 40, 0.4);
            margin-bottom: 2rem;
        }
        .welcome-card h3 {
            color: #1a1a1a;
        }
        .welcome-emoji {
            filter: grayscale(0) brightness(1.2) contrast(1.1);
            display: inline-block;
            font-size: 1.5em;
        }
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #FFC107;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateX(4px);
        }
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%);
            border: none;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 0.875rem;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ffca28 0%, #FFC107 100%);
            box-shadow: 0 8px 20px -4px rgba(248, 184, 0, 0.5);
            transform: translateY(-2px);
        }
        .main-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        @media (min-width: 1024px) {
            .main-grid {
                grid-template-columns: 350px 1fr;
            }
        }
        .stats-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        #quick-actions-card {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            padding: 2rem !important;
        }
        .actions-grid-responsive {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 0.5rem !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        @media (max-width: 900px) {
            .actions-grid-responsive {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 480px) {
            .actions-grid-responsive {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <h3 class="text-2xl font-bold mb-2">¡Bienvenido, {{ auth()->user()->name }}! <span class="welcome-emoji">⚡</span></h3>
                <p class="text-lg opacity-80">Panel de administración - Sistema de Integración Shopify</p>
            </div>

            @if(auth()->user()->isAdmin())
                <!-- Main Content Grid -->
                <div class="main-grid">
                    <!-- Left Column - Stats -->
                    <div class="stats-column">
                        <div class="stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold">Total Clientes</p>
                                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\User::role('cliente')->count() }}</p>
                                </div>
                                <div class="bg-yellow-100 p-3 rounded-full">
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold">Integraciones Activas</p>
                                    <p class="text-3xl font-bold text-gray-900 mt-2">3</p>
                                </div>
                                <div class="bg-yellow-100 p-3 rounded-full">
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold">Estado Sistema</p>
                                    <p class="text-3xl font-bold text-green-600 mt-2">Online</p>
                                </div>
                                <div class="bg-green-100 p-3 rounded-full">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Últimas Integraciones -->
                    <div>
                        <div class="dashboard-card">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Últimas Integraciones</h3>
                    <div class="space-y-4">
                        <!-- Integración 1 -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex items-center space-x-4">
                                <div class="bg-yellow-100 p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Shopify → Lioren</p>
                                    <p class="text-sm text-gray-600">Cliente: Empresa Demo S.A.</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-green-600">Activa</p>
                                <p class="text-xs text-gray-500">Hace 2 horas</p>
                            </div>
                        </div>

                        <!-- Integración 2 -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-100 p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Shopify → Bsale</p>
                                    <p class="text-sm text-gray-600">Cliente: Comercial XYZ Ltda.</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-green-600">Activa</p>
                                <p class="text-xs text-gray-500">Hace 5 horas</p>
                            </div>
                        </div>

                        <!-- Integración 3 -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex items-center space-x-4">
                                <div class="bg-purple-100 p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Shopify → Mercado Libre</p>
                                    <p class="text-sm text-gray-600">Cliente: Tienda Online SpA</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-green-600">Activa</p>
                                <p class="text-xs text-gray-500">Hace 1 día</p>
                            </div>
                        </div>

                        <!-- Integración 4 -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex items-center space-x-4">
                                <div class="bg-yellow-100 p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Shopify → Lioren</p>
                                    <p class="text-sm text-gray-600">Cliente: Distribuidora ABC</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-yellow-600">En proceso</p>
                                <p class="text-xs text-gray-500">Hace 2 días</p>
                            </div>
                        </div>
                    </div>
                        </div>
                </div>
            </div>

            <!-- Quick Actions - FUERA del contenedor limitado -->
            <div style="background: white; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); padding: 1.5rem; margin: 0 1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 1.5rem;">Acciones Rápidas</h3>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; width: 100%; box-sizing: border-box;">
                        <a href="{{ route('usuarios.index') }}" style="display: flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); border-radius: 0.5rem; font-weight: 700; font-size: 0.875rem; color: #000; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                            <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Usuarios
                        </a>
                        <a href="{{ route('integracion.index') }}" style="display: flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); border-radius: 0.5rem; font-weight: 700; font-size: 0.875rem; color: #000; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                            <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Integración
                        </a>
                        <a href="{{ route('warehouse.config') }}" style="display: flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); border-radius: 0.5rem; font-weight: 700; font-size: 0.875rem; color: #000; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                            <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Bodegas
                        </a>
                        <a href="{{ route('boletas.index') }}" style="display: flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); border-radius: 0.5rem; font-weight: 700; font-size: 0.875rem; color: #000; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;">
                            <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Boletas
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>
</x-app-layout>
