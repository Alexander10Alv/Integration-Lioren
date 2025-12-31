<?php
/**
 * Funciones auxiliares para la integración Shopify - Lioren
 */

/**
 * Valida las credenciales de Shopify
 * 
 * @param string $tienda Nombre de la tienda (ejemplo.myshopify.com)
 * @param string $token Access token de Shopify
 * @return array ['success' => bool, 'message' => string, 'data' => array]
 */
function validarShopify($tienda, $token) {
    try {
        $url = "https://{$tienda}/admin/api/2024-01/shop.json";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Shopify-Access-Token: {$token}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => "Error de conexión: {$error}",
                'data' => null
            ];
        }
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'message' => "Conexión exitosa con Shopify",
                'data' => $data['shop'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'message' => "Credenciales inválidas (HTTP {$httpCode})",
                'data' => null
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Error: " . $e->getMessage(),
            'data' => null
        ];
    }
}

/**
 * Valida las credenciales de Lioren
 * 
 * @param string $api_key API Key de Lioren
 * @return array ['success' => bool, 'message' => string]
 */
function validarLioren($api_key) {
    try {
        $url = "https://www.lioren.cl/api/productos";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$api_key}",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => "Error de conexión: {$error}"
            ];
        }
        
        if ($httpCode === 200 || $httpCode === 201) {
            return [
                'success' => true,
                'message' => "Conexión exitosa con Lioren"
            ];
        } else {
            return [
                'success' => false,
                'message' => "API Key inválida (HTTP {$httpCode})"
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Error: " . $e->getMessage()
        ];
    }
}

/**
 * Crea un webhook en Shopify
 * 
 * @param string $tienda Nombre de la tienda
 * @param string $token Access token
 * @param string $topic Tema del webhook (ej: orders/create)
 * @param string $url URL donde se recibirá el webhook
 * @return array ['success' => bool, 'webhook_id' => int|null, 'message' => string]
 */
function crearWebhook($tienda, $token, $topic, $url) {
    try {
        $apiUrl = "https://{$tienda}/admin/api/2024-01/webhooks.json";
        
        $data = [
            'webhook' => [
                'topic' => $topic,
                'address' => $url,
                'format' => 'json'
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Shopify-Access-Token: {$token}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'webhook_id' => null,
                'message' => "Error: {$error}"
            ];
        }
        
        if ($httpCode === 201) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'webhook_id' => $result['webhook']['id'] ?? null,
                'message' => "Webhook creado exitosamente"
            ];
        } else {
            $result = json_decode($response, true);
            return [
                'success' => false,
                'webhook_id' => null,
                'message' => "Error al crear webhook (HTTP {$httpCode}): " . ($result['errors'] ?? 'Error desconocido')
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'webhook_id' => null,
            'message' => "Error: " . $e->getMessage()
        ];
    }
}

/**
 * Obtiene productos de Shopify
 * 
 * @param string $tienda Nombre de la tienda
 * @param string $token Access token
 * @param int $limit Límite de productos a obtener
 * @return array ['success' => bool, 'products' => array, 'message' => string]
 */
function obtenerProductosShopify($tienda, $token, $limit = 10) {
    try {
        $url = "https://{$tienda}/admin/api/2024-01/products.json?limit={$limit}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Shopify-Access-Token: {$token}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'products' => [],
                'message' => "Error: {$error}"
            ];
        }
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'products' => $data['products'] ?? [],
                'message' => "Productos obtenidos exitosamente"
            ];
        } else {
            return [
                'success' => false,
                'products' => [],
                'message' => "Error al obtener productos (HTTP {$httpCode})"
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'products' => [],
            'message' => "Error: " . $e->getMessage()
        ];
    }
}

/**
 * Crea un producto en Lioren
 * 
 * @param string $api_key API Key de Lioren
 * @param array $datos_producto Datos del producto
 * @return array ['success' => bool, 'message' => string, 'product_id' => mixed]
 */
function crearProductoLioren($api_key, $datos_producto) {
    try {
        $url = "https://www.lioren.cl/api/productos";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos_producto));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$api_key}",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => "Error: {$error}",
                'product_id' => null
            ];
        }
        
        if ($httpCode === 200 || $httpCode === 201) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'message' => "Producto creado en Lioren",
                'product_id' => $result['id'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'message' => "Error al crear producto (HTTP {$httpCode}): {$response}",
                'product_id' => null
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Error: " . $e->getMessage(),
            'product_id' => null
        ];
    }
}

/**
 * Mapea un producto de Shopify a formato Lioren
 * 
 * @param array $producto_shopify Producto de Shopify
 * @return array Producto en formato Lioren
 */
function mapearProductoShopifyALioren($producto_shopify) {
    $variant = $producto_shopify['variants'][0] ?? [];
    $precio = floatval($variant['price'] ?? 0);
    
    // Calcular precio neto (sin IVA 19%)
    $preciocompraneto = round($precio / 1.19, 2);
    $precioventabruto = $precio;
    
    return [
        'nombre' => $producto_shopify['title'] ?? 'Producto sin nombre',
        'codigo' => $variant['sku'] ?? 'SKU-' . ($producto_shopify['id'] ?? rand(1000, 9999)),
        'fraccionable' => 0, // No fraccionable
        'exento' => 0, // Afecto a IVA
        'preciocompraneto' => $preciocompraneto,
        'precioventabruto' => $precioventabruto,
        'unidad' => 'Unidad',
        'descripcion' => strip_tags($producto_shopify['body_html'] ?? ''),
    ];
}

/**
 * Registra un mensaje en el archivo de log
 * 
 * @param string $mensaje Mensaje a registrar
 * @param string $archivo Nombre del archivo de log
 * @return bool
 */
function registrarLog($mensaje, $archivo = 'integracion.log') {
    try {
        $logDir = __DIR__ . '/logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/' . $archivo;
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$mensaje}\n";
        
        return file_put_contents($logFile, $logMessage, FILE_APPEND) !== false;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Valida la firma HMAC de un webhook de Shopify
 * 
 * @param string $data Datos recibidos del webhook
 * @param string $hmac_header HMAC del header
 * @param string $secret API Secret de Shopify
 * @return bool
 */
function validarHmacShopify($data, $hmac_header, $secret) {
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
    return hash_equals($calculated_hmac, $hmac_header);
}

/**
 * Crea una venta en Lioren desde un pedido de Shopify
 * 
 * @param string $api_key API Key de Lioren
 * @param array $order Pedido de Shopify
 * @return array ['success' => bool, 'message' => string]
 */
function crearVentaLioren($api_key, $order) {
    try {
        $url = "https://www.lioren.cl/api/v1/sales";
        
        $items = [];
        foreach ($order['line_items'] ?? [] as $item) {
            $items[] = [
                'product_id' => $item['product_id'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'price' => floatval($item['price'] ?? 0),
                'sku' => $item['sku'] ?? ''
            ];
        }
        
        $data = [
            'external_id' => strval($order['id'] ?? ''),
            'total' => floatval($order['total_price'] ?? 0),
            'items' => $items,
            'customer_email' => $order['customer']['email'] ?? '',
            'customer_name' => ($order['customer']['first_name'] ?? '') . ' ' . ($order['customer']['last_name'] ?? ''),
            'status' => 'completed'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$api_key}",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 || $httpCode === 201) {
            return [
                'success' => true,
                'message' => "Venta creada en Lioren"
            ];
        } else {
            return [
                'success' => false,
                'message' => "Error al crear venta (HTTP {$httpCode})"
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Error: " . $e->getMessage()
        ];
    }
}
