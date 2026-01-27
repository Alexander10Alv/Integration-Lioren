<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configurar Credenciales de Integración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">🔐 Credenciales de Integración</h3>
                    <p class="text-gray-600 mb-4">
                        Para completar la activación de tu plan, necesitamos que ingreses las credenciales de tu tienda Shopify y tu cuenta Lioren.
                        Una vez que las ingreses, nuestro equipo procederá con la conexión.
                    </p>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="text-2xl">ℹ️</span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Importante:</strong> Asegúrate de tener acceso a tu panel de administración de Shopify y tu cuenta de Lioren para obtener estas credenciales.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @forelse($solicitudes as $solicitud)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-gray-800">
                                    {{ $solicitud->plan->nombre }}
                                </h4>
                                <p class="text-sm text-gray-600">
                                    {{ $solicitud->plan->empresa->nombre }}
                                </p>
                            </div>
                            <div>
                                @if($solicitud->tieneCredencialesCompletas())
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ✅ Credenciales Completas
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        ⏳ Pendiente
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($solicitud->integracion_conectada)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <p class="text-green-800 font-semibold">
                                    🎉 ¡Integración conectada exitosamente!
                                </p>
                                <p class="text-sm text-green-700 mt-1">
                                    Conectada el {{ $solicitud->fecha_conexion->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @else
                            <form action="{{ route('cliente.solicitudes.guardar-credenciales', $solicitud) }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <!-- Shopify Section -->
                                <div class="border-t pt-4">
                                    <h5 class="text-md font-bold text-indigo-600 mb-3">📦 Credenciales de Shopify</h5>

                                    <div class="mb-4">
                                        <label for="tienda_shopify_{{ $solicitud->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            Nombre de Tienda *
                                        </label>
                                        <input 
                                            type="text" 
                                            id="tienda_shopify_{{ $solicitud->id }}"
                                            name="tienda_shopify" 
                                            value="{{ old('tienda_shopify', $solicitud->tienda_shopify) }}"
                                            placeholder="tu-tienda.myshopify.com"
                                            pattern="[a-zA-Z0-9\-]+\.myshopify\.com"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">Formato: tu-tienda.myshopify.com</p>
                                        @error('tienda_shopify')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="access_token_{{ $solicitud->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            Access Token *
                                        </label>
                                        <input 
                                            type="password" 
                                            id="access_token_{{ $solicitud->id }}"
                                            name="access_token" 
                                            value="{{ old('access_token', $solicitud->access_token) }}"
                                            placeholder="shpat_xxxxxxxxxxxxx"
                                            minlength="20"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">Token de API de tu app personalizada de Shopify</p>
                                        @error('access_token')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="api_secret_{{ $solicitud->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            API Secret (para webhooks) *
                                        </label>
                                        <input 
                                            type="password" 
                                            id="api_secret_{{ $solicitud->id }}"
                                            name="api_secret" 
                                            value="{{ old('api_secret', $solicitud->api_secret) }}"
                                            placeholder="shpss_xxxxxxxxxxxxx"
                                            minlength="20"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">Secret key para validar webhooks de Shopify</p>
                                        @error('api_secret')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Lioren Section -->
                                <div class="border-t pt-4">
                                    <h5 class="text-md font-bold text-indigo-600 mb-3">🏪 Credenciales de Lioren</h5>

                                    <div class="mb-4">
                                        <label for="api_key_{{ $solicitud->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            API Key (Bearer Token) *
                                        </label>
                                        <input 
                                            type="password" 
                                            id="api_key_{{ $solicitud->id }}"
                                            name="api_key" 
                                            value="{{ old('api_key', $solicitud->api_key) }}"
                                            placeholder="tu_api_key_de_lioren"
                                            minlength="10"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">Token de autenticación de la API de Lioren</p>
                                        @error('api_key')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="telefono_{{ $solicitud->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            Teléfono de Contacto (opcional)
                                        </label>
                                        <input 
                                            type="text" 
                                            id="telefono_{{ $solicitud->id }}"
                                            name="telefono" 
                                            value="{{ old('telefono', $solicitud->telefono) }}"
                                            placeholder="+56 9 1234 5678"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                        @error('telefono')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4">
                                    <button 
                                        type="submit"
                                        class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                                    >
                                        💾 Guardar Credenciales
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-gray-600">No tienes solicitudes pendientes de configuración.</p>
                        <a href="{{ route('cliente.planes') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 font-semibold">
                            Ver Planes Disponibles →
                        </a>
                    </div>
                </div>
            @endforelse

            <!-- Ayuda -->
            <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h4 class="text-lg font-bold text-gray-800 mb-3">❓ ¿Necesitas ayuda?</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><strong>Para obtener tus credenciales de Shopify:</strong></p>
                        <ol class="list-decimal list-inside ml-4 space-y-1">
                            <li>Ve a tu panel de administración de Shopify</li>
                            <li>Configuración → Apps y canales de venta → Desarrollar apps</li>
                            <li>Crea una app personalizada con los permisos necesarios</li>
                            <li>Copia el Access Token y el API Secret</li>
                        </ol>
                        <p class="mt-3"><strong>Para obtener tu API Key de Lioren:</strong></p>
                        <ol class="list-decimal list-inside ml-4 space-y-1">
                            <li>Ingresa a tu cuenta de Lioren</li>
                            <li>Ve a Configuración → API</li>
                            <li>Copia tu Bearer Token</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
