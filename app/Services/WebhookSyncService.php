<?php

namespace App\Services;

use App\Models\ProductMapping;
use App\Models\SyncLog;
use App\Models\SyncQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookSyncService
{
    protected $userId;
    protected $config;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->config = \App\Models\IntegracionConfig::where('user_id', $userId)->first();
    }

    /**
     * Procesar webhook de producto creado
     */
    public function handleProductCreate($productData)
    {
        Log::info("🆕 Procesando producto creado", ['product_id' => $productData['id']]);

        try {
            foreach ($productData['variants'] as $variant) {
                $sku = $variant['sku'] ?? 'SHOPIFY-' . $variant['id'];

                // Verificar si ya existe en el mapeo
                $mapping = ProductMapping::findBySku($sku, $this->userId);

                if ($mapping) {
                    Log::info("Producto ya existe en mapeo, actualizando: {$sku}");
                    $this->handleProductUpdate($productData);
                    continue;
                }

                // Crear en Lioren con reintentos
                $this->createProductInLiorenWithRetry($productData, $variant);
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error("Error procesando producto creado: " . $e->getMessage());
            
            SyncLog::logError(
                $this->userId,
                'webhook',
                'shopify_to_lioren',
                'product',
                $productData['id'],
                $e->getMessage()
            );

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Procesar webhook de producto actualizado
     */
    public function handleProductUpdate($productData)
    {
        Log::info("✏️ Procesando producto actualizado", ['product_id' => $productData['id']]);

        try {
            foreach ($productData['variants'] as $variant) {
                $sku = $variant['sku'] ?? 'SHOPIFY-' . $variant['id'];

                // Buscar mapeo
                $mapping = ProductMapping::findBySku($sku, $this->userId);

                if (!$mapping || !$mapping->lioren_product_id) {
                    Log::warning("Producto no encontrado en mapeo, creando: {$sku}");
                    $this->createProductInLiorenWithRetry($productData, $variant);
                    continue;
                }

                // Actualizar en Lioren con reintentos
                $this->updateProductInLiorenWithRetry($mapping->lioren_product_id, $productData, $variant);

                // Actualizar mapeo local
                $mapping->update([
                    'product_title' => $productData['title'],
                    'price' => floatval($variant['price']),
                    'last_synced_at' => now(),
                ]);
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error("Error procesando producto actualizado: " . $e->getMessage());
            
            SyncLog::logError(
                $this->userId,
                'webhook',
                'shopify_to_lioren',
                'product',
                $productData['id'],
                $e->getMessage()
            );

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Procesar webhook de producto eliminado
     */
    public function handleProductDelete($productData)
    {
        Log::info("🗑️ Procesando producto eliminado", ['product_id' => $productData['id']]);

        try {
            $mapping = ProductMapping::findByShopifyId($productData['id'], $this->userId);

            if (!$mapping || !$mapping->lioren_product_id) {
                Log::warning("Producto no encontrado en mapeo");
                return ['success' => true, 'message' => 'Producto no encontrado'];
            }

            // Eliminar en Lioren con reintentos
            $this->deleteProductInLiorenWithRetry($mapping->lioren_product_id);

            // Eliminar mapeo
            $mapping->delete();

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error("Error procesando producto eliminado: " . $e->getMessage());
            
            SyncLog::logError(
                $this->userId,
                'webhook',
                'shopify_to_lioren',
                'product',
                $productData['id'],
                $e->getMessage()
            );

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Procesar webhook de inventario actualizado
     */
    public function handleInventoryUpdate($inventoryData)
    {
        Log::info("📦 Procesando inventario actualizado", [
            'inventory_item_id' => $inventoryData['inventory_item_id'],
            'available' => $inventoryData['available'],
        ]);

        try {
            // Usar el servicio de inventario
            $inventoryService = new \App\Services\InventorySyncService($this->userId);
            
            return $inventoryService->syncInventoryFromShopify($inventoryData);

        } catch (\Exception $e) {
            Log::error("Error procesando inventario: " . $e->getMessage());
            
            SyncLog::logError(
                $this->userId,
                'webhook',
                'shopify_to_lioren',
                'inventory',
                $inventoryData['inventory_item_id'],
                $e->getMessage()
            );

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Crear producto en Lioren con sistema de reintentos
     */
    protected function createProductInLiorenWithRetry($productData, $variant)
    {
        $sku = $variant['sku'] ?? 'SHOPIFY-' . $variant['id'];

        // Agregar a la cola de sincronización
        SyncQueue::enqueue(
            $this->userId,
            'create',
            'lioren',
            [
                'product' => $productData,
                'variant' => $variant,
            ],
            $sku
        );

        // Intentar ejecutar inmediatamente
        return $this->executeCreateProduct($productData, $variant);
    }

    /**
     * Ejecutar creación de producto
     */
    protected function executeCreateProduct($productData, $variant)
    {
        $sku = $variant['sku'] ?? 'SHOPIFY-' . $variant['id'];
        $price = floatval($variant['price']);
        $priceNeto = round($price / 1.19, 2);

        $data = [
            'nombre' => substr($productData['title'], 0, 80),
            'codigo' => substr($sku, 0, 128),
            'unidad' => 'Unidad',
            'descripcion' => substr(strip_tags($productData['body_html'] ?? ''), 0, 1000),
            'fraccionable' => 0,
            'exento' => 0,
            'preciocompraneto' => $priceNeto,
            'precioventabruto' => $price,
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->config->lioren_api_key}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(30)->post('https://www.lioren.cl/api/productos', $data);

        if (!$response->successful()) {
            throw new \Exception("Error creando producto en Lioren: " . $response->body());
        }

        $liorenProduct = $response->json();

        // Guardar mapeo
        ProductMapping::updateOrCreate(
            ['user_id' => $this->userId, 'sku' => $sku],
            [
                'shopify_product_id' => $productData['id'],
                'shopify_variant_id' => $variant['id'],
                'lioren_product_id' => $liorenProduct['id'],
                'product_title' => $productData['title'],
                'price' => $price,
                'stock' => intval($variant['inventory_quantity'] ?? 0),
                'sync_status' => 'synced',
                'last_synced_at' => now(),
            ]
        );

        SyncLog::logSuccess(
            $this->userId,
            'webhook',
            'shopify_to_lioren',
            'product',
            $productData['id'],
            "Producto creado: {$sku}"
        );

        return $liorenProduct;
    }

    /**
     * Actualizar producto en Lioren con reintentos
     */
    protected function updateProductInLiorenWithRetry($liorenId, $productData, $variant)
    {
        $sku = $variant['sku'] ?? 'SHOPIFY-' . $variant['id'];

        // Agregar a la cola
        SyncQueue::enqueue(
            $this->userId,
            'update',
            'lioren',
            [
                'lioren_id' => $liorenId,
                'product' => $productData,
                'variant' => $variant,
            ],
            $sku
        );

        // Intentar ejecutar inmediatamente
        return $this->executeUpdateProduct($liorenId, $productData, $variant);
    }

    /**
     * Ejecutar actualización de producto
     */
    protected function executeUpdateProduct($liorenId, $productData, $variant)
    {
        $price = floatval($variant['price']);
        $priceNeto = round($price / 1.19, 2);

        $data = [
            'nombre' => substr($productData['title'], 0, 80),
            'codigo' => substr($variant['sku'] ?? 'SHOPIFY-' . $variant['id'], 0, 128),
            'unidad' => 'Unidad',
            'descripcion' => substr(strip_tags($productData['body_html'] ?? ''), 0, 1000),
            'fraccionable' => 0,
            'exento' => 0,
            'preciocompraneto' => $priceNeto,
            'precioventabruto' => $price,
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->config->lioren_api_key}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(30)->put("https://www.lioren.cl/api/productos/{$liorenId}", $data);

        if (!$response->successful()) {
            throw new \Exception("Error actualizando producto en Lioren: " . $response->body());
        }

        SyncLog::logSuccess(
            $this->userId,
            'webhook',
            'shopify_to_lioren',
            'product',
            $productData['id'],
            "Producto actualizado: {$data['codigo']}"
        );

        return $response->json();
    }

    /**
     * Eliminar producto en Lioren con reintentos
     */
    protected function deleteProductInLiorenWithRetry($liorenId)
    {
        // Agregar a la cola
        SyncQueue::enqueue(
            $this->userId,
            'delete',
            'lioren',
            ['lioren_id' => $liorenId]
        );

        // Intentar ejecutar inmediatamente
        return $this->executeDeleteProduct($liorenId);
    }

    /**
     * Ejecutar eliminación de producto
     */
    protected function executeDeleteProduct($liorenId)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->config->lioren_api_key}",
            'Accept' => 'application/json',
        ])->timeout(30)->delete("https://www.lioren.cl/api/productos/{$liorenId}");

        if (!$response->successful()) {
            throw new \Exception("Error eliminando producto en Lioren: " . $response->body());
        }

        SyncLog::logSuccess(
            $this->userId,
            'webhook',
            'shopify_to_lioren',
            'product',
            $liorenId,
            "Producto eliminado"
        );

        return true;
    }

    /**
     * Actualizar stock en Lioren con reintentos
     */
    protected function updateStockInLiorenWithRetry($liorenId, $quantity, $locationId = null)
    {
        // Agregar a la cola
        SyncQueue::enqueue(
            $this->userId,
            'sync_inventory',
            'lioren',
            [
                'lioren_id' => $liorenId,
                'quantity' => $quantity,
                'location_id' => $locationId,
            ]
        );

        // Intentar ejecutar inmediatamente
        return $this->executeUpdateStock($liorenId, $quantity, $locationId);
    }

    /**
     * Ejecutar actualización de stock
     */
    protected function executeUpdateStock($liorenId, $quantity, $locationId = null)
    {
        // Obtener bodega según location (implementar en FASE 4)
        $bodegaId = $this->getBodegaForLocation($locationId);

        // Por ahora, usar bodega por defecto
        if (!$bodegaId) {
            $warehouseConfig = \App\Models\WarehouseMapping::getConfig($this->userId);
            $bodegaId = $warehouseConfig->default_bodega_id ?? 1; // Fallback a bodega 1
        }

        // Obtener stock actual
        $currentStock = $this->getCurrentStock($liorenId, $bodegaId);

        // Calcular diferencia
        $difference = $quantity - $currentStock;

        if ($difference == 0) {
            Log::info("Stock ya está sincronizado");
            return true;
        }

        // Ajustar stock
        if ($difference > 0) {
            // Agregar stock
            $this->addStock($liorenId, $bodegaId, $difference);
        } else {
            // Quitar stock
            $this->removeStock($liorenId, $bodegaId, abs($difference));
        }

        SyncLog::logSuccess(
            $this->userId,
            'webhook',
            'shopify_to_lioren',
            'inventory',
            $liorenId,
            "Stock actualizado: {$currentStock} → {$quantity}"
        );

        return true;
    }

    /**
     * Obtener bodega para una location (FASE 4)
     */
    protected function getBodegaForLocation($locationId)
    {
        if (!$locationId) {
            return null;
        }

        return \App\Models\LocationBodegaMapping::getBodegaForLocation($locationId, $this->userId);
    }

    /**
     * Obtener stock actual en Lioren
     */
    protected function getCurrentStock($productoId, $bodegaId)
    {
        // TODO: Implementar consulta a API de Lioren para obtener stock actual
        // Por ahora retornar 0
        return 0;
    }

    /**
     * Agregar stock en Lioren
     */
    protected function addStock($productoId, $bodegaId, $cantidad)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->config->lioren_api_key}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://www.lioren.cl/api/stocks', [
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'cantidad' => $cantidad,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Error agregando stock: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Quitar stock en Lioren
     */
    protected function removeStock($productoId, $bodegaId, $cantidad)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->config->lioren_api_key}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->delete('https://www.lioren.cl/api/stocks', [
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'cantidad' => $cantidad,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Error quitando stock: " . $response->body());
        }

        return $response->json();
    }
}
