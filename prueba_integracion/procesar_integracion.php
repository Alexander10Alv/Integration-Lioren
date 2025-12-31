<?php
/**
 * Procesa la integración entre Shopify y Lioren
 * Este script realiza todos los pasos de configuración automática
 */

require_once 'funciones.php';

// Iniciar sesión para mantener datos
session_start();

// Obtener datos del formulario
$shopify_tienda = $_POST['shopify_tienda'] ?? '';
$shopify_token = $_POST['shopify_token'] ?? '';
$shopify_secret = $_POST['shopify_secret'] ?? '';
$lioren_api_key = $_POST['lioren_api_key'] ?? '';
$webhook_url = $_POST['webhook_url'] ?? '';

// Guardar credenciales en sesión para el webhook receiver
$_SESSION['shopify_secret'] = $shopify_secret;
$_SESSION['lioren_api_key'] = $lioren_api_key;

// Registrar inicio del proceso
registrarLog("=== INICIO DE INTEGRACIÓN ===");
registrarLog("Tienda: {$shopify_tienda}");
registrarLog("URL Webhook: {$webhook_url}");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando Integración...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .step {
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #e0e0e0;
            background: #f9f9f9;
        }
        .step.success {
            border-left-color: #4caf50;
            background: #f1f8f4;
        }
        .step.error {
            border-left-color: #f44336;
            background: #fef1f0;
        }
        .step.processing {
            border-left-color: #2196f3;
            background: #e3f2fd;
        }
        .step-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .step-message {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .summary {
            margin-top: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
        }
        .summary h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .summary-item {
            margin: 10px 0;
            font-size: 15px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .product-list {
            margin-top: 10px;
            padding-left: 20px;
        }
        .product-item {
            font-size: 13px;
            color: #555;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Procesando Integración</h1>

<?php

// PASO 1: VALIDAR CREDENCIALES SHOPIFY
echo '<div class="step processing">';
echo '<div class="step-title"><span class="icon">📦</span> PASO 1: Validando credenciales de Shopify...</div>';
echo '</div>';
flush();
ob_flush();

$validacion_shopify = validarShopify($shopify_tienda, $shopify_token);

if ($validacion_shopify['success']) {
    $shop_name = $validacion_shopify['data']['name'] ?? $shopify_tienda;
    echo '<div class="step success">';
    echo '<div class="step-title"><span class="icon">✅</span> Conexión con Shopify exitosa</div>';
    echo '<div class="step-message">Tienda: ' . htmlspecialchars($shop_name) . '</div>';
    echo '</div>';
    registrarLog("✅ Conexión con Shopify exitosa: {$shop_name}");
} else {
    echo '<div class="step error">';
    echo '<div class="step-title"><span class="icon">❌</span> Error en Shopify</div>';
    echo '<div class="step-message">' . htmlspecialchars($validacion_shopify['message']) . '</div>';
    echo '</div>';
    registrarLog("❌ Error en Shopify: {$validacion_shopify['message']}");
    echo '<a href="index.php" class="btn">← Volver al formulario</a>';
    echo '</div></body></html>';
    exit;
}

// PASO 2: VALIDAR CREDENCIALES LIOREN
echo '<div class="step processing">';
echo '<div class="step-title"><span class="icon">🏪</span> PASO 2: Validando credenciales de Lioren...</div>';
echo '</div>';
flush();
ob_flush();

$validacion_lioren = validarLioren($lioren_api_key);

if ($validacion_lioren['success']) {
    echo '<div class="step success">';
    echo '<div class="step-title"><span class="icon">✅</span> Conexión con Lioren exitosa</div>';
    echo '<div class="step-message">API Key válida y funcionando correctamente</div>';
    echo '</div>';
    registrarLog("✅ Conexión con Lioren exitosa");
} else {
    echo '<div class="step error">';
    echo '<div class="step-title"><span class="icon">❌</span> Error en Lioren</div>';
    echo '<div class="step-message">' . htmlspecialchars($validacion_lioren['message']) . '</div>';
    echo '</div>';
    registrarLog("❌ Error en Lioren: {$validacion_lioren['message']}");
    echo '<a href="index.php" class="btn">← Volver al formulario</a>';
    echo '</div></body></html>';
    exit;
}

// PASO 3: CREAR WEBHOOKS EN SHOPIFY
echo '<div class="step processing">';
echo '<div class="step-title"><span class="icon">🔔</span> PASO 3: Creando webhooks en Shopify...</div>';
echo '</div>';
flush();
ob_flush();

$webhooks = [
    ['topic' => 'orders/create', 'evento' => 'order_create', 'nombre' => 'Nuevos Pedidos'],
    ['topic' => 'products/create', 'evento' => 'product_create', 'nombre' => 'Productos Creados'],
    ['topic' => 'products/update', 'evento' => 'product_update', 'nombre' => 'Productos Actualizados'],
    ['topic' => 'inventory_levels/update', 'evento' => 'inventory_update', 'nombre' => 'Inventario Actualizado']
];

$webhooks_creados = 0;
$webhook_errors = [];

foreach ($webhooks as $webhook) {
    $url_completa = $webhook_url . '?evento=' . $webhook['evento'];
    $resultado = crearWebhook($shopify_tienda, $shopify_token, $webhook['topic'], $url_completa);
    
    if ($resultado['success']) {
        echo '<div class="step success">';
        echo '<div class="step-title"><span class="icon">✅</span> Webhook: ' . htmlspecialchars($webhook['nombre']) . '</div>';
        echo '<div class="step-message">Topic: ' . htmlspecialchars($webhook['topic']) . ' (ID: ' . $resultado['webhook_id'] . ')</div>';
        echo '</div>';
        registrarLog("✅ Webhook creado: {$webhook['topic']} (ID: {$resultado['webhook_id']})");
        $webhooks_creados++;
    } else {
        echo '<div class="step error">';
        echo '<div class="step-title"><span class="icon">⚠️</span> Error en webhook: ' . htmlspecialchars($webhook['nombre']) . '</div>';
        echo '<div class="step-message">' . htmlspecialchars($resultado['message']) . '</div>';
        echo '</div>';
        registrarLog("⚠️ Error en webhook {$webhook['topic']}: {$resultado['message']}");
        $webhook_errors[] = $webhook['nombre'];
    }
    flush();
    ob_flush();
}

// PASO 4: SINCRONIZACIÓN INICIAL DE PRODUCTOS
echo '<div class="step processing">';
echo '<div class="step-title"><span class="icon">📦</span> PASO 4: Sincronizando productos de Shopify a Lioren...</div>';
echo '</div>';
flush();
ob_flush();

$resultado_productos = obtenerProductosShopify($shopify_tienda, $shopify_token, 10);

if ($resultado_productos['success']) {
    $productos = $resultado_productos['products'];
    $productos_sincronizados = 0;
    $productos_error = [];
    
    echo '<div class="step success">';
    echo '<div class="step-title"><span class="icon">📥</span> Productos obtenidos de Shopify: ' . count($productos) . '</div>';
    echo '<div class="product-list">';
    
    foreach ($productos as $producto) {
        $producto_lioren = mapearProductoShopifyALioren($producto);
        $resultado_crear = crearProductoLioren($lioren_api_key, $producto_lioren);
        
        if ($resultado_crear['success']) {
            echo '<div class="product-item">✅ ' . htmlspecialchars($producto['title']) . '</div>';
            registrarLog("✅ Producto sincronizado: {$producto['title']}");
            $productos_sincronizados++;
        } else {
            echo '<div class="product-item">⚠️ ' . htmlspecialchars($producto['title']) . ' - ' . htmlspecialchars($resultado_crear['message']) . '</div>';
            registrarLog("⚠️ Error al sincronizar producto {$producto['title']}: {$resultado_crear['message']}");
            $productos_error[] = $producto['title'];
        }
        flush();
        ob_flush();
    }
    
    echo '</div></div>';
    
    echo '<div class="step success">';
    echo '<div class="step-title"><span class="icon">✅</span> Sincronización completada</div>';
    echo '<div class="step-message">' . $productos_sincronizados . ' de ' . count($productos) . ' productos sincronizados exitosamente</div>';
    echo '</div>';
    
} else {
    echo '<div class="step error">';
    echo '<div class="step-title"><span class="icon">❌</span> Error al obtener productos</div>';
    echo '<div class="step-message">' . htmlspecialchars($resultado_productos['message']) . '</div>';
    echo '</div>';
    registrarLog("❌ Error al obtener productos: {$resultado_productos['message']}");
    $productos_sincronizados = 0;
}

// RESUMEN FINAL
registrarLog("=== INTEGRACIÓN COMPLETADA ===");
registrarLog("Webhooks creados: {$webhooks_creados}");
registrarLog("Productos sincronizados: {$productos_sincronizados}");

?>

        <div class="summary">
            <h2>🎉 ¡INTEGRACIÓN COMPLETADA!</h2>
            
            <div class="summary-item">✅ <strong>Conexión con Shopify:</strong> OK</div>
            <div class="summary-item">✅ <strong>Conexión con Lioren:</strong> OK</div>
            <div class="summary-item">✅ <strong>Webhooks creados:</strong> <?php echo $webhooks_creados; ?> de 4</div>
            <div class="summary-item">✅ <strong>Productos sincronizados:</strong> <?php echo $productos_sincronizados ?? 0; ?></div>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3);">
                <strong>📡 Eventos que se sincronizarán automáticamente:</strong>
                <ul style="margin-top: 10px; padding-left: 20px;">
                    <li>Nuevos pedidos en Shopify → Se crearán en Lioren</li>
                    <li>Nuevos productos en Shopify → Se crearán en Lioren</li>
                    <li>Productos actualizados → Se actualizarán en Lioren</li>
                    <li>Cambios de inventario → Se actualizarán en Lioren</li>
                </ul>
            </div>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3);">
                <strong>🔗 URL del receptor de webhooks:</strong><br>
                <code style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 5px;">
                    <?php echo htmlspecialchars($webhook_url); ?>
                </code>
            </div>
            
            <a href="index.php" class="btn">← Volver al inicio</a>
        </div>

    </div>
</body>
</html>
