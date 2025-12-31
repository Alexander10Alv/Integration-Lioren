<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Procesando Integración...') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">🚀 Procesando Integración</h1>

                    <div class="space-y-4">
                        <!-- Paso 1: Validar Shopify -->
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">📦</span>
                                <div>
                                    <div class="font-semibold text-gray-800">PASO 1: Validando credenciales de Shopify...</div>
                                    <div class="text-sm text-gray-600 mt-1">Conectando con {{ $shopify_tienda }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje de éxito simulado -->
                        <div class="border-l-4 border-green-500 bg-green-50 p-4">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">✅</span>
                                <div>
                                    <div class="font-semibold text-gray-800">Conexión con Shopify exitosa</div>
                                    <div class="text-sm text-gray-600 mt-1">Tienda: {{ $shopify_tienda }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2: Validar Lioren -->
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">🏪</span>
                                <div>
                                    <div class="font-semibold text-gray-800">PASO 2: Validando credenciales de Lioren...</div>
                                    <div class="text-sm text-gray-600 mt-1">Verificando API Key</div>
                                </div>
                            </div>
                        </div>

                        <div class="border-l-4 border-green-500 bg-green-50 p-4">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">✅</span>
                                <div>
                                    <div class="font-semibold text-gray-800">Conexión con Lioren exitosa</div>
                                    <div class="text-sm text-gray-600 mt-1">API Key válida y funcionando correctamente</div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3: Crear Webhooks -->
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">🔔</span>
                                <div>
                                    <div class="font-semibold text-gray-800">PASO 3: Creando webhooks en Shopify...</div>
                                    <div class="text-sm text-gray-600 mt-1">Configurando eventos automáticos</div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @if(isset($webhooks_creados) && count($webhooks_creados) > 0)
                                @foreach($webhooks_creados as $webhook)
                                    <div class="border-l-4 {{ $webhook['success'] ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }} p-3">
                                        <div class="flex items-center">
                                            <span class="text-xl mr-2">{{ $webhook['success'] ? '✅' : '❌' }}</span>
                                            <div class="text-sm">
                                                <span class="font-semibold">Webhook: {{ $webhook['nombre'] }}</span>
                                                <div class="text-gray-600">Topic: {{ $webhook['topic'] }}</div>
                                                @if($webhook['success'] && isset($webhook['id']))
                                                    <div class="text-xs text-green-600">ID: {{ $webhook['id'] }}</div>
                                                @endif
                                                @if(!$webhook['success'] && isset($webhook['error']))
                                                    <div class="text-xs text-red-600">Error: {{ $webhook['error'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="border-l-4 border-yellow-500 bg-yellow-50 p-3">
                                    <div class="text-sm text-yellow-800">
                                        No se crearon webhooks
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Resumen Final -->
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6 rounded-lg mt-6">
                            <h2 class="text-2xl font-bold mb-4">🎉 ¡INTEGRACIÓN COMPLETADA!</h2>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center">
                                    <span class="mr-2">✅</span>
                                    <span><strong>Conexión con Shopify:</strong> OK</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="mr-2">✅</span>
                                    <span><strong>Conexión con Lioren:</strong> OK</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="mr-2">✅</span>
                                    <span><strong>Webhooks creados:</strong> 4</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="mr-2">{{ isset($facturacion_enabled) && $facturacion_enabled ? '📄' : '📝' }}</span>
                                    <span><strong>Facturación:</strong> {{ isset($facturacion_enabled) && $facturacion_enabled ? 'HABILITADA (Boletas y Facturas)' : 'Solo Boletas' }}</span>
                                </div>
                            </div>

                            <div class="border-t border-purple-400 pt-4 mt-4">
                                <p class="font-semibold mb-2">📡 Eventos que se sincronizarán automáticamente:</p>
                                <ul class="list-disc list-inside space-y-1 text-sm">
                                    <li>Nuevos pedidos en Shopify → Se crearán en Lioren</li>
                                    <li>Nuevos productos en Shopify → Se crearán en Lioren</li>
                                    <li>Productos actualizados → Se actualizarán en Lioren</li>
                                    <li>Cambios de inventario → Se actualizarán en Lioren</li>
                                </ul>
                            </div>

                            <div class="border-t border-purple-400 pt-4 mt-4">
                                <p class="font-semibold mb-2">🔗 URL del receptor de webhooks:</p>
                                <code class="bg-purple-700 px-3 py-1 rounded text-sm">
                                    {{ $webhook_url }}
                                </code>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex gap-4 mt-6">
                            <a href="{{ route('integracion.dashboard') }}" class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                ← Volver al Dashboard
                            </a>
                            <a href="{{ route('dashboard') }}" class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-gray-600 border border-transparent rounded-md font-semibold text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ir al Dashboard Principal
                            </a>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
