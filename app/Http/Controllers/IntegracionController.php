<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\ProductSyncService;
use App\Services\WebhookSyncService;

class IntegracionController extends Controller
{
    /**
     * Mostrar el dashboard de integración
     */
    public function dashboard()
    {
        return view('integracion.dashboard');
    }

    /**
     * Mostrar el formulario de configuración
     */
    public function index()
    {
        $webhook_url = url('/integracion/webhook-receiver');
        return view('integracion.index', compact('webhook_url'));
    }

    /**
     * Procesar la integración
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'shopify_tienda' => 'required|string',
            'shopify_token' => 'required|string|min:20',
            'shopify_secret' => 'required|string|min:20',
            'lioren_api_key' => 'required|string|min:10',
            'webhook_url' => 'required|url',
            'facturacion_enabled' => 'nullable|boolean',
            'shopify_visibility_enabled' => 'nullable|boolean',
            'notas_credito_enabled' => 'nullable|boolean',
            'no_order_limit' => 'nullable|boolean',
            'monthly_order_limit' => 'nullable|integer|min:1',
        ]);

        $facturacionEnabled = $request->has('facturacion_enabled') && $request->facturacion_enabled == '1';
        $shopifyVisibilityEnabled = $request->has('shopify_visibility_enabled') && $request->shopify_visibility_enabled == '1';
        $notasCreditoEnabled = $request->has('notas_credito_enabled') && $request->notas_credito_enabled == '1';
        $noOrderLimit = $request->has('no_order_limit') && $request->no_order_limit == '1';
        $orderLimitEnabled = !$noOrderLimit;
        $monthlyOrderLimit = $orderLimitEnabled ? $request->monthly_order_limit : null;

        $data = [
            'shopify_tienda' => $request->shopify_tienda,
            'shopify_token' => $request->shopify_token,
            'shopify_secret' => $request->shopify_secret,
            'lioren_api_key' => $request->lioren_api_key,
            'webhook_url' => $request->webhook_url,
            'facturacion_enabled' => $facturacionEnabled,
            'shopify_visibility_enabled' => $shopifyVisibilityEnabled,
            'notas_credito_enabled' => $notasCreditoEnabled,
        ];

        // Guardar en sesión (temporal para la vista)
        session($data);

        // Guardar en base de datos (permanente para webhooks)
        \App\Models\IntegracionConfig::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'shopify_tienda' => $request->shopify_tienda,
                'shopify_token' => $request->shopify_token,
                'shopify_secret' => $request->shopify_secret,
                'lioren_api_key' => $request->lioren_api_key,
                'facturacion_enabled' => $facturacionEnabled,
                'shopify_visibility_enabled' => $shopifyVisibilityEnabled,
                'notas_credito_enabled' => $notasCreditoEnabled,
                'order_limit_enabled' => $orderLimitEnabled,
                'monthly_order_limit' => $monthlyOrderLimit,
                'activo' => true,
                'ultima_sincronizacion' => now(),
            ]
        );

        Log::info("Configuración guardada - Facturación: " . ($facturacionEnabled ? 'HABILITADA' : 'DESHABILITADA') . " - Visibilidad Shopify: " . ($shopifyVisibilityEnabled ? 'HABILITADA' : 'DESHABILITADA') . " - Notas de Crédito: " . ($notasCreditoEnabled ? 'HABILITADA' : 'DESHABILITADA') . " - Límite pedidos: " . ($orderLimitEnabled ? $monthlyOrderLimit . ' mensuales' : 'SIN LÍMITE'));

        // Validar Shopify
        $shopify_valid = $this->validarShopify($data['shopify_tienda'], $data['shopify_token']);
        
        // Validar Lioren
        $lioren_valid = $this->validarLioren($data['lioren_api_key']);

        // Crear webhooks
        $webhooks_creados = [];
        if ($shopify_valid['success']) {
            $webhooks_creados = $this->crearWebhooks(
                $data['shopify_tienda'],
                $data['shopify_token'],
                $data['webhook_url']
            );
        }

        // Obtener y sincronizar productos
        $productos_sincronizados = 0;
        Log::info("Validaciones - Shopify: " . ($shopify_valid['success'] ? 'OK' : 'FAIL') . ", Lioren: " . ($lioren_valid['success'] ? 'OK' : 'FAIL'));
        
        $syncResults = ['success' => false, 'results' => []];
        
        if ($shopify_valid['success'] && $lioren_valid['success']) {
            Log::info("Llamando a sincronización bidireccional...");
            
            // Usar el nuevo servicio de sincronización bidireccional
            $syncService = new ProductSyncService(
                auth()->id(),
                $data['shopify_tienda'],
                $data['shopify_token'],
                $data['lioren_api_key']
            );

            $syncResults = $syncService->initialBidirectionalSync();
            
            $productos_sincronizados = $syncResults['results']['total_synced'] ?? 0;
            Log::info("Productos sincronizados: {$productos_sincronizados}");
        } else {
            Log::warning("No se sincronizarán productos porque las validaciones fallaron");
            $productos_sincronizados = 0;
        }

        $data['shopify_valid'] = $shopify_valid;
        $data['lioren_valid'] = $lioren_valid;
        $data['webhooks_creados'] = $webhooks_creados;
        $data['productos_sincronizados'] = $productos_sincronizados;
        $data['sync_results'] = $syncResults;

        return view('integracion.procesar', $data);
    }

    /**
     * Sincronizar productos de Shopify a Lioren
     */
    private function sincronizarProductos($tienda, $token, $api_key)
    {
        Log::info("=== INICIANDO SINCRONIZACIÓN DE PRODUCTOS ===");
        Log::info("Tienda: {$tienda}");
        
        try {
            // Obtener productos de Shopify
            Log::info("Obteniendo productos de Shopify...");
            
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->get("https://{$tienda}/admin/api/2024-01/products.json?limit=10");
            
            Log::info("Respuesta Shopify: Status {$response->status()}");

            if (!$response->successful()) {
                return 0;
            }

            $productos = $response->json()['products'] ?? [];
            $sincronizados = 0;
            
            Log::info("Productos encontrados en Shopify: " . count($productos));

            foreach ($productos as $producto) {
                $variant = $producto['variants'][0] ?? [];
                $precio = floatval($variant['price'] ?? 0);
                
                // Calcular precio neto (sin IVA) y bruto (con IVA)
                $precioventabruto = $precio;
                $preciocompraneto = round($precio / 1.19, 2); // Precio sin IVA (19%)
                
                // Preparar datos para Lioren según documentación oficial
                $datos_lioren = [
                    'nombre' => $producto['title'] ?? 'Producto sin nombre',
                    'codigo' => $variant['sku'] ?? 'SKU-' . $producto['id'],
                    'fraccionable' => 0, // No fraccionable por defecto
                    'exento' => 0, // Afecto a IVA por defecto
                    'preciocompraneto' => $preciocompraneto,
                    'precioventabruto' => $precioventabruto,
                    'unidad' => 'Unidad',
                    'descripcion' => strip_tags($producto['body_html'] ?? ''),
                ];

                // Enviar a Lioren
                Log::info("Intentando crear producto en Lioren: {$producto['title']}", $datos_lioren);
                
                $lioren_response = Http::withHeaders([
                    'Authorization' => "Bearer {$api_key}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://www.lioren.cl/api/productos', $datos_lioren);

                Log::info("Respuesta Lioren: Status {$lioren_response->status()}", [
                    'body' => $lioren_response->body()
                ]);

                if ($lioren_response->successful()) {
                    $lioren_data = $lioren_response->json();
                    
                    // Guardar mapeo en BD
                    \App\Models\ProductMapping::updateOrCreate(
                        ['shopify_product_id' => $producto['id']],
                        [
                            'lioren_product_id' => $lioren_data['id'] ?? null,
                            'product_title' => $producto['title'],
                            'sku' => $variant['sku'] ?? '',
                            'price' => $variant['price'] ?? 0,
                            'stock' => $variant['inventory_quantity'] ?? 0,
                            'sync_status' => 'synced',
                            'last_synced_at' => now(),
                        ]
                    );

                    $sincronizados++;
                    Log::info("Producto sincronizado: {$producto['title']}");
                } else {
                    // Guardar error
                    \App\Models\ProductMapping::updateOrCreate(
                        ['shopify_product_id' => $producto['id']],
                        [
                            'product_title' => $producto['title'],
                            'sync_status' => 'error',
                            'last_error' => $lioren_response->body(),
                        ]
                    );
                }
            }

            return $sincronizados;

        } catch (\Exception $e) {
            Log::error("Error sincronizando productos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crear webhooks en Shopify
     */
    private function crearWebhooks($tienda, $token, $webhook_url)
    {
        $config = \App\Models\IntegracionConfig::where('user_id', auth()->id())->first();
        
        $webhooks = [
            ['topic' => 'orders/create', 'nombre' => 'Nuevos Pedidos'],
            ['topic' => 'products/create', 'nombre' => 'Productos Creados'],
            ['topic' => 'products/update', 'nombre' => 'Productos Actualizados'],
            ['topic' => 'inventory_levels/update', 'nombre' => 'Inventario Actualizado']
        ];

        // Agregar webhooks de Notas de Crédito si está habilitado
        if ($config && $config->notas_credito_enabled) {
            $webhooks[] = ['topic' => 'orders/cancelled', 'nombre' => 'Pedidos Cancelados'];
            $webhooks[] = ['topic' => 'refunds/create', 'nombre' => 'Reembolsos Creados'];
        }

        $creados = [];

        foreach ($webhooks as $webhook) {
            try {
                $url_completa = $webhook_url . '?evento=' . str_replace('/', '_', $webhook['topic']);
                
                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $token,
                ])->post("https://{$tienda}/admin/api/2024-01/webhooks.json", [
                    'webhook' => [
                        'topic' => $webhook['topic'],
                        'address' => $url_completa,
                        'format' => 'json'
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $creados[] = [
                        'topic' => $webhook['topic'],
                        'nombre' => $webhook['nombre'],
                        'id' => $result['webhook']['id'] ?? null,
                        'success' => true
                    ];
                    Log::info("Webhook creado: {$webhook['topic']}");
                } else {
                    $creados[] = [
                        'topic' => $webhook['topic'],
                        'nombre' => $webhook['nombre'],
                        'success' => false,
                        'error' => $response->body()
                    ];
                    Log::error("Error creando webhook {$webhook['topic']}: " . $response->body());
                }
            } catch (\Exception $e) {
                $creados[] = [
                    'topic' => $webhook['topic'],
                    'nombre' => $webhook['nombre'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                Log::error("Excepción creando webhook: " . $e->getMessage());
            }
        }

        return $creados;
    }

    /**
     * Ver productos sincronizados
     */
    public function productos()
    {
        $productos = \App\Models\ProductMapping::orderBy('created_at', 'desc')->get();
        return view('integracion.productos', compact('productos'));
    }

    /**
     * Ver productos directamente desde Lioren
     */
    public function productosLioren()
    {
        $config = \App\Models\IntegracionConfig::getActiva();
        $api_key = $config ? $config->lioren_api_key : (session('lioren_api_key') ?? env('LIOREN_API_KEY'));
        
        if (!$api_key) {
            return view('integracion.productos-lioren', [
                'error' => 'No hay API Key de Lioren configurada. Por favor, ejecuta la integración primero.',
                'productos' => []
            ]);
        }

        try {
            Log::info("Obteniendo productos de Lioren...");
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$api_key}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get('https://www.lioren.cl/api/productos');

            Log::info("Respuesta Lioren: Status {$response->status()}");

            if ($response->successful()) {
                $productos = $response->json();
                
                // Si la respuesta es un objeto con 'data', extraerlo
                if (isset($productos['data'])) {
                    $productos = $productos['data'];
                }
                
                // Si no es array, convertirlo
                if (!is_array($productos)) {
                    $productos = [];
                }

                return view('integracion.productos-lioren', [
                    'productos' => $productos,
                    'error' => null,
                    'total' => count($productos)
                ]);
            } else {
                return view('integracion.productos-lioren', [
                    'error' => "Error al obtener productos de Lioren (HTTP {$response->status()}): " . $response->body(),
                    'productos' => []
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Error obteniendo productos de Lioren: " . $e->getMessage());
            
            return view('integracion.productos-lioren', [
                'error' => 'Error: ' . $e->getMessage(),
                'productos' => []
            ]);
        }
    }

    /**
     * Receptor de webhooks de Shopify
     */
    public function webhookReceiver(Request $request)
    {
        $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
        $shop_domain = $request->header('X-Shopify-Shop-Domain');
        $topic = $request->header('X-Shopify-Topic');
        $evento = $request->query('evento');

        $data = $request->getContent();

        // Registrar en log
        Log::channel('single')->info('=== WEBHOOK RECIBIDO ===', [
            'evento' => $evento,
            'topic' => $topic,
            'shop' => $shop_domain,
        ]);

        // Obtener configuración activa de la BD
        $config = \App\Models\IntegracionConfig::getActiva();
        
        if (!$config) {
            Log::channel('single')->error('No hay configuración activa');
            return response()->json(['error' => 'No configuration found'], 500);
        }

        // Validar HMAC (temporalmente desactivado para pruebas)
        if ($hmac_header && false) { // Desactivado temporalmente
            $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $config->shopify_secret, true));
            
            if (!hash_equals($calculated_hmac, $hmac_header)) {
                Log::channel('single')->error('HMAC inválido - Webhook rechazado', [
                    'calculated' => substr($calculated_hmac, 0, 20),
                    'received' => substr($hmac_header, 0, 20),
                ]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            Log::channel('single')->info('✅ HMAC válido');
        } else {
            Log::channel('single')->warning('⚠️ HMAC validation disabled for testing');
        }

        $webhook_data = json_decode($data, true);

        if (!$webhook_data) {
            Log::channel('single')->error('Error al decodificar JSON');
            return response()->json(['error' => 'Bad Request'], 400);
        }

        // Procesar según el tipo de evento
        try {
            $lioren_api_key = $config->lioren_api_key;

            // Inicializar servicio de sincronización
            $webhookSync = new WebhookSyncService($config->user_id);

            switch ($evento) {
                case 'orders_create':
                case 'order_create':
                    // Verificar límite de pedidos mensuales
                    if ($config->order_limit_enabled && $config->monthly_order_limit) {
                        $ordersThisMonth = $this->getMonthlyOrderCount($config->user_id);
                        
                        if ($ordersThisMonth >= $config->monthly_order_limit) {
                            Log::channel('single')->warning("🚫 Límite mensual alcanzado: {$ordersThisMonth}/{$config->monthly_order_limit} - Pedido no procesado");
                            return response()->json(['status' => 'limit_reached', 'message' => 'Límite mensual de pedidos alcanzado'], 200);
                        }
                        
                        Log::channel('single')->info("📊 Pedidos este mes: {$ordersThisMonth}/{$config->monthly_order_limit}");
                    }
                    
                    // Verificar si la facturación está habilitada
                    if ($config->facturacion_enabled) {
                        Log::channel('single')->info('� Factturación habilitada - Procesando con módulo de facturación');
                        $this->procesarPedidoConFacturacion($webhook_data, $lioren_api_key);
                    } else {
                        Log::channel('single')->info('📝 Facturación deshabilitada - Procesando solo boleta');
                        $this->procesarPedido($webhook_data, $lioren_api_key);
                    }
                    break;
                    
                case 'products_create':
                case 'product_create':
                    Log::channel('single')->info('🆕 Webhook: Producto creado');
                    $webhookSync->handleProductCreate($webhook_data);
                    break;
                    
                case 'products_update':
                case 'product_update':
                    Log::channel('single')->info('✏️ Webhook: Producto actualizado');
                    $webhookSync->handleProductUpdate($webhook_data);
                    break;

                case 'products_delete':
                case 'product_delete':
                    Log::channel('single')->info('🗑️ Webhook: Producto eliminado');
                    $webhookSync->handleProductDelete($webhook_data);
                    break;
                    
                case 'inventory_levels_update':
                case 'inventory_update':
                    Log::channel('single')->info('📦 Webhook: Inventario actualizado');
                    $webhookSync->handleInventoryUpdate($webhook_data);
                    break;

                case 'orders_cancelled':
                case 'order_cancelled':
                    if ($config->notas_credito_enabled) {
                        Log::channel('single')->info('🔄 Pedido cancelado - Emitiendo Nota de Crédito');
                        $this->procesarCancelacion($webhook_data, $lioren_api_key, $config);
                    } else {
                        Log::channel('single')->info('⚠️ Notas de Crédito deshabilitadas - Cancelación no procesada');
                    }
                    break;

                case 'refunds_create':
                case 'refund_create':
                    if ($config->notas_credito_enabled) {
                        Log::channel('single')->info('🔄 Reembolso creado - Emitiendo Nota de Crédito');
                        $this->procesarReembolso($webhook_data, $lioren_api_key, $config);
                    } else {
                        Log::channel('single')->info('⚠️ Notas de Crédito deshabilitadas - Reembolso no procesado');
                    }
                    break;
            }

            Log::channel('single')->info('Webhook procesado exitosamente');
            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::channel('single')->error('Error al procesar webhook: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Validar credenciales de Shopify
     */
    private function validarShopify($tienda, $token)
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->get("https://{$tienda}/admin/api/2024-01/shop.json");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con Shopify',
                    'data' => $response->json()['shop'] ?? null
                ];
            }

            return [
                'success' => false,
                'message' => "Credenciales inválidas (HTTP {$response->status()})",
                'data' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validar credenciales de Lioren
     */
    private function validarLioren($api_key)
    {
        try {
            Log::info("Validando Lioren con API Key: " . substr($api_key, 0, 10) . "...");
            
            // Intentar crear un producto de prueba (sin guardarlo realmente)
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$api_key}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get('https://www.lioren.cl/api/productos');

            Log::info("Respuesta Lioren validación: Status {$response->status()}");
            Log::info("Body: " . substr($response->body(), 0, 200));

            // Lioren puede responder 200 o 401
            if ($response->status() === 200 || $response->status() === 201) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con Lioren'
                ];
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => "API Key inválida o sin permisos"
                ];
            }

            return [
                'success' => false,
                'message' => "Error (HTTP {$response->status()}): " . $response->body()
            ];
        } catch (\Exception $e) {
            Log::error("Excepción validando Lioren: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar pedido CON facturación habilitada (detecta boleta o factura)
     */
    private function procesarPedidoConFacturacion($order, $api_key)
    {
        Log::channel('single')->info('=== PROCESANDO PEDIDO CON FACTURACIÓN ===', [
            'order_id' => $order['id'] ?? null,
            'order_number' => $order['order_number'] ?? null,
            'total' => $order['total_price'] ?? null,
        ]);

        try {
            // Extraer note_attributes
            $noteAttributes = $order['note_attributes'] ?? [];
            $tipoComprobante = null;
            $rut = null;
            $razonSocial = null;
            $giro = null;

            // Leer datos de note_attributes
            foreach ($noteAttributes as $attr) {
                $name = strtolower($attr['name'] ?? '');
                $value = $attr['value'] ?? null;

                if ($name === 'tipo_comprobante') {
                    $tipoComprobante = strtolower($value);
                } elseif ($name === 'rut') {
                    $rut = $value;
                } elseif ($name === 'razon_social') {
                    $razonSocial = $value;
                } elseif ($name === 'giro') {
                    $giro = $value;
                }
            }

            Log::channel('single')->info('Datos extraídos de note_attributes', [
                'tipo_comprobante' => $tipoComprobante,
                'rut' => $rut ? 'presente' : 'ausente',
                'razon_social' => $razonSocial ? 'presente' : 'ausente',
                'giro' => $giro ? 'presente' : 'ausente',
            ]);

            // Decidir si es factura o boleta
            if ($tipoComprobante === 'factura' && $rut && $razonSocial && $giro) {
                Log::channel('single')->info('📄 Emitiendo FACTURA');
                $this->emitirFactura($order, $api_key, $rut, $razonSocial, $giro);
            } else {
                Log::channel('single')->info('📝 Emitiendo BOLETA (datos de factura incompletos o tipo=boleta)');
                $this->procesarPedido($order, $api_key);
            }

        } catch (\Exception $e) {
            Log::channel('single')->error('Error en procesarPedidoConFacturacion: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Emitir FACTURA en Lioren
     */
    private function emitirFactura($order, $api_key, $rut, $razonSocial, $giro)
    {
        try {
            // Extraer datos del cliente para dirección
            $customer = $order['customer'] ?? [];
            $shippingAddress = $order['shipping_address'] ?? [];
            $billingAddress = $order['billing_address'] ?? [];

            // Priorizar shipping address, luego billing
            $address = $shippingAddress ?: $billingAddress;
            $direccion = trim(($address['address1'] ?? '') . ' ' . ($address['address2'] ?? ''));
            $ciudad = $address['city'] ?? $address['province'] ?? 'Santiago';
            $customerEmail = $customer['email'] ?? $order['email'] ?? null;

            // Si no hay dirección, usar datos por defecto
            if (empty($direccion)) {
                $direccion = 'Sin dirección especificada';
            }

            // Obtener IDs de localización (comuna y ciudad)
            $localizacion = $this->obtenerIdsLocalizacion($ciudad, $api_key);

            if (!$localizacion) {
                Log::channel('single')->warning('No se pudo obtener IDs de localización para: ' . $ciudad . ', usando Santiago por defecto');
                // Usar valores por defecto (Santiago Centro)
                $localizacion = ['comunaId' => 13101, 'ciudadId' => 131];
            }

            // Preparar detalles de productos (PRECIO NETO sin IVA)
            $detalles = [];
            $lineItems = $order['line_items'] ?? [];
            
            foreach ($lineItems as $item) {
                $precioConIva = floatval($item['price'] ?? 0);
                $precioNeto = round($precioConIva / 1.19, 2); // Convertir a neto

                $detalles[] = [
                    'codigo' => $item['sku'] ?? 'PROD-' . ($item['product_id'] ?? rand(1000, 9999)),
                    'nombre' => substr($item['title'] ?? 'Producto', 0, 80),
                    'cantidad' => floatval($item['quantity'] ?? 1),
                    'precio' => $precioNeto, // NETO sin IVA
                    'unidad' => 'UN',
                    'exento' => false, // Afecto a IVA
                ];
            }

            if (empty($detalles)) {
                Log::channel('single')->warning('Pedido sin productos, no se emite factura');
                return;
            }

            // Limpiar RUT (quitar solo puntos, mantener guión del dígito verificador)
            $rutLimpio = str_replace('.', '', $rut);
            // Asegurar que tenga el formato correcto (12345678-9)
            if (!str_contains($rutLimpio, '-')) {
                // Si no tiene guión, agregarlo antes del último dígito
                $rutLimpio = substr($rutLimpio, 0, -1) . '-' . substr($rutLimpio, -1);
            }

            // Preparar payload para Lioren
            $facturaData = [
                'emisor' => [
                    'tipodoc' => '33', // Factura Electrónica
                    'fecha' => now()->format('Y-m-d'),
                    'observaciones' => 'Pedido Shopify #' . ($order['order_number'] ?? $order['id']),
                ],
                'receptor' => [
                    'rut' => $rutLimpio,
                    'rs' => substr($razonSocial, 0, 100),
                    'giro' => substr($giro, 0, 40),
                    'comuna' => $localizacion['comunaId'],
                    'ciudad' => $localizacion['ciudadId'],
                    'direccion' => substr($direccion, 0, 50),
                ],
                'detalles' => $detalles,
                'expects' => 'all', // Recibir PDF y XML
            ];

            // Agregar email si existe
            if ($customerEmail) {
                $facturaData['receptor']['email'] = substr($customerEmail, 0, 80);
            }

            Log::channel('single')->info('Emitiendo factura en Lioren', [
                'rut' => $rutLimpio,
                'razon_social' => $razonSocial,
                'total_items' => count($detalles),
            ]);

            // Enviar a Lioren
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$api_key}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://www.lioren.cl/api/dtes', $facturaData);

            Log::channel('single')->info("Respuesta Lioren: Status {$response->status()}");

            if ($response->successful()) {
                $result = $response->json();

                // Guardar factura en base de datos
                \App\Models\FacturaEmitida::create([
                    'shopify_order_id' => (string)$order['id'],
                    'shopify_order_number' => (string)($order['order_number'] ?? $order['id']),
                    'tipo_documento' => '33',
                    'lioren_factura_id' => $result['id'] ?? null,
                    'folio' => $result['folio'] ?? null,
                    'rut_receptor' => $rutLimpio,
                    'razon_social' => $razonSocial,
                    'monto_neto' => $result['montoneto'] ?? 0,
                    'monto_iva' => $result['montoiva'] ?? 0,
                    'monto_total' => $result['montototal'] ?? 0,
                    'pdf_base64' => $result['pdf'] ?? null,
                    'xml_base64' => $result['xml'] ?? null,
                    'status' => 'emitida',
                    'emitida_at' => now(),
                ]);

                Log::channel('single')->info("✅ Factura #{$result['folio']} emitida exitosamente para pedido Shopify #{$order['order_number']}");

                // Actualizar nota en Shopify si está habilitado
                if ($config->shopify_visibility_enabled && isset($result['folio'])) {
                    $this->updateShopifyOrderNote($order['id'], "Factura Lioren #{$result['folio']}", $config);
                }

            } else {
                Log::channel('single')->error('Error al emitir factura en Lioren', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // Guardar error en BD
                \App\Models\FacturaEmitida::create([
                    'shopify_order_id' => (string)$order['id'],
                    'shopify_order_number' => (string)($order['order_number'] ?? $order['id']),
                    'tipo_documento' => '33',
                    'rut_receptor' => $rutLimpio,
                    'razon_social' => $razonSocial,
                    'monto_neto' => 0,
                    'monto_iva' => 0,
                    'monto_total' => 0,
                    'status' => 'error',
                    'error_message' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('single')->error('Excepción al emitir factura: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Obtener IDs de localización (comuna y ciudad) desde Lioren
     */
    private function obtenerIdsLocalizacion($nombreCiudad, $api_key)
    {
        try {
            // Normalizar nombre de ciudad
            $nombreNormalizado = $this->normalizarNombreCiudad($nombreCiudad);

            Log::channel('single')->info("Buscando localización para: {$nombreCiudad} (normalizado: {$nombreNormalizado})");

            // Obtener localidades desde Lioren (endpoint correcto es /comunas)
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$api_key}",
                'Accept' => 'application/json',
            ])->timeout(10)->get('https://www.lioren.cl/api/comunas');

            if (!$response->successful()) {
                Log::channel('single')->error('Error al obtener comunas de Lioren: ' . $response->status());
                return null;
            }

            $localidades = $response->json();
            
            // Si la respuesta tiene una clave 'comunas', usarla
            if (isset($localidades['comunas'])) {
                $localidades = $localidades['comunas'];
            }

            // Buscar coincidencia
            foreach ($localidades as $localidad) {
                $nombreLocalidad = strtolower($localidad['nombre'] ?? '');
                
                if (strpos($nombreLocalidad, $nombreNormalizado) !== false || 
                    strpos($nombreNormalizado, $nombreLocalidad) !== false) {
                    
                    Log::channel('single')->info("✅ Localización encontrada", [
                        'nombre' => $localidad['nombre'],
                        'comunaId' => $localidad['id'],
                        'ciudadId' => $localidad['ciudad_id'] ?? $localidad['ciudadid'] ?? null,
                    ]);

                    return [
                        'comunaId' => $localidad['id'],
                        'ciudadId' => $localidad['ciudad_id'] ?? $localidad['ciudadid'] ?? 131, // Santiago por defecto
                    ];
                }
            }

            Log::channel('single')->warning("No se encontró localización para: {$nombreCiudad}");
            return null;

        } catch (\Exception $e) {
            Log::channel('single')->error('Error obteniendo localización: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalizar nombre de ciudad para búsqueda
     */
    private function normalizarNombreCiudad($nombre)
    {
        $nombre = strtolower(trim($nombre));
        
        // Mapeo de nombres comunes
        $mapeo = [
            'stgo' => 'santiago',
            'conce' => 'concepcion',
            'valpo' => 'valparaiso',
            'la serena' => 'la serena',
            'antofa' => 'antofagasta',
            'temuco' => 'temuco',
            'rancagua' => 'rancagua',
            'talca' => 'talca',
            'arica' => 'arica',
            'iquique' => 'iquique',
            'puerto montt' => 'puerto montt',
            'chillan' => 'chillan',
            'los angeles' => 'los angeles',
            'calama' => 'calama',
            'copiapo' => 'copiapo',
            'valdivia' => 'valdivia',
            'osorno' => 'osorno',
            'quillota' => 'quillota',
            'curico' => 'curico',
        ];

        return $mapeo[$nombre] ?? $nombre;
    }

    /**
     * Procesar pedido y emitir boleta automáticamente (SIN facturación)
     */
    private function procesarPedido($order, $api_key)
    {
        Log::channel('single')->info('=== PROCESANDO PEDIDO (SOLO BOLETA) ===', [
            'order_id' => $order['id'] ?? null,
            'order_number' => $order['order_number'] ?? null,
            'total' => $order['total_price'] ?? null,
        ]);

        try {
            // Extraer datos del cliente
            $customer = $order['customer'] ?? [];
            $customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
            $customerEmail = $customer['email'] ?? $order['email'] ?? null;
            
            // Extraer RUT de note_attributes si existe
            $rut = null;
            if (isset($order['note_attributes']) && is_array($order['note_attributes'])) {
                foreach ($order['note_attributes'] as $attr) {
                    if (strtolower($attr['name'] ?? '') === 'rut') {
                        $rut = $attr['value'];
                        break;
                    }
                }
            }

            // Preparar detalles de productos
            $detalles = [];
            $lineItems = $order['line_items'] ?? [];
            
            foreach ($lineItems as $item) {
                $detalles[] = [
                    'codigo' => $item['sku'] ?? 'PROD-' . ($item['product_id'] ?? rand(1000, 9999)),
                    'nombre' => $item['title'] ?? 'Producto',
                    'cantidad' => floatval($item['quantity'] ?? 1),
                    'precio' => floatval($item['price'] ?? 0), // Precio BRUTO (con IVA)
                    'unidad' => 'UN',
                    'exento' => false, // Afecto a IVA
                ];
            }

            if (empty($detalles)) {
                Log::channel('single')->warning('Pedido sin productos, no se emite boleta');
                return;
            }

            // Preparar datos de la boleta
            $boletaData = [
                'emisor' => [
                    'tipodoc' => '39', // Boleta Afecta
                    'servicio' => 3,   // Ventas y Servicios
                    'observaciones' => 'Pedido Shopify #' . ($order['order_number'] ?? $order['id']),
                ],
                'detalles' => $detalles,
                'expects' => 'all', // Recibir PDF y XML
            ];

            // Agregar receptor si hay datos
            if ($rut || $customerName) {
                $boletaData['receptor'] = array_filter([
                    'rut' => $rut,
                    'rs' => $customerName ?: 'Cliente',
                    'email' => $customerEmail,
                ]);
            }

            Log::channel('single')->info('Emitiendo boleta en Lioren', $boletaData);

            // Enviar a Lioren
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$api_key}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://www.lioren.cl/api/boletas', $boletaData);

            Log::channel('single')->info("Respuesta Lioren: Status {$response->status()}");

            if ($response->successful()) {
                $result = $response->json();

                // Guardar boleta en base de datos
                $boleta = \App\Models\Boleta::create([
                    'user_id' => 1, // Usuario del sistema (puedes ajustar esto)
                    'lioren_id' => $result['id'] ?? null,
                    'tipodoc' => $result['tipodoc'] ?? '39',
                    'folio' => $result['folio'] ?? null,
                    'fecha' => $result['fecha'] ?? now()->format('Y-m-d'),
                    'receptor_rut' => $rut,
                    'receptor_nombre' => $customerName ?: $result['rs'] ?? null,
                    'receptor_email' => $customerEmail,
                    'monto_neto' => $result['montoneto'] ?? 0,
                    'monto_exento' => $result['montoexento'] ?? 0,
                    'monto_iva' => $result['montoiva'] ?? 0,
                    'monto_total' => $result['montototal'] ?? 0,
                    'pdf_base64' => $result['pdf'] ?? null,
                    'xml_base64' => $result['xml'] ?? null,
                    'detalles' => $result['detalles'] ?? $detalles,
                    'observaciones' => 'Pedido Shopify #' . ($order['order_number'] ?? $order['id']),
                    'status' => 'emitida',
                ]);

                Log::channel('single')->info("✅ Boleta #{$boleta->folio} emitida exitosamente para pedido Shopify #{$order['order_number']}");

                // Actualizar nota en Shopify si está habilitado
                if ($config->shopify_visibility_enabled && $boleta->folio) {
                    $this->updateShopifyOrderNote($order['id'], "Boleta Lioren #{$boleta->folio}", $config);
                }

            } else {
                Log::channel('single')->error('Error al emitir boleta en Lioren', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // Guardar error en BD
                \App\Models\Boleta::create([
                    'user_id' => 1,
                    'fecha' => now()->format('Y-m-d'),
                    'receptor_rut' => $rut,
                    'receptor_nombre' => $customerName,
                    'monto_total' => 0,
                    'detalles' => $detalles,
                    'observaciones' => 'Pedido Shopify #' . ($order['order_number'] ?? $order['id']),
                    'status' => 'error',
                    'error_message' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('single')->error('Excepción al procesar pedido: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Procesar producto creado
     */
    private function procesarProductoCreado($product, $api_key)
    {
        Log::channel('single')->info('Procesando producto creado', [
            'product_id' => $product['id'] ?? null,
            'title' => $product['title'] ?? null,
        ]);

        // Aquí iría la lógica para crear el producto en Lioren
    }

    /**
     * Procesar producto actualizado
     */
    private function procesarProductoActualizado($product, $api_key)
    {
        Log::channel('single')->info('Procesando producto actualizado', [
            'product_id' => $product['id'] ?? null,
            'title' => $product['title'] ?? null,
        ]);

        // Aquí iría la lógica para actualizar el producto en Lioren
    }

    /**
     * Procesar inventario
     */
    private function procesarInventario($inventory, $api_key)
    {
        Log::channel('single')->info('Procesando inventario', [
            'inventory_item_id' => $inventory['inventory_item_id'] ?? null,
            'available' => $inventory['available'] ?? null,
        ]);

        // Aquí iría la lógica para actualizar el inventario en Lioren
    }

    /**
     * Mostrar formulario de emisión de boletas
     */
    public function boletasForm()
    {
        $config = \App\Models\IntegracionConfig::getActiva();
        $api_key = $config ? $config->lioren_api_key : (session('lioren_api_key') ?? env('LIOREN_API_KEY'));
        
        // Obtener productos sincronizados para el formulario
        $productos = \App\Models\ProductMapping::where('sync_status', 'synced')
            ->orderBy('product_title')
            ->get();

        return view('integracion.boletas-form', compact('productos', 'api_key'));
    }

    /**
     * Emitir boleta en Lioren
     */
    public function emitirBoleta(Request $request)
    {
        $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*.codigo' => 'required|string',
            'detalles.*.nombre' => 'required|string',
            'detalles.*.cantidad' => 'required|numeric|min:0.000001',
            'detalles.*.precio' => 'required|numeric|min:0',
            'receptor_rut' => 'nullable|string',
            'receptor_nombre' => 'nullable|string',
            'receptor_email' => 'nullable|email',
            'observaciones' => 'nullable|string|max:250',
        ]);

        $config = \App\Models\IntegracionConfig::getActiva();
        $api_key = $config ? $config->lioren_api_key : (session('lioren_api_key') ?? env('LIOREN_API_KEY'));

        if (!$api_key) {
            return back()->with('error', 'No hay API Key de Lioren configurada');
        }

        try {
            // Preparar detalles
            $detalles = [];
            foreach ($request->detalles as $detalle) {
                $detalles[] = [
                    'codigo' => $detalle['codigo'],
                    'nombre' => $detalle['nombre'],
                    'cantidad' => floatval($detalle['cantidad']),
                    'precio' => floatval($detalle['precio']), // Precio BRUTO (con IVA)
                    'unidad' => $detalle['unidad'] ?? 'UN',
                    'exento' => false, // Afecto a IVA
                ];
            }

            // Preparar datos de la boleta
            $data = [
                'emisor' => [
                    'tipodoc' => '39', // Boleta Afecta
                    'servicio' => 3,   // Ventas y Servicios
                    'observaciones' => $request->observaciones,
                ],
                'detalles' => $detalles,
                'expects' => 'all', // Recibir PDF y XML
            ];

            // Agregar receptor si se proporcionó
            if ($request->receptor_rut || $request->receptor_nombre) {
                $data['receptor'] = array_filter([
                    'rut' => $request->receptor_rut,
                    'rs' => $request->receptor_nombre,
                    'email' => $request->receptor_email,
                ]);
            }

            Log::info('Emitiendo boleta en Lioren', $data);

            // Enviar a Lioren
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$api_key}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://www.lioren.cl/api/boletas', $data);

            Log::info("Respuesta Lioren: Status {$response->status()}", [
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $result = $response->json();

                // Guardar en base de datos
                $boleta = \App\Models\Boleta::create([
                    'user_id' => auth()->id(),
                    'lioren_id' => $result['id'] ?? null,
                    'tipodoc' => $result['tipodoc'] ?? '39',
                    'folio' => $result['folio'] ?? null,
                    'fecha' => $result['fecha'] ?? now()->format('Y-m-d'),
                    'receptor_rut' => $request->receptor_rut,
                    'receptor_nombre' => $request->receptor_nombre ?? $result['rs'] ?? null,
                    'receptor_email' => $request->receptor_email,
                    'monto_neto' => $result['montoneto'] ?? 0,
                    'monto_exento' => $result['montoexento'] ?? 0,
                    'monto_iva' => $result['montoiva'] ?? 0,
                    'monto_total' => $result['montototal'] ?? 0,
                    'pdf_base64' => $result['pdf'] ?? null,
                    'xml_base64' => $result['xml'] ?? null,
                    'detalles' => $result['detalles'] ?? $detalles,
                    'pagos' => $result['pagos'] ?? null,
                    'observaciones' => $request->observaciones,
                    'status' => 'emitida',
                ]);

                return redirect()->route('integracion.boletas')
                    ->with('success', "¡Boleta #{$boleta->folio} emitida exitosamente!");

            } else {
                // Guardar error
                \App\Models\Boleta::create([
                    'user_id' => auth()->id(),
                    'fecha' => now()->format('Y-m-d'),
                    'receptor_rut' => $request->receptor_rut,
                    'receptor_nombre' => $request->receptor_nombre,
                    'monto_total' => 0,
                    'detalles' => $detalles,
                    'observaciones' => $request->observaciones,
                    'status' => 'error',
                    'error_message' => $response->body(),
                ]);

                return back()->with('error', "Error al emitir boleta: {$response->body()}");
            }

        } catch (\Exception $e) {
            Log::error("Error emitiendo boleta: " . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Listar boletas emitidas
     */
    public function boletas()
    {
        $boletas = \App\Models\Boleta::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('integracion.boletas', compact('boletas'));
    }

    /**
     * Descargar PDF de boleta
     */
    public function boletaPdf($id)
    {
        $boleta = \App\Models\Boleta::findOrFail($id);

        if (!$boleta->pdf_base64) {
            abort(404, 'PDF no disponible');
        }

        $pdf = base64_decode($boleta->pdf_base64);
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=boleta_{$boleta->folio}.pdf");
    }

    /**
     * Descargar XML de boleta
     */
    public function boletaXml($id)
    {
        $boleta = \App\Models\Boleta::findOrFail($id);

        if (!$boleta->xml_base64) {
            abort(404, 'XML no disponible');
        }

        $xml = base64_decode($boleta->xml_base64);
        
        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', "attachment; filename=boleta_{$boleta->folio}.xml");
    }

    /**
     * Ver estado de la integración
     */
    public function estado()
    {
        $config = \App\Models\IntegracionConfig::getActiva();
        
        $stats = [
            'productos' => \App\Models\ProductMapping::where('sync_status', 'synced')->count(),
            'boletas' => \App\Models\Boleta::where('status', 'emitida')->count(),
            'total_facturado' => \App\Models\Boleta::where('status', 'emitida')->sum('monto_total'),    
        ];

        return view('integracion.estado', compact('config', 'stats'));
    }

    /**
     * Mapeo de ciudades/comunas normalizadas
     */
    private $mapeoNormalizado = [
        'stgo' => 'santiago',
        'conce' => 'concepción',
        'valpo' => 'valparaíso',
        'la serena' => 'la serena',
        'antofa' => 'antofagasta',
        'las condes' => 'las condes',
        'providencia' => 'providencia',
        'ñuñoa' => 'ñuñoa',
        'maipu' => 'maipú',
        'la florida' => 'la florida',
        'puente alto' => 'puente alto',
    ];

    /**
     * Normalizar nombre de ciudad
     */
    private function normalizarCiudad($ciudad)
    {
        $ciudadLower = strtolower(trim($ciudad));
        return $this->mapeoNormalizado[$ciudadLower] ?? $ciudadLower;
    }

    /**
     * Resetear/Eliminar integración completa
     */
    public function resetearIntegracion(Request $request)
    {
        try {
            Log::info("=== INICIANDO RESETEO DE INTEGRACIÓN ===");

            $config = \App\Models\IntegracionConfig::where('user_id', auth()->id())->first();
            
            $resultados = [
                'webhooks_eliminados' => 0,
                'config_eliminada' => false,
                'productos_eliminados' => 0,
                'facturas_eliminadas' => 0,
                'errores' => [],
            ];

            // 1. Eliminar webhooks de Shopify (si existe configuración)
            if ($config) {
                try {
                    Log::info("Obteniendo webhooks de Shopify...");
                    
                    $response = Http::withHeaders([
                        'X-Shopify-Access-Token' => $config->shopify_token,
                    ])->get("https://{$config->shopify_tienda}/admin/api/2024-01/webhooks.json");

                    if ($response->successful()) {
                        $webhooks = $response->json()['webhooks'] ?? [];
                        
                        foreach ($webhooks as $webhook) {
                            $deleteResponse = Http::withHeaders([
                                'X-Shopify-Access-Token' => $config->shopify_token,
                            ])->delete("https://{$config->shopify_tienda}/admin/api/2024-01/webhooks/{$webhook['id']}.json");

                            if ($deleteResponse->successful()) {
                                $resultados['webhooks_eliminados']++;
                                Log::info("Webhook eliminado: {$webhook['id']}");
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $resultados['errores'][] = "Error eliminando webhooks: " . $e->getMessage();
                    Log::error("Error eliminando webhooks: " . $e->getMessage());
                }
            }

            // 2. Eliminar productos sincronizados (opcional)
            if ($request->has('eliminar_productos')) {
                $productosEliminados = \App\Models\ProductMapping::count();
                \App\Models\ProductMapping::truncate();
                $resultados['productos_eliminados'] = $productosEliminados;
                Log::info("Productos eliminados: {$productosEliminados}");
            }

            // 3. Eliminar facturas emitidas (opcional)
            if ($request->has('eliminar_facturas')) {
                $facturasEliminadas = \App\Models\FacturaEmitida::count();
                \App\Models\FacturaEmitida::truncate();
                $resultados['facturas_eliminadas'] = $facturasEliminadas;
                Log::info("Facturas eliminadas: {$facturasEliminadas}");
            }

            // 4. Eliminar configuración de BD
            if ($config) {
                $config->delete();
                $resultados['config_eliminada'] = true;
                Log::info("Configuración eliminada de BD");
            }

            // 5. Limpiar sesión
            session()->forget(['shopify_tienda', 'shopify_token', 'shopify_secret', 'lioren_api_key', 'facturacion_enabled']);
            Log::info("Sesión limpiada");

            Log::info("=== RESETEO COMPLETADO ===", $resultados);

            return redirect()->route('integracion.dashboard')->with('success', '✅ Integración reseteada exitosamente. Puedes configurar desde cero.');

        } catch (\Exception $e) {
            Log::error("Error reseteando integración: " . $e->getMessage());
            return redirect()->route('integracion.dashboard')->with('error', '❌ Error al resetear: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar vista de confirmación para resetear
     */
    public function confirmarReset()
    {
        $config = \App\Models\IntegracionConfig::where('user_id', auth()->id())->first();
        $productosCount = \App\Models\ProductMapping::count();
        $facturasCount = \App\Models\FacturaEmitida::count();

        return view('integracion.resetear', compact('config', 'productosCount', 'facturasCount'));
    }

    /**
     * Actualizar nota del pedido en Shopify con el número de folio
     */
    private function updateShopifyOrderNote($orderId, $note, $config)
    {
        try {
            Log::channel('single')->info("📝 Actualizando nota en Shopify para pedido #{$orderId}: {$note}");

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $config->shopify_token,
                'Content-Type' => 'application/json',
            ])->put("https://{$config->shopify_tienda}/admin/api/2025-10/orders/{$orderId}.json", [
                'order' => [
                    'id' => $orderId,
                    'note' => $note,
                ]
            ]);

            if ($response->successful()) {
                Log::channel('single')->info("✅ Nota actualizada exitosamente en Shopify");
                return true;
            } else {
                Log::channel('single')->error("❌ Error actualizando nota en Shopify", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::channel('single')->error("❌ Excepción actualizando nota en Shopify: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener cantidad de pedidos procesados en el mes actual
     */
    private function getMonthlyOrderCount($userId)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $boletasCount = \App\Models\Boleta::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'emitida')
            ->count();

        $facturasCount = \App\Models\FacturaEmitida::where('shopify_order_id', '!=', null)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'emitida')
            ->count();

        return $boletasCount + $facturasCount;
    }

    /**
     * Procesar cancelación de pedido y emitir Nota de Crédito
     */
    private function procesarCancelacion($order, $api_key, $config)
    {
        Log::channel('single')->info('=== PROCESANDO CANCELACIÓN DE PEDIDO ===', [
            'order_id' => $order['id'] ?? null,
            'order_number' => $order['order_number'] ?? null,
        ]);

        try {
            $orderId = (string)$order['id'];

            // Buscar boleta o factura original
            $boleta = \App\Models\Boleta::where('observaciones', 'LIKE', "%Shopify #{$order['order_number']}%")
                ->where('status', 'emitida')
                ->first();

            $factura = \App\Models\FacturaEmitida::where('shopify_order_id', $orderId)
                ->where('status', 'emitida')
                ->first();

            if (!$boleta && !$factura) {
                Log::channel('single')->warning('No se encontró boleta/factura original para este pedido');
                return;
            }

            // Determinar tipo de documento original y folio
            if ($factura) {
                $tipoDocOriginal = '33'; // Factura
                $folioOriginal = $factura->folio;
                $rutReceptor = $factura->rut_receptor;
                $razonSocial = $factura->razon_social;
                $montoTotal = $factura->monto_total;
            } else {
                $tipoDocOriginal = '39'; // Boleta
                $folioOriginal = $boleta->folio;
                $rutReceptor = $boleta->receptor_rut ?? '66666666-6';
                $razonSocial = $boleta->receptor_nombre ?? 'Cliente';
                $montoTotal = $boleta->monto_total;
            }

            Log::channel('single')->info("Documento original encontrado: Tipo {$tipoDocOriginal}, Folio {$folioOriginal}");

            // Emitir Nota de Crédito
            $this->emitirNotaCredito(
                $api_key,
                $config,
                $tipoDocOriginal,
                $folioOriginal,
                $rutReceptor,
                $razonSocial,
                $montoTotal,
                $orderId,
                $order['order_number'] ?? $orderId,
                'Anula documento por cancelación de pedido'
            );

        } catch (\Exception $e) {
            Log::channel('single')->error('Error procesando cancelación: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Procesar reembolso y emitir Nota de Crédito
     */
    private function procesarReembolso($refund, $api_key, $config)
    {
        Log::channel('single')->info('=== PROCESANDO REEMBOLSO ===', [
            'refund_id' => $refund['id'] ?? null,
            'order_id' => $refund['order_id'] ?? null,
        ]);

        try {
            $orderId = (string)($refund['order_id'] ?? null);

            if (!$orderId) {
                Log::channel('single')->warning('Reembolso sin order_id');
                return;
            }

            // Buscar boleta o factura original
            $factura = \App\Models\FacturaEmitida::where('shopify_order_id', $orderId)
                ->where('status', 'emitida')
                ->first();

            $boleta = null;
            if (!$factura) {
                // Buscar por order_id en observaciones de boleta
                $boleta = \App\Models\Boleta::where('observaciones', 'LIKE', "%{$orderId}%")
                    ->where('status', 'emitida')
                    ->first();
            }

            if (!$boleta && !$factura) {
                Log::channel('single')->warning('No se encontró boleta/factura original para este reembolso');
                return;
            }

            // Determinar tipo de documento original y folio
            if ($factura) {
                $tipoDocOriginal = '33'; // Factura
                $folioOriginal = $factura->folio;
                $rutReceptor = $factura->rut_receptor;
                $razonSocial = $factura->razon_social;
                $montoTotal = $factura->monto_total;
                $orderNumber = $factura->shopify_order_number;
            } else {
                $tipoDocOriginal = '39'; // Boleta
                $folioOriginal = $boleta->folio;
                $rutReceptor = $boleta->receptor_rut ?? '66666666-6';
                $razonSocial = $boleta->receptor_nombre ?? 'Cliente';
                $montoTotal = $boleta->monto_total;
                $orderNumber = $orderId;
            }

            Log::channel('single')->info("Documento original encontrado: Tipo {$tipoDocOriginal}, Folio {$folioOriginal}");

            // Emitir Nota de Crédito
            $this->emitirNotaCredito(
                $api_key,
                $config,
                $tipoDocOriginal,
                $folioOriginal,
                $rutReceptor,
                $razonSocial,
                $montoTotal,
                $orderId,
                $orderNumber,
                'Anula documento por reembolso'
            );

        } catch (\Exception $e) {
            Log::channel('single')->error('Error procesando reembolso: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Emitir Nota de Crédito en Lioren
     */
    private function emitirNotaCredito($api_key, $config, $tipoDocOriginal, $folioOriginal, $rutReceptor, $razonSocial, $montoTotal, $orderId, $orderNumber, $glosa)
    {
        try {
            Log::channel('single')->info('Emitiendo Nota de Crédito en Lioren', [
                'tipo_doc_original' => $tipoDocOriginal,
                'folio_original' => $folioOriginal,
                'monto' => $montoTotal,
            ]);

            // Calcular monto neto (sin IVA)
            $montoNeto = round($montoTotal / 1.19, 2);

            // Preparar datos de la Nota de Crédito
            $notaCreditoData = [
                'emisor' => [
                    'tipodoc' => '61', // Nota de Crédito
                    'fecha' => now()->format('Y-m-d'),
                ],
                'receptor' => [
                    'rut' => str_replace('.', '', $rutReceptor),
                    'rs' => substr($razonSocial, 0, 100),
                    'giro' => 'Comercio',
                    'comuna' => 13101, // Santiago Centro por defecto
                    'ciudad' => 131, // Santiago
                    'direccion' => 'Sin dirección',
                ],
                'detalles' => [
                    [
                        'nombre' => 'Devolución por cancelación/reembolso',
                        'cantidad' => 1,
                        'precio' => $montoNeto,
                        'exento' => false,
                    ]
                ],
                'referencias' => [
                    [
                        'fecha' => now()->format('Y-m-d'),
                        'tipodoc' => $tipoDocOriginal, // 39=Boleta, 33=Factura
                        'folio' => (string)$folioOriginal,
                        'razon' => 1, // Anula documento de referencia
                        'glosa' => $glosa,
                    ]
                ],
                'expects' => 'all',
            ];

            Log::channel('single')->info('Datos de Nota de Crédito preparados', $notaCreditoData);

            // Enviar a Lioren
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$api_key}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://www.lioren.cl/api/dtes', $notaCreditoData);

            Log::channel('single')->info("Respuesta Lioren: Status {$response->status()}");

            if ($response->successful()) {
                $result = $response->json();

                // Guardar Nota de Crédito en base de datos
                \App\Models\NotaCredito::create([
                    'shopify_order_id' => $orderId,
                    'shopify_order_number' => $orderNumber,
                    'tipo_documento_original' => $tipoDocOriginal,
                    'folio_original' => $folioOriginal,
                    'lioren_nota_id' => $result['id'] ?? null,
                    'folio' => $result['folio'] ?? null,
                    'rut_receptor' => $rutReceptor,
                    'razon_social' => $razonSocial,
                    'monto_neto' => $result['montoneto'] ?? 0,
                    'monto_iva' => $result['montoiva'] ?? 0,
                    'monto_total' => $result['montototal'] ?? 0,
                    'pdf_base64' => $result['pdf'] ?? null,
                    'xml_base64' => $result['xml'] ?? null,
                    'status' => 'emitida',
                    'glosa' => $glosa,
                    'emitida_at' => now(),
                ]);

                Log::channel('single')->info("✅ Nota de Crédito #{$result['folio']} emitida exitosamente");

                // Actualizar nota en Shopify si está habilitado
                if ($config->shopify_visibility_enabled && isset($result['folio'])) {
                    $this->updateShopifyOrderNote($orderId, "Nota de Crédito Lioren #{$result['folio']}", $config);
                }

            } else {
                Log::channel('single')->error('Error al emitir Nota de Crédito en Lioren', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // Guardar error en BD
                \App\Models\NotaCredito::create([
                    'shopify_order_id' => $orderId,
                    'shopify_order_number' => $orderNumber,
                    'tipo_documento_original' => $tipoDocOriginal,
                    'folio_original' => $folioOriginal,
                    'rut_receptor' => $rutReceptor,
                    'razon_social' => $razonSocial,
                    'monto_neto' => 0,
                    'monto_iva' => 0,
                    'monto_total' => 0,
                    'status' => 'error',
                    'glosa' => $glosa,
                    'error_message' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::channel('single')->error('Excepción al emitir Nota de Crédito: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Listar notas de crédito emitidas
     */
    public function notasCredito()
    {
        $notasCredito = \App\Models\NotaCredito::orderBy('created_at', 'desc')->paginate(20);
        return view('integracion.notas-credito', compact('notasCredito'));
    }

    /**
     * Descargar PDF de nota de crédito
     */
    public function notaCreditoPdf($id)
    {
        $notaCredito = \App\Models\NotaCredito::findOrFail($id);

        if (!$notaCredito->pdf_base64) {
            abort(404, 'PDF no disponible');
        }

        $pdf = base64_decode($notaCredito->pdf_base64);
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=nota_credito_{$notaCredito->folio}.pdf");
    }

    /**
     * Descargar XML de nota de crédito
     */
    public function notaCreditoXml($id)
    {
        $notaCredito = \App\Models\NotaCredito::findOrFail($id);

        if (!$notaCredito->xml_base64) {
            abort(404, 'XML no disponible');
        }

        $xml = base64_decode($notaCredito->xml_base64);
        
        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', "attachment; filename=nota_credito_{$notaCredito->folio}.xml");
    }
}
