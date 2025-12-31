<?php
/**
 * Ejemplo de Uso de las Funciones
 * Este archivo muestra cómo usar las funciones del sistema programáticamente
 */

require_once 'funciones.php';

// ============================================
// CONFIGURACIÓN (Reemplaza con tus datos)
// ============================================

$SHOPIFY_TIENDA = 'tu-tienda.myshopify.com';
$SHOPIFY_TOKEN = 'shpat_xxxxxxxxxxxxx';
$LIOREN_API_KEY = 'tu_api_key_aqui';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo de Uso - API</title>
    <style>
        body {
            font-family: monospace;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .example {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        pre {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .result {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-top: 10px;
        }
        .success {
            border-left-color: #4caf50;
            background: #f1f8f4;
        }
        .error {
            border-left-color: #f44336;
            background: #fef1f0;
        }
    </style>
</head>
<body>
    <h1>📚 Ejemplos de Uso de las Funciones</h1>

    <!-- EJEMPLO 1: Validar Shopify -->
    <div class="example">
        <h2>1. Validar Credenciales de Shopify</h2>
        <pre>
$resultado = validarShopify($tienda, $token);

if ($resultado['success']) {
    echo "✅ Conexión exitosa";
    echo "Tienda: " . $resultado['data']['name'];
} else {
    echo "❌ Error: " . $resultado['message'];
}
        </pre>
        
        <?php if (isset($_GET['test']) && $_GET['test'] == '1'): ?>
            <div class="result <?php echo validarShopify($SHOPIFY_TIENDA, $SHOPIFY_TOKEN)['success'] ? 'success' : 'error'; ?>">
                <strong>Resultado:</strong>
                <pre><?php print_r(validarShopify($SHOPIFY_TIENDA, $SHOPIFY_TOKEN)); ?></pre>
            </div>
        <?php else: ?>
            <a href="?test=1#ejemplo1" style="color: #667eea;">▶️ Ejecutar ejemplo</a>
        <?php endif; ?>
    </div>

    <!-- EJEMPLO 2: Validar Lioren -->
    <div class="example">
        <h2>2. Validar Credenciales de Lioren</h2>
        <pre>
$resultado = validarLioren($api_key);

if ($resultado['success']) {
    echo "✅ API Key válida";
} else {
    echo "❌ Error: " . $resultado['message'];
}
        </pre>
        
        <?php if (isset($_GET['test']) && $_GET['test'] == '2'): ?>
            <div class="result <?php echo validarLioren($LIOREN_API_KEY)['success'] ? 'success' : 'error'; ?>">
                <strong>Resultado:</strong>
                <pre><?php print_r(validarLioren($LIOREN_API_KEY)); ?></pre>
            </div>
        <?php else: ?>
            <a href="?test=2#ejemplo2" style="color: #667eea;">▶️ Ejecutar ejemplo</a>
        <?php endif; ?>
    </div>

    <!-- EJEMPLO 3: Obtener Productos -->
    <div class="example">
        <h2>3. Obtener Productos de Shopify</h2>
        <pre>
$resultado = obtenerProductosShopify($tienda, $token, 5);

if ($resultado['success']) {
    foreach ($resultado['products'] as $producto) {
        echo $producto['title'] . "\n";
    }
}
        </pre>
        
        <?php if (isset($_GET['test']) && $_GET['test'] == '3'): ?>
            <?php $resultado = obtenerProductosShopify($SHOPIFY_TIENDA, $SHOPIFY_TOKEN, 5); ?>
            <div class="result <?php echo $resultado['success'] ? 'success' : 'error'; ?>">
                <strong>Resultado:</strong>
                <?php if ($resultado['success']): ?>
                    <p>Productos encontrados: <?php echo count($resultado['products']); ?></p>
                    <ul>
                        <?php foreach ($resultado['products'] as $producto): ?>
                            <li><?php echo htmlspecialchars($producto['title']); ?> (ID: <?php echo $producto['id']; ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Error: <?php echo htmlspecialchars($resultado['message']); ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <a href="?test=3#ejemplo3" style="color: #667eea;">▶️ Ejecutar ejemplo</a>
        <?php endif; ?>
    </div>

    <!-- EJEMPLO 4: Mapear Producto -->
    <div class="example">
        <h2>4. Mapear Producto de Shopify a Lioren</h2>
        <pre>
$producto_shopify = [
    'id' => 123456,
    'title' => 'Producto de Ejemplo',
    'body_html' => '&lt;p&gt;Descripción&lt;/p&gt;',
    'variants' => [
        [
            'sku' => 'PROD-001',
            'price' => '99.99',
            'inventory_quantity' => 10
        ]
    ]
];

$producto_lioren = mapearProductoShopifyALioren($producto_shopify);
print_r($producto_lioren);
        </pre>
        
        <?php if (isset($_GET['test']) && $_GET['test'] == '4'): ?>
            <?php
            $producto_ejemplo = [
                'id' => 123456,
                'title' => 'Producto de Ejemplo',
                'body_html' => '<p>Descripción del producto</p>',
                'variants' => [
                    [
                        'sku' => 'PROD-001',
                        'price' => '99.99',
                        'inventory_quantity' => 10
                    ]
                ]
            ];
            $mapeado = mapearProductoShopifyALioren($producto_ejemplo);
            ?>
            <div class="result success">
                <strong>Resultado del mapeo:</strong>
                <pre><?php print_r($mapeado); ?></pre>
            </div>
        <?php else: ?>
            <a href="?test=4#ejemplo4" style="color: #667eea;">▶️ Ejecutar ejemplo</a>
        <?php endif; ?>
    </div>

    <!-- EJEMPLO 5: Crear Webhook -->
    <div class="example">
        <h2>5. Crear Webhook en Shopify</h2>
        <pre>
$resultado = crearWebhook(
    $tienda,
    $token,
    'orders/create',
    'https://tudominio.com/webhook_receiver.php?evento=order_create'
);

if ($resultado['success']) {
    echo "✅ Webhook creado (ID: " . $resultado['webhook_id'] . ")";
} else {
    echo "❌ Error: " . $resultado['message'];
}
        </pre>
        
        <p style="color: #666; font-size: 14px;">
            ⚠️ Este ejemplo no se ejecuta automáticamente para evitar crear webhooks duplicados.
            Usa el formulario principal para crear webhooks.
        </p>
    </div>

    <!-- EJEMPLO 6: Registrar Log -->
    <div class="example">
        <h2>6. Registrar en Log</h2>
        <pre>
registrarLog("Mensaje de prueba", "mi_log.log");
registrarLog("✅ Operación exitosa");
registrarLog("❌ Error en proceso");
        </pre>
        
        <?php if (isset($_GET['test']) && $_GET['test'] == '6'): ?>
            <?php
            $log_ok = registrarLog("Mensaje de prueba desde ejemplo_uso.php", "ejemplo.log");
            ?>
            <div class="result <?php echo $log_ok ? 'success' : 'error'; ?>">
                <strong>Resultado:</strong>
                <?php if ($log_ok): ?>
                    <p>✅ Log registrado exitosamente en logs/ejemplo.log</p>
                <?php else: ?>
                    <p>❌ Error al registrar log</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <a href="?test=6#ejemplo6" style="color: #667eea;">▶️ Ejecutar ejemplo</a>
        <?php endif; ?>
    </div>

    <!-- EJEMPLO 7: Validar HMAC -->
    <div class="example">
        <h2>7. Validar HMAC de Webhook</h2>
        <pre>
$data = file_get_contents('php://input');
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$secret = 'tu_api_secret';

$valido = validarHmacShopify($data, $hmac_header, $secret);

if ($valido) {
    echo "✅ Webhook auténtico";
} else {
    echo "❌ Webhook no válido";
}
        </pre>
        
        <p style="color: #666; font-size: 14px;">
            ℹ️ Esta función se usa automáticamente en webhook_receiver.php
        </p>
    </div>

    <!-- EJEMPLO 8: Uso Completo -->
    <div class="example">
        <h2>8. Ejemplo de Flujo Completo</h2>
        <pre>
// 1. Validar credenciales
$shopify_ok = validarShopify($tienda, $token);
$lioren_ok = validarLioren($api_key);

if (!$shopify_ok['success'] || !$lioren_ok['success']) {
    die("Error en credenciales");
}

// 2. Obtener productos de Shopify
$productos = obtenerProductosShopify($tienda, $token, 10);

// 3. Sincronizar cada producto a Lioren
foreach ($productos['products'] as $producto) {
    $producto_lioren = mapearProductoShopifyALioren($producto);
    $resultado = crearProductoLioren($api_key, $producto_lioren);
    
    if ($resultado['success']) {
        registrarLog("✅ Producto sincronizado: " . $producto['title']);
    } else {
        registrarLog("❌ Error: " . $resultado['message']);
    }
}

// 4. Crear webhooks
$webhooks = ['orders/create', 'products/create', 'products/update'];
foreach ($webhooks as $topic) {
    crearWebhook($tienda, $token, $topic, $webhook_url);
}
        </pre>
        
        <p style="color: #666; font-size: 14px;">
            💡 Este es el flujo que ejecuta procesar_integracion.php
        </p>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <a href="dashboard.php" style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px;">
            ← Volver al Dashboard
        </a>
    </div>

</body>
</html>
