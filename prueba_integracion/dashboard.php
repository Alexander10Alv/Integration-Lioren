<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Integración Shopify-Lioren</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .card-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .card-description {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }
        .card.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .card.primary .card-title,
        .card.primary .card-description {
            color: white;
        }
        .info-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .info-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 22px;
        }
        .info-section ul {
            list-style: none;
            padding: 0;
        }
        .info-section li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }
        .info-section li:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge.success {
            background: #d4edda;
            color: #155724;
        }
        .badge.warning {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>🔗 Dashboard de Integración</h1>
            <p class="subtitle">Shopify ↔️ Lioren | Módulo de Prueba</p>
        </div>

        <div class="cards-grid">
            
            <a href="index.php" class="card primary">
                <div class="card-icon">🚀</div>
                <div class="card-title">Configurar Integración</div>
                <div class="card-description">
                    Formulario principal para conectar Shopify con Lioren y crear webhooks automáticamente
                </div>
            </a>

            <a href="test_conexion.php" class="card">
                <div class="card-icon">🧪</div>
                <div class="card-title">Probar Conexiones</div>
                <div class="card-description">
                    Verifica que tus credenciales funcionen correctamente antes de la integración completa
                </div>
            </a>

            <a href="ver_logs.php" class="card">
                <div class="card-icon">📊</div>
                <div class="card-title">Ver Logs</div>
                <div class="card-description">
                    Visualiza los logs de integración y webhooks en tiempo real desde el navegador
                </div>
            </a>

            <a href="install.php" class="card">
                <div class="card-icon">🔧</div>
                <div class="card-title">Verificar Sistema</div>
                <div class="card-description">
                    Comprueba que todos los requisitos del sistema estén correctamente configurados
                </div>
            </a>

        </div>

        <div class="info-section">
            <h2>📋 Características del Sistema</h2>
            <ul>
                <li>
                    ✅ Validación automática de credenciales
                    <span class="badge success">Activo</span>
                </li>
                <li>
                    ✅ Creación automática de 4 webhooks en Shopify
                    <span class="badge success">Activo</span>
                </li>
                <li>
                    ✅ Sincronización inicial de productos
                    <span class="badge success">Activo</span>
                </li>
                <li>
                    ✅ Sincronización en tiempo real de pedidos
                    <span class="badge success">Activo</span>
                </li>
                <li>
                    ✅ Sincronización de productos creados/actualizados
                    <span class="badge success">Activo</span>
                </li>
                <li>
                    ✅ Sincronización de cambios de inventario
                    <span class="badge success">Activo</span>
                </li>
                <li>
                    ✅ Sistema de logs detallado
                    <span class="badge success">Activo</span>
                </li>
                <li>
                    ✅ Validación HMAC de webhooks
                    <span class="badge success">Activo</span>
                </li>
            </ul>
        </div>

        <div class="info-section" style="margin-top: 20px;">
            <h2>🔄 Flujo de Trabajo</h2>
            <ul>
                <li><strong>1.</strong> Verifica el sistema con "Verificar Sistema"</li>
                <li><strong>2.</strong> Prueba tus credenciales con "Probar Conexiones"</li>
                <li><strong>3.</strong> Configura la integración completa con "Configurar Integración"</li>
                <li><strong>4.</strong> Monitorea los eventos con "Ver Logs"</li>
            </ul>
        </div>

        <div class="info-section" style="margin-top: 20px;">
            <h2>📚 Recursos Adicionales</h2>
            <ul>
                <li>
                    <a href="README.md" style="color: #667eea; text-decoration: none;">
                        📖 Documentación Completa
                    </a>
                </li>
                <li>
                    <a href="config_ejemplo.php" style="color: #667eea; text-decoration: none;">
                        ⚙️ Archivo de Configuración de Ejemplo
                    </a>
                </li>
                <li>
                    <a href="https://shopify.dev/docs/api/admin-rest" target="_blank" style="color: #667eea; text-decoration: none;">
                        🔗 Documentación API de Shopify
                    </a>
                </li>
                <li>
                    <a href="https://www.lioren.cl/docs" target="_blank" style="color: #667eea; text-decoration: none;">
                        🔗 Documentación API de Lioren
                    </a>
                </li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px; color: white; font-size: 14px;">
            <p>Sistema de Integración Shopify-Lioren v1.0</p>
            <p style="margin-top: 5px; opacity: 0.8;">Módulo de Prueba | PHP <?php echo phpversion(); ?></p>
        </div>

    </div>
</body>
</html>
