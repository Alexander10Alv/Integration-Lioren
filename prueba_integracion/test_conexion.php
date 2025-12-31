<?php
/**
 * Script de prueba de conexión
 * Útil para verificar que las funciones básicas funcionan correctamente
 */

require_once 'funciones.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #667eea;
        }
        .success {
            color: #4caf50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            color: #2196f3;
        }
        pre {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        form {
            margin: 20px 0;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Conexión - Shopify & Lioren</h1>

        <div class="test-section">
            <h3>📋 Información del Sistema</h3>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>cURL:</strong> <?php echo function_exists('curl_version') ? '✅ Habilitado' : '❌ No disponible'; ?></p>
            <?php if (function_exists('curl_version')): ?>
                <p><strong>cURL Version:</strong> <?php echo curl_version()['version']; ?></p>
            <?php endif; ?>
            <p><strong>Directorio de logs:</strong> <?php echo is_writable(__DIR__ . '/logs') ? '✅ Escribible' : '⚠️ No escribible'; ?></p>
        </div>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            
            <?php
            $shopify_tienda = $_POST['shopify_tienda'] ?? '';
            $shopify_token = $_POST['shopify_token'] ?? '';
            $lioren_api_key = $_POST['lioren_api_key'] ?? '';
            ?>

            <?php if (!empty($shopify_tienda) && !empty($shopify_token)): ?>
                <div class="test-section">
                    <h3>📦 Test de Shopify</h3>
                    <?php
                    $resultado = validarShopify($shopify_tienda, $shopify_token);
                    if ($resultado['success']):
                        $shop = $resultado['data'];
                    ?>
                        <p class="success">✅ Conexión exitosa con Shopify</p>
                        <p><strong>Tienda:</strong> <?php echo htmlspecialchars($shop['name'] ?? 'N/A'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($shop['email'] ?? 'N/A'); ?></p>
                        <p><strong>Dominio:</strong> <?php echo htmlspecialchars($shop['domain'] ?? 'N/A'); ?></p>
                        <p><strong>Moneda:</strong> <?php echo htmlspecialchars($shop['currency'] ?? 'N/A'); ?></p>
                    <?php else: ?>
                        <p class="error">❌ Error: <?php echo htmlspecialchars($resultado['message']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($lioren_api_key)): ?>
                <div class="test-section">
                    <h3>🏪 Test de Lioren</h3>
                    <?php
                    $resultado = validarLioren($lioren_api_key);
                    if ($resultado['success']):
                    ?>
                        <p class="success">✅ Conexión exitosa con Lioren</p>
                        <p class="info">API Key válida y funcionando correctamente</p>
                    <?php else: ?>
                        <p class="error">❌ Error: <?php echo htmlspecialchars($resultado['message']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($shopify_tienda) && !empty($shopify_token)): ?>
                <div class="test-section">
                    <h3>📦 Test de Productos de Shopify</h3>
                    <?php
                    $resultado = obtenerProductosShopify($shopify_tienda, $shopify_token, 3);
                    if ($resultado['success']):
                        $productos = $resultado['products'];
                    ?>
                        <p class="success">✅ Productos obtenidos: <?php echo count($productos); ?></p>
                        <?php if (count($productos) > 0): ?>
                            <p><strong>Primeros productos:</strong></p>
                            <ul>
                                <?php foreach ($productos as $producto): ?>
                                    <li>
                                        <?php echo htmlspecialchars($producto['title']); ?>
                                        (ID: <?php echo $producto['id']; ?>)
                                        - Variantes: <?php echo count($producto['variants']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="info">ℹ️ No hay productos en la tienda</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="error">❌ Error: <?php echo htmlspecialchars($resultado['message']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>

            <div class="test-section">
                <h3>🔑 Ingresa tus credenciales para probar</h3>
                <form method="POST">
                    <label>Tienda Shopify:</label>
                    <input type="text" name="shopify_tienda" placeholder="ejemplo.myshopify.com" required>
                    
                    <label>Access Token Shopify:</label>
                    <input type="password" name="shopify_token" placeholder="shpat_xxxxx" required>
                    
                    <label>API Key Lioren:</label>
                    <input type="password" name="lioren_api_key" placeholder="tu_api_key">
                    
                    <button type="submit">🧪 Probar Conexiones</button>
                </form>
            </div>

        <?php endif; ?>

        <div class="test-section">
            <h3>📝 Funciones Disponibles</h3>
            <ul>
                <li>✅ validarShopify()</li>
                <li>✅ validarLioren()</li>
                <li>✅ crearWebhook()</li>
                <li>✅ obtenerProductosShopify()</li>
                <li>✅ crearProductoLioren()</li>
                <li>✅ mapearProductoShopifyALioren()</li>
                <li>✅ registrarLog()</li>
                <li>✅ validarHmacShopify()</li>
                <li>✅ crearVentaLioren()</li>
            </ul>
        </div>

        <div class="test-section">
            <p class="info">
                <strong>💡 Tip:</strong> Si las pruebas son exitosas, puedes proceder a usar el 
                <a href="index.php">formulario de integración completa</a>
            </p>
        </div>

    </div>
</body>
</html>
