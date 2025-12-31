<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configurar Integración Shopify - Lioren') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    <div class="mb-6">
                        <a href="{{ route('integracion.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
                            ← Volver al Dashboard
                        </a>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-800 mb-2">🔗 Integración Shopify - Lioren</h1>
                    <p class="text-gray-600 mb-6">Módulo de Prueba - Configuración Automática</p>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">ℹ️</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Importante:</strong> Este módulo creará webhooks automáticamente y sincronizará productos. Asegúrate de tener las credenciales correctas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('integracion.procesar') }}" method="POST">
                        @csrf

                        <!-- Shopify Section -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-indigo-600 mb-4 pb-2 border-b-2 border-indigo-100">
                                📦 Credenciales de Shopify
                            </h2>

                            <div class="mb-4">
                                <x-input-label for="shopify_tienda" value="Nombre de Tienda" />
                                <x-text-input 
                                    id="shopify_tienda" 
                                    class="block mt-1 w-full" 
                                    type="text" 
                                    name="shopify_tienda" 
                                    :value="old('shopify_tienda')" 
                                    required 
                                    placeholder="ejemplo.myshopify.com"
                                    pattern="[a-zA-Z0-9\-]+\.myshopify\.com"
                                />
                                <p class="mt-1 text-sm text-gray-500">Formato: tu-tienda.myshopify.com</p>
                                <x-input-error :messages="$errors->get('shopify_tienda')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="shopify_token" value="Access Token" />
                                <x-text-input 
                                    id="shopify_token" 
                                    class="block mt-1 w-full" 
                                    type="password" 
                                    name="shopify_token" 
                                    required 
                                    placeholder="shpat_xxxxxxxxxxxxx"
                                    minlength="20"
                                />
                                <p class="mt-1 text-sm text-gray-500">Token de API de tu app personalizada de Shopify</p>
                                <x-input-error :messages="$errors->get('shopify_token')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="shopify_secret" value="API Secret (para webhooks)" />
                                <x-text-input 
                                    id="shopify_secret" 
                                    class="block mt-1 w-full" 
                                    type="password" 
                                    name="shopify_secret" 
                                    required 
                                    placeholder="shpss_xxxxxxxxxxxxx"
                                    minlength="20"
                                />
                                <p class="mt-1 text-sm text-gray-500">Secret key para validar webhooks de Shopify</p>
                                <x-input-error :messages="$errors->get('shopify_secret')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Lioren Section -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-indigo-600 mb-4 pb-2 border-b-2 border-indigo-100">
                                🏪 Credenciales de Lioren
                            </h2>

                            <div class="mb-4">
                                <x-input-label for="lioren_api_key" value="API Key (Bearer Token)" />
                                <x-text-input 
                                    id="lioren_api_key" 
                                    class="block mt-1 w-full" 
                                    type="password" 
                                    name="lioren_api_key" 
                                    required 
                                    placeholder="tu_api_key_de_lioren"
                                    minlength="10"
                                />
                                <p class="mt-1 text-sm text-gray-500">Token de autenticación de la API de Lioren</p>
                                <x-input-error :messages="$errors->get('lioren_api_key')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Webhook Section -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-indigo-600 mb-4 pb-2 border-b-2 border-indigo-100">
                                🔔 Configuración de Webhooks
                            </h2>

                            <div class="mb-4">
                                <x-input-label for="webhook_url" value="URL del Receptor de Webhooks" />
                                <x-text-input 
                                    id="webhook_url" 
                                    class="block mt-1 w-full" 
                                    type="text" 
                                    name="webhook_url" 
                                    :value="$webhook_url" 
                                    required 
                                    pattern="https?://.+"
                                />
                                <p class="mt-1 text-sm text-gray-500">URL pública donde Shopify enviará los eventos</p>
                                <x-input-error :messages="$errors->get('webhook_url')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Facturación Section -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-indigo-600 mb-4 pb-2 border-b-2 border-indigo-100">
                                📄 Opciones de Facturación
                            </h2>

                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input 
                                            id="facturacion_enabled" 
                                            name="facturacion_enabled" 
                                            type="checkbox" 
                                            value="1"
                                            class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2"
                                        >
                                    </div>
                                    <div class="ml-4 text-sm">
                                        <label for="facturacion_enabled" class="font-bold text-gray-900 text-lg cursor-pointer">
                                            ✅ Habilitar emisión de facturas electrónicas
                                        </label>
                                        <p class="text-gray-700 mt-2">
                                            Al activar esta opción, el sistema podrá procesar tanto <strong>boletas</strong> como <strong>facturas</strong> según lo que elija cada cliente en el checkout de Shopify.
                                        </p>
                                        <div class="mt-3 p-3 bg-white rounded border border-green-300">
                                            <p class="text-xs text-gray-600 font-semibold mb-2">📋 ¿Cómo funciona?</p>
                                            <ul class="text-xs text-gray-600 space-y-1 ml-4">
                                                <li>• <strong>Si está desactivado:</strong> Solo se emitirán boletas para todos los pedidos</li>
                                                <li>• <strong>Si está activado:</strong> El sistema detectará automáticamente si el cliente eligió "Boleta" o "Factura" en Shopify</li>
                                                <li>• Los clientes que elijan factura deberán proporcionar: RUT, Razón Social, Giro y Dirección</li>
                                                <li>• Todo se procesa automáticamente vía webhooks</li>
                                            </ul>
                                        </div>
                                        <div class="mt-3 p-3 bg-yellow-50 rounded border border-yellow-300">
                                            <p class="text-xs text-yellow-800">
                                                <strong>⚠️ Importante:</strong> Debes configurar campos personalizados en tu checkout de Shopify para capturar los datos de factura (RUT, Razón Social, Giro, etc.)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button class="w-full justify-center text-lg py-3">
                                🚀 Conectar y Configurar Integración
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
