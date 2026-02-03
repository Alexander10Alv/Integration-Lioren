<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Management Routes - Only accessible by admin role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('usuarios', UserController::class);
});

// Clientes CRUD - Only accessible by admin role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('clientes', App\Http\Controllers\AdminClienteController::class);
});

// Planes CRUD - Only accessible by admin role
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('planes', App\Http\Controllers\PlanController::class);
});

// Integración Shopify-Lioren Routes - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('integracion')->name('integracion.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\IntegracionController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [App\Http\Controllers\IntegracionController::class, 'index'])->name('index');
    Route::post('/procesar', [App\Http\Controllers\IntegracionController::class, 'procesar'])->name('procesar');
    Route::get('/productos', [App\Http\Controllers\IntegracionController::class, 'productos'])->name('productos');
    Route::get('/productos-lioren', [App\Http\Controllers\IntegracionController::class, 'productosLioren'])->name('productos-lioren');
    Route::get('/estado', [App\Http\Controllers\IntegracionController::class, 'estado'])->name('estado');
    Route::get('/resetear', [App\Http\Controllers\IntegracionController::class, 'confirmarReset'])->name('resetear');
    Route::delete('/resetear', [App\Http\Controllers\IntegracionController::class, 'resetearIntegracion'])->name('resetear.ejecutar');

    // Configuración de Bodegas
    Route::get('/bodegas', [App\Http\Controllers\WarehouseConfigController::class, 'index'])->name('bodegas');
    Route::get('/bodegas/config', [App\Http\Controllers\WarehouseConfigController::class, 'getConfig'])->name('bodegas.config');
    Route::get('/bodegas/lioren', [App\Http\Controllers\WarehouseConfigController::class, 'getLiorenBodegas'])->name('bodegas.lioren');
    Route::get('/bodegas/shopify-locations', [App\Http\Controllers\WarehouseConfigController::class, 'getShopifyLocations'])->name('bodegas.shopify');
    Route::post('/bodegas/configure-simple', [App\Http\Controllers\WarehouseConfigController::class, 'configureSimple'])->name('bodegas.configure-simple');
    Route::post('/bodegas/configure-advanced', [App\Http\Controllers\WarehouseConfigController::class, 'configureAdvanced'])->name('bodegas.configure-advanced');
    Route::post('/bodegas/modo', [App\Http\Controllers\WarehouseConfigController::class, 'setMode'])->name('bodegas.modo');
    Route::post('/bodegas/mapeo', [App\Http\Controllers\WarehouseConfigController::class, 'saveMapping'])->name('bodegas.mapeo');
    Route::delete('/bodegas/mapeo/{locationId}', [App\Http\Controllers\WarehouseConfigController::class, 'deleteMapping'])->name('bodegas.delete');
});

// Boletas Routes - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('boletas')->name('boletas.')->group(function () {
    Route::get('/', [App\Http\Controllers\IntegracionController::class, 'boletas'])->name('index');
    Route::get('/emitir', [App\Http\Controllers\IntegracionController::class, 'boletasForm'])->name('form');
    Route::post('/emitir', [App\Http\Controllers\IntegracionController::class, 'emitirBoleta'])->name('emitir');
});

// Notas de Crédito Routes - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('notas-credito')->name('notas-credito.')->group(function () {
    Route::get('/', [App\Http\Controllers\IntegracionController::class, 'notasCredito'])->name('index');
    Route::get('/{id}/pdf', [App\Http\Controllers\IntegracionController::class, 'notaCreditoPdf'])->name('pdf');
    Route::get('/{id}/xml', [App\Http\Controllers\IntegracionController::class, 'notaCreditoXml'])->name('xml');
});

// Configuración de Bodegas/Locations - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/config', [App\Http\Controllers\WarehouseConfigController::class, 'index'])->name('config');
    Route::post('/config/simple', [App\Http\Controllers\WarehouseConfigController::class, 'configureSimple'])->name('config.simple');
    Route::post('/config/advanced', [App\Http\Controllers\WarehouseConfigController::class, 'configureAdvanced'])->name('config.advanced');
    Route::post('/config/mapping', [App\Http\Controllers\WarehouseConfigController::class, 'createMapping'])->name('config.mapping');
    Route::delete('/config/mapping/{locationId}', [App\Http\Controllers\WarehouseConfigController::class, 'deleteMapping'])->name('config.mapping.delete');
    Route::get('/config/refresh-bodegas', [App\Http\Controllers\WarehouseConfigController::class, 'refreshBodegas'])->name('config.refresh');
});

// Boletas PDF/XML - SOLO ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/boletas/{id}/pdf', [App\Http\Controllers\IntegracionController::class, 'boletaPdf'])->name('boletas.pdf');
    Route::get('/boletas/{id}/xml', [App\Http\Controllers\IntegracionController::class, 'boletaXml'])->name('boletas.xml');
});

// Rutas para CLIENTES
Route::middleware(['auth', 'role:cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ClienteController::class, 'dashboard'])->name('dashboard');
    Route::get('/planes', [App\Http\Controllers\ClienteController::class, 'planes'])->name('planes');
    Route::get('/estados-solicitud', [App\Http\Controllers\ClienteController::class, 'estadosSolicitud'])->name('estados-solicitud');
    Route::get('/planes-activos', [App\Http\Controllers\ClienteController::class, 'planesActivos'])->name('planes-activos');
    Route::get('/facturas', [App\Http\Controllers\ClienteController::class, 'facturas'])->name('facturas');

    // Suscripciones y Pagos
    Route::get('/suscripciones', [App\Http\Controllers\SuscripcionController::class, 'index'])->name('suscripciones');
    Route::get('/suscripciones/{suscripcion}/renovar', [App\Http\Controllers\SuscripcionController::class, 'renovar'])->name('suscripciones.renovar');
    Route::delete('/suscripciones/{suscripcion}/cancelar', [App\Http\Controllers\SuscripcionController::class, 'cancelar'])->name('suscripciones.cancelar');

    // Chats
    Route::get('/chats', [App\Http\Controllers\ChatController::class, 'index'])->name('chats');
    Route::post('/chats', [App\Http\Controllers\ChatController::class, 'store'])->name('chats.store');

    // Solicitudes
    Route::post('/solicitudes', [App\Http\Controllers\SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::get('/solicitudes/{solicitud}/config', [App\Http\Controllers\SolicitudController::class, 'getConfig'])->name('solicitudes.getConfig');
    Route::post('/solicitudes/{solicitud}/config', [App\Http\Controllers\SolicitudController::class, 'updateConfig'])->name('solicitudes.updateConfig');
    
    // Credenciales de Integración (Cliente)
    Route::get('/solicitudes/credenciales', [App\Http\Controllers\SolicitudController::class, 'credenciales'])->name('solicitudes.credenciales');
    Route::get('/solicitudes/{solicitud}/credenciales', [App\Http\Controllers\SolicitudController::class, 'credenciales'])->name('solicitudes.credenciales-id');
    Route::put('/solicitudes/{solicitud}/credenciales', [App\Http\Controllers\SolicitudController::class, 'guardarCredenciales'])->name('solicitudes.guardar-credenciales');
    
    // Shopify OAuth 2.0 Routes
    Route::post('/shopify/oauth/iniciar', [App\Http\Controllers\ShopifyOAuthController::class, 'iniciarOAuth'])->name('shopify.oauth.iniciar');
});

// Shopify OAuth Callback (sin middleware de rol porque Shopify redirige aquí)
Route::middleware(['auth'])->group(function () {
    Route::get('/shopify/oauth/callback', [App\Http\Controllers\ShopifyOAuthController::class, 'handleCallback'])->name('shopify.oauth.callback');
});

// Rutas de Chat (compartidas entre admin y cliente)
Route::middleware(['auth'])->group(function () {
    Route::get('/chats/{chat}', [App\Http\Controllers\ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chat}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chats.sendMessage');
    Route::get('/chats/{chat}/new-messages', [App\Http\Controllers\ChatController::class, 'getNewMessages'])->name('chats.getNewMessages');
    Route::post('/chats/{chat}/close', [App\Http\Controllers\ChatController::class, 'close'])->name('chats.close');
});

// Rutas de Chat para ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/chats', [App\Http\Controllers\ChatController::class, 'adminIndex'])->name('chats');
    Route::get('/chats/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chats.unreadCount');
    Route::get('/solicitudes', [App\Http\Controllers\SolicitudController::class, 'index'])->name('solicitudes');
    Route::get('/solicitudes/{solicitud}', [App\Http\Controllers\SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::post('/solicitudes/{solicitud}/estado', [App\Http\Controllers\SolicitudController::class, 'updateEstado'])->name('solicitudes.updateEstado');
    
    // Solicitudes Pendientes de Conexión (Admin)
    Route::get('/solicitudes-pendientes-conexion', [App\Http\Controllers\SolicitudController::class, 'pendientesConexion'])->name('solicitudes.pendientes-conexion');
    Route::post('/solicitudes/{solicitud}/conectar', [App\Http\Controllers\SolicitudController::class, 'conectarIntegracion'])->name('solicitudes.conectar');
    Route::post('/solicitudes/{solicitud}/rechazar', [App\Http\Controllers\SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');
    
    // Suscripciones Admin
    Route::get('/suscripciones', [App\Http\Controllers\SuscripcionController::class, 'admin'])->name('suscripciones');
});

// Flow Payment Routes
Route::prefix('flow')->name('flow.')->group(function () {
    // Ruta de prueba simple
    Route::get('/debug-payment', function () {
        $controller = new App\Http\Controllers\FlowController();

        $params = [
            'apiKey' => config('flow.api_key'),
            'commerceOrder' => 'DEBUG_' . time(),
            'subject' => 'Test Debug',
            'currency' => 'CLP',
            'amount' => 1000,
            'email' => 'elianfa3000@gmail.com',
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
        ];

        // Usar el método del controlador
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('signParams');
        $method->setAccessible(true);
        $signature = $method->invoke($controller, $params);
        $params['s'] = $signature;

        try {
            $response = Http::withoutVerifying()->asForm()->post(config('flow.api_url') . '/payment/create', $params);

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
                'params_sent' => $params,
                'api_url' => config('flow.api_url'),
                'environment' => config('flow.environment'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'params_sent' => $params,
            ], 500);
        }
    })->name('debug-payment');

    // Rutas públicas (sin autenticación)
    Route::match(['GET', 'POST'], '/return', [App\Http\Controllers\FlowController::class, 'returnFromFlow'])->name('return');
    Route::post('/confirmation', [App\Http\Controllers\FlowController::class, 'confirmationWebhook'])->name('confirmation');

    // Ruta de prueba temporal sin autenticación
    Route::get('/test-payment', function () {
        $apiKey = config('flow.api_key');
        $secretKey = config('flow.secret_key');
        $apiUrl = config('flow.api_url');

        // Parámetros exactos según documentación
        $params = [
            'apiKey' => $apiKey,
            'commerceOrder' => uniqid('ORDER-'),
            'subject' => 'Pago de prueba',
            'currency' => 'CLP',
            'amount' => 1000,
            'email' => auth()->user()->email ?? 'test@example.com',
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
            'optional' => json_encode([
                'test' => 'value'
            ]),
        ];

        // Firmar parámetros con HMAC SHA256
        ksort($params);
        $toSign = '';
        foreach ($params as $key => $value) {
            $toSign .= $key . $value;
        }
        $signature = hash_hmac('sha256', $toSign, $secretKey);
        $params['s'] = $signature;

        try {
            $response = Http::asForm()->post("{$apiUrl}/payment/create", $params);

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'redirect_url' => $response->successful() ? ($response->json()['url'] ?? null) : null,
                'payment_data_sent' => $params,
                'api_url' => $apiUrl,
                'environment' => config('flow.environment'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'payment_data_sent' => $params,
                'api_url' => $apiUrl,
                'environment' => config('flow.environment'),
            ], 500);
        }
    })->name('test-payment');

    // Rutas protegidas
    Route::middleware('auth')->group(function () {
        Route::get('/payment-form', [App\Http\Controllers\FlowController::class, 'showPaymentForm'])->name('payment-form');
        Route::post('/create-payment', [App\Http\Controllers\FlowController::class, 'createPayment'])->name('create-payment');
        Route::post('/create-plan-payment', [App\Http\Controllers\FlowController::class, 'createPlanPayment'])->name('create-plan-payment');
    });
});

// Payment result pages
Route::middleware('auth')->group(function () {
    Route::get('/payment/success', function () {
        return view('flow.success');
    })->name('payment.success');

    Route::get('/payment/error', function () {
        return view('flow.error');
    })->name('payment.error');

    Route::get('/payment/pending', function () {
        return view('flow.pending');
    })->name('payment.pending');
});

// Webhook receiver - Public route (no auth required)
Route::post('/integracion/webhook-receiver', [App\Http\Controllers\IntegracionController::class, 'webhookReceiver'])->name('integracion.webhook');

// Shopify GDPR Webhooks - Public routes (no auth required)
Route::post('/webhooks/customers/data_request', [App\Http\Controllers\ShopifyGdprController::class, 'customersDataRequest'])->name('webhooks.customers.data_request');
Route::post('/webhooks/customers/redact', [App\Http\Controllers\ShopifyGdprController::class, 'customersRedact'])->name('webhooks.customers.redact');
Route::post('/webhooks/shop/redact', [App\Http\Controllers\ShopifyGdprController::class, 'shopRedact'])->name('webhooks.shop.redact');

require __DIR__ . '/auth.php';

// Ruta de prueba temporal para Flow
Route::get('/test-flow', function () {
    $apiKey = config('flow.api_key');
    $secretKey = config('flow.secret_key');

    // Test básico de conexión con parámetros mínimos
    $url = 'https://www.flow.cl/api/payment/create';

    // Probar con diferentes combinaciones de parámetros
    $testCases = [
        'minimal' => [
            'apiKey' => $apiKey,
            'commerceOrder' => 'TEST_' . time(),
            'subject' => 'Test',
            'amount' => 1000,
            'email' => 'test@example.com',
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
        ],
        'with_currency' => [
            'apiKey' => $apiKey,
            'commerceOrder' => 'TEST_' . time(),
            'subject' => 'Test',
            'currency' => 'CLP',
            'amount' => 1000,
            'email' => 'test@example.com',
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
        ],
        'with_payment_method' => [
            'apiKey' => $apiKey,
            'commerceOrder' => 'TEST_' . time(),
            'subject' => 'Test',
            'currency' => 'CLP',
            'amount' => 1000,
            'email' => 'test@example.com',
            'paymentMethod' => 1,
            'urlConfirmation' => route('flow.confirmation'),
            'urlReturn' => route('flow.return'),
        ]
    ];

    $results = [];

    foreach ($testCases as $testName => $data) {
        // Ordenar los datos alfabéticamente
        ksort($data);

        // Crear firma
        $toSign = '';
        foreach ($data as $key => $value) {
            $toSign .= $key . $value;
        }
        $toSign .= $secretKey;
        $signature = hash('sha256', $toSign);
        $data['s'] = $signature;

        try {
            $response = Http::withoutVerifying()->post($url, $data);
            $results[$testName] = [
                'status' => $response->status(),
                'body' => $response->json(),
                'params_count' => count($data) - 1, // -1 para excluir la firma
            ];
        } catch (\Exception $e) {
            $results[$testName] = [
                'error' => $e->getMessage(),
                'params_count' => count($data) - 1,
            ];
        }
    }

    return response()->json([
        'test_results' => $results,
        'credentials' => [
            'api_key' => substr($apiKey, 0, 10) . '...',
            'has_secret' => !empty($secretKey),
        ]
    ]);
});
