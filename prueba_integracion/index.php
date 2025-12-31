<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integración Shopify - Lioren | Módulo de Prueba</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .btn:active {
            transform: translateY(0);
        }
        .section-title {
            color: #667eea;
            font-size: 18px;
            font-weight: 600;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .section-title:first-of-type {
            margin-top: 0;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-info {
            background: #e3f2fd;
            color: #1976d2;
            border-left: 4px solid #1976d2;
        }
        .icon {
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="dashboard.php" style="color: #667eea; text-decoration: none; font-size: 14px;">← Volver al Dashboard</a>
        </div>
        <h1>🔗 Integración Shopify - Lioren</h1>
        <p class="subtitle">Módulo de Prueba - Configuración Automática</p>

        <div class="alert alert-info">
            <span class="icon">ℹ️</span>
            <strong>Importante:</strong> Este módulo creará webhooks automáticamente y sincronizará productos. Asegúrate de tener las credenciales correctas.
        </div>

        <form action="procesar_integracion.php" method="POST">
            
            <div class="section-title">📦 Credenciales de Shopify</div>
            
            <div class="form-group">
                <label for="shopify_tienda">Nombre de Tienda</label>
                <input 
                    type="text" 
                    id="shopify_tienda" 
                    name="shopify_tienda" 
                    placeholder="ejemplo.myshopify.com"
                    required
                    pattern="[a-zA-Z0-9\-]+\.myshopify\.com"
                >
                <div class="help-text">Formato: tu-tienda.myshopify.com</div>
            </div>

            <div class="form-group">
                <label for="shopify_token">Access Token</label>
                <input 
                    type="password" 
                    id="shopify_token" 
                    name="shopify_token" 
                    placeholder="shpat_xxxxxxxxxxxxx"
                    required
                    minlength="20"
                >
                <div class="help-text">Token de API de tu app personalizada de Shopify</div>
            </div>

            <div class="form-group">
                <label for="shopify_secret">API Secret (para webhooks)</label>
                <input 
                    type="password" 
                    id="shopify_secret" 
                    name="shopify_secret" 
                    placeholder="shpss_xxxxxxxxxxxxx"
                    required
                    minlength="20"
                >
                <div class="help-text">Secret key para validar webhooks de Shopify</div>
            </div>

            <div class="section-title">🏪 Credenciales de Lioren</div>

            <div class="form-group">
                <label for="lioren_api_key">API Key (Bearer Token)</label>
                <input 
                    type="password" 
                    id="lioren_api_key" 
                    name="lioren_api_key" 
                    placeholder="tu_api_key_de_lioren"
                    required
                    minlength="10"
                >
                <div class="help-text">Token de autenticación de la API de Lioren</div>
            </div>

            <div class="section-title">🔔 Configuración de Webhooks</div>

            <div class="form-group">
                <label for="webhook_url">URL del Receptor de Webhooks</label>
                <input 
                    type="text" 
                    id="webhook_url" 
                    name="webhook_url" 
                    value="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/webhook_receiver.php'; ?>"
                    required
                    pattern="https?://.+"
                >
                <div class="help-text">URL pública donde Shopify enviará los eventos</div>
            </div>

            <button type="submit" class="btn">
                🚀 Conectar y Configurar Integración
            </button>

        </form>
    </div>
</body>
</html>
