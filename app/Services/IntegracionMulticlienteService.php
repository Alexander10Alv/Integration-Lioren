<?php

namespace App\Services;

use App\Models\Solicitud;
use App\Models\IntegracionConfig;
use App\Models\ClienteWebhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class IntegracionMulticlienteService
{
    /**
     * Conectar un cliente completo: validar, crear webhooks, sincronizar productos
     */
    public function conectarCliente(Solicitud $solicitud)
    {
        DB::beginTransaction();
        
        try {
            Log::info("=== INICIANDO CONEXIÓN DE CLIENTE ===", [
                'solicitud_id' => $solicitud->id,
                'cliente_id' => $solicitud->cliente_id,
                'plan_id' => $solicitud->plan_id,
            ]);

            // 1. Validar credenciales de Shopify
            $shopifyValid = $this->validarShopify(
                $solicitud->tienda_shopify,
                $solicitud->access_token
            );

            if (!$shopifyValid['success']) {
                return [
                    'success' => false,
                    'message' => 'Credenciales de Shopify inválidas: ' . $shopifyValid['message'],
                ];
            }

            // 2. Validar credenciales de Lioren
            $liorenValid = $this->validarLioren($solicitud->api_key);

            if (!$liorenValid['success']) {
                return [
                    'success' => false,
                    'message' => 'Credenciales de Lioren inválidas: ' . $liorenValid['message'],
                ];
            }

            // 3. Crear configuración de integración con permisos del plan
            $plan = $solicitud->plan;
            $integracionConfig = IntegracionConfig::create([
                'user_id' => $solicitud->cliente_id,
                'solicitud_id' => $solicitud->id,
                'shopify_tienda' => $solicitud->tienda_shopify,
                'shopify_token' => $solicitud->access_token,
                'shopify_secret' => $solicitud->api_secret,
                'lioren_api_key' => $solicitud->api_key,
                'facturacion_enabled' => $plan->facturacion_enabled,
                'shopify_visibility_enabled' => $plan->shopify_visibility_enabled,
                'notas_credito_enabled' => $plan->notas_credito_enabled,
                'order_limit_enabled' => $plan->order_limit_enabled,
                'monthly_order_limit' => $plan->monthly_order_limit,
                'activo' => true,
                'ultima_sincronizacion' => now(),
            ]);

            Log::info("✅ Configuración de integración creada", ['config_id' => $integracionConfig->id]);

            // 4. Crear webhooks en Shopify
            $webhooksResult = $this->crearWebhooks($solicitud, $integracionConfig);

            Log::info("📊 Resultado de webhooks", [
                'exitosos' => $webhooksResult['exitosos'],
                'fallidos' => $webhooksResult['fallidos'],
                'total' => $webhooksResult['total']
            ]);

            // Verificar si hubo errores críticos en webhooks
            if ($webhooksResult['fallidos'] > 0 && $webhooksResult['exitosos'] === 0) {
                // Todos los webhooks fallaron - error crítico
                DB::rollBack();
                
                $erroresDetalle = array_map(function($error) {
                    return "{$error['nombre']}: {$error['error']}";
                }, $webhooksResult['errores']);

                return [
                    'success' => false,
                    'message' => 'Error crítico: No se pudo crear ningún webhook',
                    'webhooks' => $webhooksResult,
                    'errores_detalle' => $erroresDetalle
                ];
            }

            // 5. Actualizar solicitud
            $solicitud->update([
                'integracion_conectada' => true,
                'fecha_conexion' => now(),
                'estado' => 'activa',
            ]);

            DB::commit();

            // 6. Lanzar sincronización en segundo plano
            \App\Jobs\SincronizarIntegracionJob::dispatch($solicitud->id);

            Log::info("✅ Job de sincronización lanzado");

            // Determinar mensaje según resultado de webhooks
            $mensaje = "✅ Integración conectada exitosamente";
            if ($webhooksResult['fallidos'] > 0) {
                $mensaje .= " (⚠️ {$webhooksResult['fallidos']} webhook(s) fallaron)";
            }

            return [
                'success' => true,
                'message' => $mensaje,
                'webhooks' => $webhooksResult,
                'data' => [
                    'config_id' => $integracionConfig->id,
                    'webhooks_exitosos' => $webhooksResult['exitosos'],
                    'webhooks_fallidos' => $webhooksResult['fallidos'],
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error conectando cliente: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error al conectar: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validar credenciales de Shopify
     */
    private function validarShopify($tienda, $token)
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->timeout(10)->get("https://{$tienda}/admin/api/2024-01/shop.json");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con Shopify',
                    'data' => $response->json()['shop'] ?? null
                ];
            }

            return [
                'success' => false,
                'message' => "Credenciales inválidas (HTTP {$response->status()})",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validar credenciales de Lioren
     */
    private function validarLioren($apiKey)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])->timeout(10)->get('https://www.lioren.cl/api/productos');

            $statusCode = $response->status();

            // Validar respuestas específicas
            if ($statusCode === 200 || $statusCode === 201) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con Lioren'
                ];
            }

            if ($statusCode === 401) {
                return [
                    'success' => false,
                    'message' => 'API Key inválido o expirado. Verifica tus credenciales de Lioren.'
                ];
            }

            if ($statusCode === 403) {
                return [
                    'success' => false,
                    'message' => 'API Key sin permisos suficientes. Contacta con Lioren para obtener los permisos necesarios.'
                ];
            }

            if ($statusCode === 302) {
                return [
                    'success' => false,
                    'message' => 'API Key requiere autenticación. Asegúrate de usar un Bearer Token válido.'
                ];
            }

            return [
                'success' => false,
                'message' => "Error de conexión con Lioren (HTTP {$statusCode}). Verifica tus credenciales."
            ];
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            
            // Detectar errores comunes
            if (str_contains($errorMsg, 'Could not resolve host')) {
                return [
                    'success' => false,
                    'message' => 'No se puede conectar con Lioren. Verifica tu conexión a internet.'
                ];
            }

            if (str_contains($errorMsg, 'timeout')) {
                return [
                    'success' => false,
                    'message' => 'Timeout al conectar con Lioren. El servidor no responde.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $errorMsg
            ];
        }
    }

    /**
     * Crear webhooks en Shopify para el cliente
     */
    private function crearWebhooks(Solicitud $solicitud, IntegracionConfig $config)
    {
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

        $creados = [];
        $errores = [];

        foreach ($webhooks as $webhook) {
            try {
                // Agregar user_id al webhook URL para identificar al cliente
                $urlCompleta = $webhookUrl . '?evento=' . str_replace('/', '_', $webhook['topic']) . '&user_id=' . $solicitud->cliente_id;
                
                Log::info("🔗 Intentando crear webhook: {$webhook['topic']}", [
                    'url' => $urlCompleta,
                    'tienda' => $solicitud->tienda_shopify
                ]);

                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $solicitud->access_token,
                ])->timeout(15)->post("https://{$solicitud->tienda_shopify}/admin/api/2024-01/webhooks.json", [
                    'webhook' => [
                        'topic' => $webhook['topic'],
                        'address' => $urlCompleta,
                        'format' => 'json'
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    
                    // Guardar webhook en BD
                    ClienteWebhook::create([
                        'user_id' => $solicitud->cliente_id,
                        'solicitud_id' => $solicitud->id,
                        'webhook_shopify_id' => $result['webhook']['id'],
                        'topic' => $webhook['topic'],
                        'address' => $urlCompleta,
                    ]);

                    $creados[] = [
                        'topic' => $webhook['topic'],
                        'nombre' => $webhook['nombre'],
                        'id' => $result['webhook']['id'],
                        'success' => true
                    ];
                    
                    Log::info("✅ Webhook creado exitosamente: {$webhook['topic']}", [
                        'webhook_id' => $result['webhook']['id'],
                        'user_id' => $solicitud->cliente_id
                    ]);
                } else {
                    $errorData = $response->json();
                    $errorMsg = "HTTP {$response->status()}: ";
                    
                    // Manejar errores que pueden ser string o array
                    if (isset($errorData['errors'])) {
                        if (is_array($errorData['errors'])) {
                            $errorMsg .= json_encode($errorData['errors']);
                        } else {
                            $errorMsg .= $errorData['errors'];
                        }
                    } else {
                        $errorMsg .= $response->body();
                    }
                    
                    $errores[] = [
                        'topic' => $webhook['topic'],
                        'nombre' => $webhook['nombre'],
                        'error' => $errorMsg,
                        'success' => false
                    ];
                    
                    Log::error("❌ Error creando webhook {$webhook['topic']}", [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                $errores[] = [
                    'topic' => $webhook['topic'],
                    'nombre' => $webhook['nombre'],
                    'error' => $e->getMessage(),
                    'success' => false
                ];
                
                Log::error("❌ Excepción creando webhook {$webhook['topic']}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'creados' => $creados,
            'errores' => $errores,
            'total' => count($webhooks),
            'exitosos' => count($creados),
            'fallidos' => count($errores)
        ];
    }

    /**
     * Desconectar un cliente (eliminar webhooks, desactivar config)
     */
    public function desconectarCliente($userId)
    {
        try {
            $config = IntegracionConfig::where('user_id', $userId)
                ->where('activo', true)
                ->first();

            if (!$config) {
                return ['success' => false, 'message' => 'No hay configuración activa'];
            }

            // Eliminar webhooks de Shopify
            $webhooks = ClienteWebhook::where('user_id', $userId)->get();
            
            foreach ($webhooks as $webhook) {
                try {
                    Http::withHeaders([
                        'X-Shopify-Access-Token' => $config->shopify_token,
                    ])->delete("https://{$config->shopify_tienda}/admin/api/2024-01/webhooks/{$webhook->webhook_shopify_id}.json");
                    
                    $webhook->delete();
                } catch (\Exception $e) {
                    Log::error("Error eliminando webhook: " . $e->getMessage());
                }
            }

            // Desactivar configuración
            $config->update(['activo' => false]);

            return ['success' => true, 'message' => 'Cliente desconectado exitosamente'];

        } catch (\Exception $e) {
            Log::error("Error desconectando cliente: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
