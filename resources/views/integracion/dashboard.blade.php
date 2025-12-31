<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Integración Shopify - Lioren') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-purple-50 via-white to-indigo-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 overflow-hidden shadow-xl sm:rounded-2xl mb-8">
                <div class="p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-4xl font-bold mb-2">🔗 Dashboard de Integración</h1>
                            <p class="text-purple-100 text-lg">Shopify ↔️ Lioren | Módulo de Prueba</p>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-6xl opacity-20">🚀</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
                
                <!-- Estado de Integración -->
                <a href="{{ route('integracion.estado') }}" class="group block">
                    <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-2xl transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-2xl border-2 border-blue-500">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-600 opacity-10 rounded-bl-full"></div>
                        <div class="p-8 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-6xl">📊</div>
                                <div class="px-4 py-2 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">ESTADO</div>
                            </div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-blue-600 transition-colors">Estado Integración</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Verifica el estado y estadísticas de la integración activa
                            </p>
                            <div class="mt-4 flex items-center text-blue-600 font-semibold">
                                <span>Ver estado</span>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Configurar Integración -->
                <a href="{{ route('integracion.index') }}" class="group block">
                    <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-2xl transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-2xl border border-gray-100">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-400 to-purple-600 opacity-10 rounded-bl-full"></div>
                        <div class="p-8 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-6xl">🚀</div>
                                <div class="px-4 py-2 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">PRINCIPAL</div>
                            </div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-purple-600 transition-colors">Configurar Integración</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Formulario principal para conectar Shopify con Lioren y crear webhooks automáticamente
                            </p>
                            <div class="mt-4 flex items-center text-purple-600 font-semibold">
                                <span>Comenzar</span>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Ver Productos -->
                <a href="{{ route('integracion.productos') }}" class="group block">
                    <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-2xl transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-2xl border border-gray-100">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-400 to-indigo-600 opacity-10 rounded-bl-full"></div>
                        <div class="p-8 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-6xl">📦</div>
                                <div class="px-4 py-2 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full">PRODUCTOS</div>
                            </div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-indigo-600 transition-colors">Ver Productos</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Visualiza todos los productos sincronizados entre Shopify y Lioren
                            </p>
                            <div class="mt-4 flex items-center text-indigo-600 font-semibold">
                                <span>Ver productos</span>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Ver Productos en Lioren (API) -->
                <a href="{{ route('integracion.productos-lioren') }}" class="group block">
                    <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-2xl transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-2xl border-2 border-green-500">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400 to-green-600 opacity-10 rounded-bl-full"></div>
                        <div class="p-8 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-6xl">🎯</div>
                                <div class="px-4 py-2 bg-green-100 text-green-700 text-xs font-bold rounded-full">PRUEBA REAL</div>
                            </div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-green-600 transition-colors">Productos en Lioren</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Consulta los productos REALES en tu cuenta de Lioren vía API
                            </p>
                            <div class="mt-4 flex items-center text-green-600 font-semibold">
                                <span>Ver en Lioren</span>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Emitir Boletas -->
                <a href="{{ route('integracion.boletas-form') }}" class="group block">
                    <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-2xl transition-all duration-300 transform group-hover:-translate-y-2 group-hover:shadow-2xl border-2 border-yellow-500">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-yellow-400 to-yellow-600 opacity-10 rounded-bl-full"></div>
                        <div class="p-8 relative">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-6xl">📄</div>
                                <div class="px-4 py-2 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">NUEVO</div>
                            </div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-yellow-600 transition-colors">Emitir Boletas</h3>
                            <p class="text-gray-600 leading-relaxed">
                                Emite boletas electrónicas directamente al SII vía Lioren
                            </p>
                            <div class="mt-4 flex items-center text-yellow-600 font-semibold">
                                <span>Emitir ahora</span>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

            </div>

            <!-- Características -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
                <div class="bg-gradient-to-r from-gray-50 to-white p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <span class="text-3xl mr-3">📋</span>
                        Características del Sistema
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Validación automática de credenciales</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Creación automática de 4 webhooks</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Sincronización inicial de productos</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Sincronización en tiempo real</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Actualización de productos</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Sincronización de inventario</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Sistema de logs detallado</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-4">✓</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Validación HMAC de webhooks</span>
                                <span class="text-green-600 text-xs font-medium">Sistema activo</span>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border border-yellow-200 hover:shadow-md transition-shadow">
                            <div class="flex-shrink-0 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-white font-bold mr-4">📄</div>
                            <div class="flex-1">
                                <span class="text-gray-800 font-semibold block">Emisión de boletas electrónicas</span>
                                <span class="text-yellow-600 text-xs font-medium">Nuevo - Directo al SII</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de Resetear Integración -->
            <div class="mt-8">
                <a href="{{ route('integracion.resetear') }}" class="group block">
                    <div class="relative bg-white overflow-hidden shadow-xl sm:rounded-2xl transition-all duration-300 transform group-hover:-translate-y-1 group-hover:shadow-2xl border-2 border-red-300 hover:border-red-500">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-400 to-red-600 opacity-10 rounded-bl-full"></div>
                        <div class="p-6 relative">
                            <div class="flex items-center">
                                <div class="text-4xl mr-4">🔄</div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-red-600 transition-colors">Resetear Integración</h3>
                                    <p class="text-gray-600 text-sm mt-1">
                                        Elimina la configuración actual para probar desde cero
                                    </p>
                                </div>
                                <svg class="w-6 h-6 text-red-500 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Flujo de Trabajo -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="bg-gradient-to-r from-gray-50 to-white p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <span class="text-3xl mr-3">🔄</span>
                        Flujo de Trabajo
                    </h2>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <!-- Línea conectora vertical -->
                        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-purple-300 via-indigo-300 to-green-300"></div>
                        
                        <ol class="space-y-6 relative">
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-full font-bold mr-4 text-lg shadow-lg z-10 ring-4 ring-purple-100">1</div>
                                <div class="flex-1 pt-2">
                                    <h3 class="text-lg font-bold text-gray-800 mb-1">Configura la integración</h3>
                                    <p class="text-gray-600">Completa el formulario con tus credenciales de Shopify y Lioren</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-full font-bold mr-4 text-lg shadow-lg z-10 ring-4 ring-indigo-100">2</div>
                                <div class="flex-1 pt-2">
                                    <h3 class="text-lg font-bold text-gray-800 mb-1">Validación automática</h3>
                                    <p class="text-gray-600">El sistema verifica que las credenciales sean correctas</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-full font-bold mr-4 text-lg shadow-lg z-10 ring-4 ring-blue-100">3</div>
                                <div class="flex-1 pt-2">
                                    <h3 class="text-lg font-bold text-gray-800 mb-1">Configuración automática</h3>
                                    <p class="text-gray-600">Se crean webhooks y se sincronizan productos iniciales</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gradient-to-br from-green-500 to-green-600 text-white rounded-full font-bold mr-4 text-lg shadow-lg z-10 ring-4 ring-green-100">4</div>
                                <div class="flex-1 pt-2">
                                    <h3 class="text-lg font-bold text-gray-800 mb-1">Monitoreo en tiempo real</h3>
                                    <p class="text-gray-600">Visualiza todos los eventos en los logs del sistema</p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
