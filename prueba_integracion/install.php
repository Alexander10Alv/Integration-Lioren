<?php
/**
 * Script de Instalación y Verificación
 * Verifica que el sistema esté correctamente configurado
 */

$checks = [];
$allPassed = true;

// Check 1: Versión de PHP
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '7.4.0', '>=');
$checks[] = [
    'name' => 'Versión de PHP',
    'status' => $phpOk,
    'message' => $phpOk ? "PHP {$phpVersion} ✅" : "PHP {$phpVersion} ❌ (Se requiere 7.4+)",
    'required' => true
];
if (!$phpOk) $allPassed = false;

// Check 2: Extensión cURL
$curlOk = function_exists('curl_version');
$checks[] = [
    'name' => 'Extensión cURL',
    'status' => $curlOk,
    'message' => $curlOk ? 'Habilitada ✅' : 'No disponible ❌',
    'required' => true
];
if (!$curlOk) $allPassed = false;

// Check 3: Extensión JSON
$jsonOk = function_exists('json_encode');
$checks[] = [
    'name' => 'Extensión JSON',
    'status' => $jsonOk,
    'message' => $jsonOk ? 'Habilitada ✅' : 'No disponible ❌',
    'required' => true
];
if (!$jsonOk) $allPassed = false;

// Check 4: Carpeta de logs
$logDir = __DIR__ . '/logs';
$logDirExists = is_dir($logDir);
$logDirWritable = $logDirExists && is_writable($logDir);
$checks[] = [
    'name' => 'Carpeta de logs',
    'status' => $logDirWritable,
    'message' => $logDirWritable ? 'Existe y es escribible ✅' : ($logDirExists ? 'Existe pero no es escribible ⚠️' : 'No existe ⚠️'),
    'required' => true,
    'fix' => !$logDirWritable ? 'Ejecuta: mkdir logs && chmod 755 logs' : null
];
if (!$logDirWritable) $allPassed = false;

// Check 5: Archivo funciones.php
$funcionesExists = file_exists(__DIR__ . '/funciones.php');
$checks[] = [
    'name' => 'Archivo funciones.php',
    'status' => $funcionesExists,
    'message' => $funcionesExists ? 'Existe ✅' : 'No encontrado ❌',
    'required' => true
];
if (!$funcionesExists) $allPassed = false;

// Check 6: Permisos de escritura
$testFile = $logDir . '/test_' . time() . '.tmp';
$canWrite = @file_put_contents($testFile, 'test') !== false;
if ($canWrite) @unlink($testFile);
$checks[] = [
    'name' => 'Permisos de escritura',
    'status' => $canWrite,
    'message' => $canWrite ? 'OK ✅' : 'No se puede escribir en logs/ ❌',
    'required' => true,
    'fix' => !$canWrite ? 'Ejecuta: chmod 755 logs' : null
];
if (!$canWrite) $allPassed = false;

// Check 7: OpenSSL (para HTTPS)
$opensslOk = extension_loaded('openssl');
$checks[] = [
    'name' => 'OpenSSL',
    'status' => $opensslOk,
    'message' => $opensslOk ? 'Habilitado ✅' : 'No disponible ⚠️',
    'required' => false
];

// Check 8: allow_url_fopen
$urlFopenOk = ini_get('allow_url_fopen');
$checks[] = [
    'name' => 'allow_url_fopen',
    'status' => $urlFopenOk,
    'message' => $urlFopenOk ? 'Habilitado ✅' : 'Deshabilitado ⚠️',
    'required' => false
];

// Check 9: Archivo de configuración
$configExists = file_exists(__DIR__ . '/config.php');
$checks[] = [
    'name' => 'Archivo config.php',
    'status' => $configExists,
    'message' => $configExists ? 'Existe ✅' : 'No encontrado (opcional) ℹ️',
    'required' => false,
    'fix' => !$configExists ? 'Copia config_ejemplo.php a config.php y edita las credenciales' : null
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Integración Shopify-Lioren</title>
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
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-banner {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }
        .status-banner.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        .status-banner.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            border-left: 4px solid #e0e0e0;
            background: #f9f9f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.passed {
            border-left-color: #4caf50;
            background: #f1f8f4;
        }
        .check-item.failed {
            border-left-color: #f44336;
            background: #fef1f0;
        }
        .check-item.warning {
            border-left-color: #ff9800;
            background: #fff8e1;
        }
        .check-name {
            font-weight: 600;
            color: #333;
        }
        .check-message {
            color: #666;
            font-size: 14px;
        }
        .fix-command {
            margin-top: 10px;
            padding: 10px;
            background: #263238;
            color: #aed581;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .actions {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 5px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            color: #1976d2;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box ul {
            margin-left: 20px;
            color: #555;
        }
        .info-box li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Instalación y Verificación</h1>
        <p class="subtitle">Sistema de Integración Shopify - Lioren</p>

        <?php if ($allPassed): ?>
            <div class="status-banner success">
                🎉 ¡Todo está listo! El sistema está correctamente configurado
            </div>
        <?php else: ?>
            <div class="status-banner error">
                ⚠️ Se encontraron problemas que deben ser corregidos
            </div>
        <?php endif; ?>

        <h2 style="margin: 30px 0 15px 0; color: #333;">Verificaciones del Sistema</h2>

        <?php foreach ($checks as $check): ?>
            <div class="check-item <?php echo $check['status'] ? 'passed' : ($check['required'] ? 'failed' : 'warning'); ?>">
                <div>
                    <div class="check-name"><?php echo htmlspecialchars($check['name']); ?></div>
                    <div class="check-message"><?php echo $check['message']; ?></div>
                    <?php if (isset($check['fix']) && $check['fix']): ?>
                        <div class="fix-command">💡 Solución: <?php echo htmlspecialchars($check['fix']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($allPassed): ?>
            <div class="info-box">
                <h3>📋 Próximos Pasos</h3>
                <ul>
                    <li>Obtén tus credenciales de Shopify y Lioren</li>
                    <li>Usa el formulario de integración para configurar la conexión</li>
                    <li>Los webhooks se crearán automáticamente</li>
                    <li>Los productos se sincronizarán inicialmente</li>
                </ul>
            </div>

            <div class="actions">
                <a href="index.php" class="btn">🚀 Ir al Formulario de Integración</a>
                <a href="test_conexion.php" class="btn btn-secondary">🧪 Probar Conexiones</a>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h3>⚠️ Acción Requerida</h3>
                <p>Corrige los problemas indicados arriba antes de continuar.</p>
                <p style="margin-top: 10px;">Una vez corregidos, recarga esta página para verificar nuevamente.</p>
            </div>

            <div class="actions">
                <a href="install.php" class="btn">🔄 Verificar Nuevamente</a>
                <a href="README.md" class="btn btn-secondary">📖 Ver Documentación</a>
            </div>
        <?php endif; ?>

        <div class="info-box" style="margin-top: 30px;">
            <h3>ℹ️ Información del Sistema</h3>
            <ul>
                <li><strong>PHP:</strong> <?php echo phpversion(); ?></li>
                <li><strong>Sistema Operativo:</strong> <?php echo PHP_OS; ?></li>
                <li><strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></li>
                <li><strong>Directorio:</strong> <?php echo __DIR__; ?></li>
                <?php if (function_exists('curl_version')): ?>
                    <li><strong>cURL:</strong> <?php echo curl_version()['version']; ?></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</body>
</html>
