<?php

/**
 * Script para reconectar webhooks faltantes
 * Uso: php reconectar_webhooks.php [user_id]
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\IntegracionConfig;
use App\Models\ClienteWebhook;
use App\Models\Solicitud;
use Illuminate\Support\Facades\Http;

$userId = $argv[1] ?? null;

if (!$userId) {
    echo "❌ Uso: php reconectar_webhooks.php [user_id]\n";
    exit(1);
}

echo "🔄 RECONECTANDO WEBHOOKS\n";
echo "========================\n\n";

// Buscar integración del usuario
$config = IntegracionConfig::where('user_id', $userId)
    ->where('activo', true)
    ->first();

if (!$config) {
    echo "❌ No se encontró integración activa para user_id: {$userId}\n";
    exit(1);
}

$solicitud = Solicitud::find($config->solicitud_id);

if (!$solicitud) {
    echo "❌ No se encontró solicitud asociada\n";
    exit(1);
}

echo "✅ Integración encontrada\n";
echo "   Usuario ID: {$config->user_id}\n";
echo "   Tienda: {$config->shopify_tienda}\n";
echo "   Solicitud ID: {$config->solicitud_id}\n\n";

// Definir webhooks necesarios
$webhookUrl = url('/integracion/webhook-receiver');

$webhooks = [
    ['topic' => 'orders/create', 'nombre' => 'Nuevos Pedidos'],
    ['topic' => 'products/create', 'nombre' => 'Productos Creados'],
    ['topic' => 'products/update', 'nombre' => 'Productos Actualizados'],
    ['topic' => 'inventory_levels/update', 'nombre' => 'Inventario Actualizado']
];

// Agregar webhooks de Notas de Crédito si está habilitado
if ($config->notas_credito_enabled) {
    $webhooks[] = ['topic' => 'orders/cancelled', 'nombre' => 'Pedidos Cancelados'];
    $webhooks[] = ['topic' => 'refunds/create', 'nombre' => 'Reembolsos Creados'];
}

echo "📋 Webhooks a crear: " . count($webhooks) . "\n\n";

$creados = 0;
$errores = 0;

foreach ($webhooks as $webhook) {
    echo "🔗 Creando webhook: {$webhook['nombre']} ({$webhook['topic']})...\n";
    
    try {
        // Verificar si ya existe en BD
        $existeDB = ClienteWebhook::where('user_id', $userId)
            ->where('topic', $webhook['topic'])
            ->first();
        
        if ($existeDB) {
            echo "   ⚠️  Ya existe en BD (ID: {$existeDB->webhook_shopify_id})\n";
            continue;
        }
        
        // Crear webhook en Shopify
        $urlCompleta = $webhookUrl . '?evento=' . str_replace('/', '_', $webhook['topic']) . '&user_id=' . $userId;
        
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $config->shopify_token,
        ])->timeout(15)->post("https://{$config->shopify_tienda}/admin/api/2024-01/webhooks.json", [
            'webhook' => [
                'topic' => $webhook['topic'],
                'address' => $urlCompleta,
                'format' => 'json'
            ]
        ]);
        
        if ($response->successful()) {
            $result = $response->json();
            
            // Guardar en BD
            ClienteWebhook::create([
                'user_id' => $userId,
                'solicitud_id' => $config->solicitud_id,
                'webhook_shopify_id' => $result['webhook']['id'],
                'topic' => $webhook['topic'],
                'address' => $urlCompleta,
            ]);
            
            echo "   ✅ Creado exitosamente (ID: {$result['webhook']['id']})\n";
            $creados++;
        } else {
            $errorMsg = $response->json()['errors'] ?? $response->body();
            echo "   ❌ Error: HTTP {$response->status()} - {$errorMsg}\n";
            $errores++;
        }
    } catch (\Exception $e) {
        echo "   ❌ Excepción: {$e->getMessage()}\n";
        $errores++;
    }
    
    echo "\n";
}

echo str_repeat("=", 50) . "\n";
echo "📊 RESUMEN:\n";
echo "   ✅ Creados: {$creados}\n";
echo "   ❌ Errores: {$errores}\n";
echo "   📋 Total: " . count($webhooks) . "\n";

if ($creados > 0) {
    echo "\n✅ Webhooks reconectados exitosamente\n";
} else {
    echo "\n⚠️  No se crearon nuevos webhooks\n";
}
