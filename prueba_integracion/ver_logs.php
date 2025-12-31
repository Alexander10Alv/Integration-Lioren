<?php
/**
 * Visor de Logs
 * Permite ver los logs generados por el sistema desde el navegador
 */

$logDir = __DIR__ . '/logs/';
$selectedLog = $_GET['log'] ?? '';

// Obtener lista de archivos de log
$logFiles = [];
if (is_dir($logDir)) {
    $files = scandir($logDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && (strpos($file, '.log') !== false || strpos($file, '.json') !== false)) {
            $logFiles[] = $file;
        }
    }
    rsort($logFiles); // Más recientes primero
}

// Leer contenido del log seleccionado
$logContent = '';
if (!empty($selectedLog) && file_exists($logDir . $selectedLog)) {
    $logContent = file_get_contents($logDir . $selectedLog);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor de Logs - Integración Shopify-Lioren</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .controls {
            background: #252526;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        select {
            width: 100%;
            padding: 10px;
            background: #3c3c3c;
            color: #d4d4d4;
            border: 1px solid #555;
            border-radius: 4px;
            font-size: 14px;
        }
        .log-viewer {
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            border-radius: 8px;
            padding: 20px;
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
            font-size: 13px;
            line-height: 1.6;
        }
        .log-line {
            margin: 5px 0;
            padding: 5px;
            border-left: 3px solid transparent;
        }
        .log-line:hover {
            background: #2d2d30;
        }
        .log-line.success {
            border-left-color: #4ec9b0;
        }
        .log-line.error {
            border-left-color: #f48771;
            color: #f48771;
        }
        .log-line.warning {
            border-left-color: #dcdcaa;
            color: #dcdcaa;
        }
        .log-line.info {
            border-left-color: #569cd6;
        }
        .timestamp {
            color: #858585;
            margin-right: 10px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #858585;
        }
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #0e639c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .btn:hover {
            background: #1177bb;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            background: #252526;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #4ec9b0;
        }
        .stat-label {
            font-size: 12px;
            color: #858585;
            margin-top: 5px;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Visor de Logs - Integración Shopify-Lioren</h1>

        <?php if (count($logFiles) > 0): ?>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($logFiles); ?></div>
                    <div class="stat-label">Archivos de Log</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $totalSize = 0;
                        foreach ($logFiles as $file) {
                            if (file_exists($logDir . $file)) {
                                $totalSize += filesize($logDir . $file);
                            }
                        }
                        echo round($totalSize / 1024, 2);
                        ?>
                    </div>
                    <div class="stat-label">KB Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        if (!empty($logContent)) {
                            echo substr_count($logContent, "\n");
                        } else {
                            echo '0';
                        }
                        ?>
                    </div>
                    <div class="stat-label">Líneas</div>
                </div>
            </div>

            <div class="controls">
                <form method="GET">
                    <select name="log" onchange="this.form.submit()">
                        <option value="">Selecciona un archivo de log...</option>
                        <?php foreach ($logFiles as $file): ?>
                            <option value="<?php echo htmlspecialchars($file); ?>" 
                                    <?php echo $selectedLog === $file ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($file); ?>
                                (<?php echo round(filesize($logDir . $file) / 1024, 2); ?> KB)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if (!empty($logContent)): ?>
                <div class="log-viewer">
                    <?php
                    // Determinar si es JSON
                    $isJson = strpos($selectedLog, '.json') !== false;
                    
                    if ($isJson) {
                        // Mostrar JSON formateado
                        $jsonData = json_decode($logContent, true);
                        if ($jsonData) {
                            echo '<pre>' . htmlspecialchars(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
                        } else {
                            echo '<pre>' . htmlspecialchars($logContent) . '</pre>';
                        }
                    } else {
                        // Mostrar log línea por línea con colores
                        $lines = explode("\n", $logContent);
                        foreach ($lines as $line) {
                            if (empty(trim($line))) continue;
                            
                            $class = '';
                            if (strpos($line, '✅') !== false || strpos($line, 'exitosa') !== false || strpos($line, 'success') !== false) {
                                $class = 'success';
                            } elseif (strpos($line, '❌') !== false || strpos($line, 'ERROR') !== false || strpos($line, 'Error') !== false) {
                                $class = 'error';
                            } elseif (strpos($line, '⚠️') !== false || strpos($line, 'WARNING') !== false) {
                                $class = 'warning';
                            } elseif (strpos($line, 'ℹ️') !== false || strpos($line, 'INFO') !== false) {
                                $class = 'info';
                            }
                            
                            // Extraer timestamp si existe
                            $displayLine = $line;
                            if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                                $timestamp = $matches[1];
                                $message = str_replace($matches[0], '', $line);
                                $displayLine = '<span class="timestamp">[' . htmlspecialchars($timestamp) . ']</span>' . htmlspecialchars($message);
                            } else {
                                $displayLine = htmlspecialchars($line);
                            }
                            
                            echo '<div class="log-line ' . $class . '">' . $displayLine . '</div>';
                        }
                    }
                    ?>
                </div>
            <?php else: ?>
                <div class="log-viewer">
                    <div class="empty-state">
                        <div class="empty-state-icon">📄</div>
                        <p>Selecciona un archivo de log para ver su contenido</p>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="log-viewer">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>No hay archivos de log disponibles</p>
                    <p style="margin-top: 10px; font-size: 14px;">
                        Los logs se generarán automáticamente cuando uses el sistema de integración
                    </p>
                    <a href="index.php" class="btn">Ir al formulario de integración</a>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px; text-align: center;">
            <a href="index.php" class="btn">← Volver al inicio</a>
            <?php if (!empty($selectedLog)): ?>
                <a href="?log=<?php echo urlencode($selectedLog); ?>&refresh=1" class="btn">🔄 Refrescar</a>
            <?php endif; ?>
        </div>

    </div>

    <?php if (!empty($selectedLog)): ?>
    <script>
        // Auto-refresh cada 5 segundos si hay un log seleccionado
        setTimeout(function() {
            location.reload();
        }, 5000);
    </script>
    <?php endif; ?>
</body>
</html>
