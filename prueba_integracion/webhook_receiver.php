<?php
/**
 * Receptor de Webhooks de Shopify
 * Este script recibe y procesa los eventos enviados por Shopify
 */

require_once 'funciones.php';

// Iniciar sesión para obtener credenciales
session_start();

// Obtener credenciales de la sesión
$shopify_secret = $_SESSION['shopify_secret'] ?? '';
$lioren_api_key = $_SESSION['lioren_api_key'] ?? '';

// Obtener datos del webhook
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
$shop_domain = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? '';
$topic = $_SERVER['HTTP_X_SHOPIFY_TOPIC'] ?? '';
$evento = $_GET['evento'] ?? '';

// Leer el cuerpo de la petición
$data = file_get_contents('php://input');

// Registrar recepción del webhook
registrarLog("=== WEBHOOK RECIBIDO ===", "webhook_" . date('Y-m-d') . ".log");
registrarLog("Evento: {$evento}", "webhook_" . date('Y-m-d') . ".log");
registrarLog("Topic: {$topic}", "webhook_" . date('Y-m-d') . ".log");
registrarLog("Shop: {$shop_domain}", "webhook_" . date('Y-m-d') . ".log");

// Validar firma HMAC
if (!empty($shopify_secret) && !empty($hmac_header)) {
    $hmac_valido = validarHmacShopify($data, $hmac_header, $shopify_secret);
    
    if (!$hmac_valido) {
        registrarLog("❌ HMAC inválido - Webhook rechazado", "webhook_" . date('Y-m-d') . ".log");
        http_response_code(401);
        exit('Unauthorized');
    }
    
    registrarLog("✅ HMAC válido", "webhook_" . date('Y-m-d') . ".log");
}

// Decodificar datos JSON
$webhook_data = json_decode($data, true);

if (!$webhook_data) {
    registrarLog("❌ Error al decodificar JSON", "webhook_" . date('Y-m-d') . ".log");
    http_response_code(400);
    exit('Bad Request');
}

// Procesar según el tipo de evento
try {
    switch ($evento) {
        
        case 'order_create':
            registrarLog("Procesando nuevo pedido...", "webhook_" . date('Y-m-d') . ".log");
            
            // Extraer información del pedido
            $order_id = $webhook_data['id'] ?? null;
            $order_number = $webhook_data['order_number'] ?? null;
            $total = $webhook_data['total_price'] ?? 0;
            
            registrarLog("Pedido #{$order_number} (ID: {$order_id}) - Total: \${$total}", "webhook_" . date('Y-m-d') . ".log");
            
            // Crear venta en Lioren
            if (!empty($lioren_api_key)) {
                $resultado = crearVentaLioren($lioren_api_key, $webhook_data);
                
                if ($resultado['success']) {
                    registrarLog("✅ Venta creada en Lioren exitosamente", "webhook_" . date('Y-m-d') . ".log");
                } else {
                    registrarLog("⚠️ Error al crear venta en Lioren: {$resultado['message']}", "webhook_" . date('Y-m-d') . ".log");
                }
            }
            break;
            
        case 'product_create':
            registrarLog("Procesando nuevo producto...", "webhook_" . date('Y-m-d') . ".log");
            
            // Extraer información del producto
            $product_id = $webhook_data['id'] ?? null;
            $product_title = $webhook_data['title'] ?? 'Sin título';
            
            registrarLog("Producto: {$product_title} (ID: {$product_id})", "webhook_" . date('Y-m-d') . ".log");
            
            // Crear producto en Lioren
            if (!empty($lioren_api_key)) {
                $producto_lioren = mapearProductoShopifyALioren($webhook_data);
                $resultado = crearProductoLioren($lioren_api_key, $producto_lioren);
                
                if ($resultado['success']) {
                    registrarLog("✅ Producto creado en Lioren exitosamente", "webhook_" . date('Y-m-d') . ".log");
                } else {
                    registrarLog("⚠️ Error al crear producto en Lioren: {$resultado['message']}", "webhook_" . date('Y-m-d') . ".log");
                }
            }
            break;
            
        case 'product_update':
            registrarLog("Procesando actualización de producto...", "webhook_" . date('Y-m-d') . ".log");
            
            // Extraer información del producto
            $product_id = $webhook_data['id'] ?? null;
            $product_title = $webhook_data['title'] ?? 'Sin título';
            
            registrarLog("Producto actualizado: {$product_title} (ID: {$product_id})", "webhook_" . date('Y-m-d') . ".log");
            
            // Actualizar producto en Lioren
            if (!empty($lioren_api_key)) {
                $producto_lioren = mapearProductoShopifyALioren($webhook_data);
                
                // Intentar actualizar (PUT) - Nota: necesitarías el ID de Lioren
                // Por ahora solo registramos
                registrarLog("ℹ️ Actualización de producto registrada", "webhook_" . date('Y-m-d') . ".log");
            }
            break;
            
        case 'inventory_update':
            registrarLog("Procesando actualización de inventario...", "webhook_" . date('Y-m-d') . ".log");
            
            // Extraer información del inventario
            $inventory_item_id = $webhook_data['inventory_item_id'] ?? null;
            $location_id = $webhook_data['location_id'] ?? null;
            $available = $webhook_data['available'] ?? 0;
            
            registrarLog("Inventario actualizado - Item: {$inventory_item_id}, Disponible: {$available}", "webhook_" . date('Y-m-d') . ".log");
            
            // Actualizar stock en Lioren
            if (!empty($lioren_api_key)) {
                // Aquí iría la lógica para actualizar el stock en Lioren
                registrarLog("ℹ️ Actualización de inventario registrada", "webhook_" . date('Y-m-d') . ".log");
            }
            break;
            
        default:
            registrarLog("⚠️ Evento no reconocido: {$evento}", "webhook_" . date('Y-m-d') . ".log");
            break;
    }
    
    // Guardar datos completos del webhook para debugging
    $debug_file = __DIR__ . '/logs/webhook_data_' . date('Y-m-d_His') . '.json';
    file_put_contents($debug_file, json_encode($webhook_data, JSON_PRETTY_PRINT));
    registrarLog("Datos completos guardados en: " . basename($debug_file), "webhook_" . date('Y-m-d') . ".log");
    
    registrarLog("=== WEBHOOK PROCESADO EXITOSAMENTE ===", "webhook_" . date('Y-m-d') . ".log");
    
    // Responder 200 OK a Shopify
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Webhook procesado correctamente']);
    
} catch (Exception $e) {
    registrarLog("❌ ERROR: " . $e->getMessage(), "webhook_" . date('Y-m-d') . ".log");
    registrarLog("Stack trace: " . $e->getTraceAsString(), "webhook_" . date('Y-m-d') . ".log");
    
    // Responder 500 a Shopify para que reintente
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al procesar webhook']);
}
