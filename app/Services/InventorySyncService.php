<?php

namespace App\Services;

use App\Models\WarehouseMapping;
use App\Models\LocationBodegaMapping;
use App\Models\PendingLocationMapping;
use App\Models\ProductMapping;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventorySyncService
{
    protected $userId;
    protected $config;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->config = \App\Models\IntegracionConfig::where('user_id', $userId)->first();
    }

    /**
     * Sincronizar inventario de Shopify a Lioren
     */
    public function syncInventoryFromShopify($inventoryData)
    {
        Log::info("📦 Sincronizando inventario", [
            'inventory_item_id' => $inventoryData['inventory_item_id'],
            'location_id' => $inventoryData['location_id'] ?? null,
            'available' => $inventoryData['available'],
        ]);

        try {
            // Buscar producto en mapeo
            $mapping = ProductMapping::where('shopify_variant_id', $inventoryData['inventory_item_id'])
                ->where('user_id', $this->userId)
                ->first();

            if (!$mapping || !$mapping->lioren_product_id) {
                Log::warning("Producto no encontrado en mapeo");
                return ['success' => false, 'message' => 'Producto no encontrado'];
            }

            // Obtener bodega según location
            $bodegaId = $this->getBodegaForLocation($inventoryData['location_id'] ?? null);

            if (!$bodegaId) {
                Log::warning("No se pudo determinar bodega, usando fallback");
            }

            // Sincronizar stock
            $this->syncStock(
                $mapping->lioren_product_id,
                $bodegaId,
                $inventoryData['available']
            );

            // Actualizar mapeo local
            $mapping->update([
                'stock' => $inventoryData['available'],
                'last_synced_at' => now(),
            ]);

            SyncLog::logSuccess(
                $this->userId,
                'webhook',
                'shopify_to_lioren',
                'inventory',
                $inventoryData['inventory_item_id'],
                "Stock sincronizado: {$inventoryData['available']}"
            );

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error("Error sincronizando inventario: " . $e->getMessage());
            
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
     * Obtener bodega para una location de Shopify
     */
    protected function getBodegaForLocation($locationId)
    {
        $warehouseConfig = WarehouseMapping::getConfig($this->userId);

        // Si no hay location, usar bodega por defecto
        if (!$locationId) {
            return $warehouseConfig->default_bodega_id;
        }

        // MODO SIMPLE: Usar siempre la bodega por defecto
        if ($warehouseConfig->isSimpleMode()) {
            return $warehouseConfig->default_bodega_id;
        }

        // MODO AVANZADO: Buscar mapeo
        $bodegaId = LocationBodegaMapping::getBodegaForLocation($locationId, $this->userId);

        if ($bodegaId) {
            return $bodegaId;
        }

        // Location no mapeada → Registrar como pendiente
        $this->registerPendingLocation($locationId);

        // Usar bodega fallback
        return $warehouseConfig->default_bodega_id;
    }

    /**
     * Registrar location pendiente de mapear
     */
    protected function registerPendingLocation($locationId)
    {
        try {
            // Obtener nombre de la location desde Shopify
            $locationName = $this->getShopifyLocationName($locationId);

            // Buscar o crear registro pendiente
            $pending = PendingLocationMapping::firstOrCreate(
                [
                    'user_id' => $this->userId,
                    'shopify_location_id' => $locationId,
                ],
                [
                    'shopify_location_name' => $locationName,
                    'status' => 'pending',
                    'first_detected_at' => now(),
                    'affected_products_count' => 0,
                ]
            );

            // Incrementar contador
            $pending->incrementAffectedCount();

            // Si es la primera vez, marcar para notificación
            if ($pending->status === 'pending' && $pending->affected_products_count === 1) {
                Log::warning("🆕 Nueva location detectada sin mapeo: {$locationName} (ID: {$locationId})");
                // TODO: Enviar notificación (FASE futura)
            }

        } catch (\Exception $e) {
            Log::error("Error registrando location pendiente: " . $e->getMessage());
        }
    }

    /**
     * Obtener nombre de location desde Shopify
     */
    protected function getShopifyLocationName($locationId)
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->config->shopify_token,
            ])->get("https://{$this->config->shopify_tienda}/admin/api/2024-01/locations/{$locationId}.json");

            if ($response->successful()) {
                return $response->json()['location']['name'] ?? "Location {$locationId}";
            }
        } catch (\Exception $e) {
            Log::error("Error obteniendo nombre de location: " . $e->getMessage());
        }

        return "Location {$locationId}";
    }

    /**
     * Sincronizar stock en Lioren
     */
    protected function syncStock($productoId, $bodegaId, $newQuantity)
    {
        // Obtener stock actual
        $currentStock = $this->getCurrentStockInLioren($productoId, $bodegaId);

        // Calcular diferencia
        $difference = $newQuantity - $currentStock;

        if ($difference == 0) {
            Log::info("Stock ya está sincronizado: {$currentStock}");
            return true;
        }

        Log::info("Ajustando stock: {$currentStock} → {$newQuantity} (diferencia: {$difference})");

        // Ajustar stock
        if ($difference > 0) {
            // Agregar stock
            $this->addStockInLioren($productoId, $bodegaId, $difference);
        } else {
            // Quitar stock
            $this->removeStockInLioren($productoId, $bodegaId, abs($difference));
        }

        return true;
    }

    /**
     * Obtener stock actual en Lioren
     */
    protected function getCurrentStockInLioren($productoId, $bodegaId)
    {
        try {
            // Obtener todos los productos para encontrar el stock
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->config->lioren_api_key}",
                'Accept' => 'application/json',
            ])->get('https://www.lioren.cl/api/productos');

            if (!$response->successful()) {
                Log::warning("No se pudo obtener stock actual, asumiendo 0");
                return 0;
            }

            $productos = $response->json();

            foreach ($productos as $producto) {
                if ($producto['id'] == $productoId) {
                    // Lioren no devuelve stock por bodega en el listado
                    // Por ahora retornar 0 para forzar ajuste
                    return 0;
                }
            }

            return 0;

        } catch (\Exception $e) {
            Log::error("Error obteniendo stock actual: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Agregar stock en Lioren
     */
    protected function addStockInLioren($productoId, $bodegaId, $cantidad)
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
            throw new \Exception("Error agregando stock en Lioren: " . $response->body());
        }

        Log::info("✅ Stock agregado: +{$cantidad}");

        return $response->json();
    }

    /**
     * Quitar stock en Lioren
     */
    protected function removeStockInLioren($productoId, $bodegaId, $cantidad)
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
            throw new \Exception("Error quitando stock en Lioren: " . $response->body());
        }

        Log::info("✅ Stock quitado: -{$cantidad}");

        return $response->json();
    }

    /**
     * Obtener locations de Shopify
     */
    public function getShopifyLocations()
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->config->shopify_token,
        ])->get("https://{$this->config->shopify_tienda}/admin/api/2024-01/locations.json");

        if (!$response->successful()) {
            throw new \Exception("Error obteniendo locations de Shopify: " . $response->body());
        }

        return $response->json()['locations'] ?? [];
    }

    /**
     * Obtener bodegas de Lioren
     */
    public function getLiorenBodegas()
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->config->lioren_api_key}",
            'Accept' => 'application/json',
        ])->get('https://www.lioren.cl/api/bodegas');

        if (!$response->successful()) {
            throw new \Exception("Error obteniendo bodegas de Lioren: " . $response->body());
        }

        $data = $response->json();

        // La respuesta puede ser un array directo o tener una clave 'bodegas'
        return $data['bodegas'] ?? $data;
    }

    /**
     * Configurar modo simple (una bodega para todo)
     */
    public function configureSimpleMode($bodegaId, $bodegaName)
    {
        $config = WarehouseMapping::getConfig($this->userId);

        $config->update([
            'sync_mode' => 'simple',
            'default_bodega_id' => $bodegaId,
            'default_bodega_name' => $bodegaName,
        ]);

        Log::info("✅ Modo simple configurado", [
            'bodega_id' => $bodegaId,
            'bodega_name' => $bodegaName,
        ]);

        return $config;
    }

    /**
     * Configurar modo avanzado (mapeo manual)
     */
    public function configureAdvancedMode($defaultBodegaId, $defaultBodegaName)
    {
        $config = WarehouseMapping::getConfig($this->userId);

        $config->update([
            'sync_mode' => 'advanced',
            'default_bodega_id' => $defaultBodegaId,
            'default_bodega_name' => $defaultBodegaName,
        ]);

        Log::info("✅ Modo avanzado configurado", [
            'default_bodega_id' => $defaultBodegaId,
            'default_bodega_name' => $defaultBodegaName,
        ]);

        return $config;
    }

    /**
     * Crear mapeo de location a bodega
     */
    public function createLocationMapping($locationId, $locationName, $bodegaId, $bodegaName)
    {
        $mapping = LocationBodegaMapping::updateOrCreate(
            [
                'user_id' => $this->userId,
                'shopify_location_id' => $locationId,
            ],
            [
                'shopify_location_name' => $locationName,
                'lioren_bodega_id' => $bodegaId,
                'lioren_bodega_name' => $bodegaName,
                'is_active' => true,
            ]
        );

        // Marcar location pendiente como resuelta
        PendingLocationMapping::where('user_id', $this->userId)
            ->where('shopify_location_id', $locationId)
            ->update(['status' => 'resolved', 'resolved_at' => now()]);

        Log::info("✅ Mapeo creado", [
            'location' => $locationName,
            'bodega' => $bodegaName,
        ]);

        return $mapping;
    }

    /**
     * Eliminar mapeo de location
     */
    public function deleteLocationMapping($locationId)
    {
        LocationBodegaMapping::where('user_id', $this->userId)
            ->where('shopify_location_id', $locationId)
            ->delete();

        Log::info("🗑️ Mapeo eliminado", ['location_id' => $locationId]);
    }

    /**
     * Obtener configuración actual
     */
    public function getCurrentConfig()
    {
        $config = WarehouseMapping::getConfig($this->userId);
        $mappings = LocationBodegaMapping::getMappedLocations($this->userId);
        $pending = PendingLocationMapping::getPending($this->userId);

        return [
            'mode' => $config->sync_mode,
            'default_bodega' => [
                'id' => $config->default_bodega_id,
                'name' => $config->default_bodega_name,
            ],
            'mappings' => $mappings,
            'pending_locations' => $pending,
        ];
    }
}
