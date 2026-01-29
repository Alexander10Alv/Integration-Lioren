<?php

/**
 * Script para verificar webhooks de Shopify
 * Uso: php check_webhooks.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\IntegracionConfig;
use App\Models\ClienteWebhook;
use Illuminate\Support\Facades\Http;

echo "🔍 VERIFICANDO WEBHOOKS DE SHOPIFY\n";
echo "==================================\n\n";

// Obtener todas las integraciones activas
$integraciones = IntegracionConfig::where('activo', true)->get();

if ($integraciones->isEmpty()) {
    echo "❌ No hay integraciones activas\n";
    exit;
}

foreach ($integraciones as $config) {
    echo "📋 Integración ID: {$config->id}\n";
    echo "   Usuario ID: {$config->user_id}\n";
    echo "   Tienda: {$config->shopify_tienda}\n";
    echo "   Solicitud ID: {$config->solicitud_id}\n\n";
    
    // Consultar webhooks en Shopify
    try {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $config->shopify_token,
        ])->timeout(10)->get("https://{$config->shopify_tienda}/admin/api/2024-01/webhooks.json");
        
        if ($response->successful()) {
            $webhooks = $response->json()['webhooks'] ?? [];
            
            echo "   ✅ Webhooks en Shopify: " . count($webhooks) . "\n";
            
            if (empty($webhooks)) {
                echo "   ⚠️  NO HAY WEBHOOKS REGISTRADOS EN SHOPIFY\n";
            } else {
                foreach ($webhooks as $webhook) {
                    echo "      • {$webhook['topic']} (ID: {$webhook['id']})\n";
                    echo "        URL: {$webhook['address']}\n";
                }
            }
        } else {
            echo "   ❌ Error consultando Shopify: HTTP {$response->status()}\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Error: {$e->getMessage()}\n";
    }
    
    // Consultar webhooks en BD
    $webhooksDB = ClienteWebhook::where('user_id', $config->user_id)->get();
    echo "\n   📊 Webhooks en BD: " . $webhooksDB->count() . "\n";
    
    if ($webhooksDB->isEmpty()) {
        echo "   ⚠️  NO HAY WEBHOOKS EN LA BASE DE DATOS\n";
    } else {
        foreach ($webhooksDB as $webhook) {
            echo "      • {$webhook->topic} (Shopify ID: {$webhook->webhook_shopify_id})\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "✅ Verificación completada\n";
