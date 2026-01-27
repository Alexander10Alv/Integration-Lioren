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
                            <p class="text-purple-100 text-lg">Shopify ↔️ Lioren | Sistema Multi-Cliente</p>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-6xl opacity-20">🚀</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Generales -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-4 justify-center">
                    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500 min-w-[160px]">
                        <div class="flex items-center gap-3">
                            <div class="text-4xl">🔌</div>
                            <div>
                                <p class="text-gray-500 text-xs font-semibold">Integraciones</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_integraciones'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-green-500 min-w-[160px]">
                        <div class="flex items-center gap-3">
                            <div class="text-4xl">📦</div>
                            <div>
                                <p class="text-gray-500 text-xs font-semibold">Productos</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_productos'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-purple-500 min-w-[160px]">
                        <div class="flex items-center gap-3">
                            <div class="text-4xl">🔔</div>
                            <div>
                                <p class="text-gray-500 text-xs font-semibold">Webhooks</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_webhooks'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-yellow-500 min-w-[160px]">
                        <div class="flex items-center gap-3">
                            <div class="text-4xl">📄</div>
                            <div>
                                <p class="text-gray-500 text-xs font-semibold">Boletas</p>
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_boletas'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Integraciones Activas -->
            @if($integraciones->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-8 border border-gray-100">
                <div class="bg-gradient-to-r from-gray-50 to-white p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <span class="text-3xl mr-3">🏪</span>
                        Integraciones Manuales (Admin)
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($integraciones as $integracion)
                        <div class="border rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-600 font-bold">{{ substr($integracion->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800">{{ $integracion->user->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $integracion->shopify_tienda }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        @if($integracion->facturacion_enabled)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✅ Facturación</span>
                                        @endif
                                        @if($integracion->shopify_visibility_enabled)
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">👁️ Visibilidad</span>
                                        @endif
                                        @if($integracion->notas_credito_enabled)
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">🔄 Notas Crédito</span>
                                        @endif
                                        @if($integracion->order_limit_enabled)
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">📊 Límite: {{ $integracion->monthly_order_limit }}</span>
                                        @else
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">♾️ Sin límite</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Última sincronización</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $integracion->ultima_sincronizacion ? $integracion->ultima_sincronizacion->diffForHumans() : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg mb-8">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">⚠️</span>
                    <div>
                        <h3 class="font-bold text-yellow-800">No hay integraciones activas</h3>
                        <p class="text-yellow-700 text-sm mt-1">Configura tu primera integración para comenzar</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Botón para Configurar Nueva Integración -->
            <div class="mb-8">
                <a href="{{ route('integracion.index') }}" class="group block">
                    <div class="relative bg-gradient-to-r from-purple-600 to-indigo-600 overflow-hidden shadow-xl sm:rounded-2xl transition-all duration-300 transform group-hover:-translate-y-1 group-hover:shadow-2xl">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-bl-full"></div>
                        <div class="p-8 relative">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="text-6xl mr-6">🚀</div>
                                    <div>
                                        <h3 class="text-3xl font-bold text-white mb-2">Configurar Nueva Integración</h3>
                                        <p class="text-purple-100 text-lg">
                                            Conecta manualmente una nueva cuenta de Shopify con Lioren
                                        </p>
                                    </div>
                                </div>
                                <svg class="w-8 h-8 text-white group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
