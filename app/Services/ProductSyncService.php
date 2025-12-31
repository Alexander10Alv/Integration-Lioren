<?php

namespace App\Services;

use App\Models\ProductMapping;
use App\Models\SyncLog;
use App\Models\SyncQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductSyncService
{
    protected $userId;
    protected $shopifyStore;
    protected $shopifyToken;
    protected $liorenApiKey;

    public function __construct($userId, $shopifyStore, $shopifyToken, $liorenApiKey)
    {
        $this->userId = $userId;
        $this->shopifyStore = $shopifyStore;
        $this->shopifyToken = $shopifyToken;
        $this->liorenApiKey = $liorenApiKey;
    }

    /**
     * SINCRONIZACIÓN INICIAL BIDIRECCIONAL
     */
    public function initialBidirectionalSync()
    {
        Log::info("=== INICIANDO SINCRONIZACIÓN BIDIRECCIONAL ===", ['user_id' => $this->userId]);

        $results = [
            'shopify_to_lioren' => ['created' => 0, 'updated' => 0, 'errors' => 0],
            'lioren_to_shopify' => ['created' => 0, 'errors' => 0],
            'total_synced' => 0,
        ];

        try {
            // PASO 1: Shopify → Lioren (Shopify es la fuente de verdad)
            $shopifyResults = $this->syncShopifyToLioren();
            $results['shopify_to_lioren'] = $shopifyResults;

            // PASO 2: Lioren → Shopify (solo productos nuevos)
            $liorenResults = $this->syncLiorenToShopify();
            $results['lioren_to_shopify'] = $liorenResults;

            $results['total_synced'] = $shopifyResults['created'] + $shopifyResults['updated'] + $liorenResults['created'];

            Log::info("✅ Sincronización bidireccional completada", $results);

            return [
                'success' => true,
                'results' => $results,
            ];

        } catch (\Exception $e) {
            Log::error("❌ Error en sincronización bidireccional: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => $results,
            ];
        }
    }

    /**
     * PASO 1: Shopify → Lioren
     * Shopify es la fuente de verdad, actualiza o crea en Lioren
     */
    protected function syncShopifyToLioren()
    {
        Log::info("📦 PASO 1: Sincronizando Shopify → Lioren");

        $results = ['created' => 0, 'updated' => 0, 'errors' => 0];

        // Obtener todos los productos de Shopify
        $shopifyProducts = $this->getShopifyProducts();

        Log::info("Productos encontrados en Shopify: " . count($shopifyProducts));

        foreach ($shopifyProducts as $shopifyProduct) {
            try {
                // Procesar cada variante como un producto separado
                foreach ($shopifyProduct['variants'] as $variant) {
                    $sku = $variant['sku'] ?? 'SHOPIFY-' . $variant['id'];
                    
                    if (empty($sku) || $sku === 'SHOPIFY-' . $variant['id']) {
                        Log::warning("Producto sin SKU, generando uno: {$sku}");
                    }

                    // Buscar si existe en Lioren por SKU
                    $liorenProduct = $this->findLiorenProductBySku($sku);

                    if ($liorenProduct) {
                        // Existe en Lioren → ACTUALIZAR con datos de Shopify
                        $this->updateLiorenProduct($liorenProduct['id'], $shopifyProduct, $variant);
                        $results['updated']++;
                        
                        Log::info("✏️ Producto actualizado en Lioren: {$sku}");
                    } else {
                        // NO existe en Lioren → CREAR
                        $liorenProduct = $this->createLiorenProduct($shopifyProduct, $variant);
                        $results['created']++;
                        
                        Log::info("➕ Producto creado en Lioren: {$sku}");
                    }

                    // Guardar mapeo en BD
                    $this->saveProductMapping(
                        $shopifyProduct['id'],
                        $variant['id'],
                        $liorenProduct['id'] ?? null,
                        $sku,
                        $shopifyProduct['title'],
                        floatval($variant['price']),
                        intval($variant['inventory_quantity'] ?? 0)
                    );
                }

            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Error procesando producto Shopify: " . $e->getMessage());
                
                SyncLog::logError(
                    $this->userId,
                    'initial',
                    'shopify_to_lioren',
                    'product',
                    $shopifyProduct['id'],
                    $e->getMessage()
                );
            }
        }

        return $results;
    }

    /**
     * PASO 2: Lioren → Shopify
     * Solo crear productos que NO existen en Shopify
     */
    protected function syncLiorenToShopify()
    {
        Log::info("📦 PASO 2: Sincronizando Lioren → Shopify (solo nuevos)");

        $results = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        // Obtener todos los productos de Lioren
        $liorenProducts = $this->getLiorenProducts();

        Log::info("Productos encontrados en Lioren: " . count($liorenProducts));

        foreach ($liorenProducts as $liorenProduct) {
            try {
                $sku = $liorenProduct['codigo'];

                // Verificar si ya existe en el mapeo (ya se sincronizó en Paso 1)
                $mapping = ProductMapping::findBySku($sku, $this->userId);

                if ($mapping && $mapping->shopify_product_id) {
                    // Ya existe en Shopify, skip
                    $results['skipped']++;
                    continue;
                }

                // NO existe en Shopify → CREAR
                $shopifyProduct = $this->createShopifyProduct($liorenProduct);
                $results['created']++;

                Log::info("➕ Producto creado en Shopify: {$sku}");

                // Actualizar mapeo
                if ($mapping) {
                    $mapping->update([
                        'shopify_product_id' => $shopifyProduct['id'],
                        'shopify_variant_id' => $shopifyProduct['variants'][0]['id'],
                    ]);
                } else {
                    $this->saveProductMapping(
                        $shopifyProduct['id'],
                        $shopifyProduct['variants'][0]['id'],
                        $liorenProduct['id'],
                        $sku,
                        $liorenProduct['nombre'],
                        floatval($liorenProduct['precioventabruto']),
                        0 // Stock se sincroniza después
                    );
                }

            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Error procesando producto Lioren: " . $e->getMessage());
                
                SyncLog::logError(
                    $this->userId,
                    'initial',
                    'lioren_to_shopify',
                    'product',
                    $liorenProduct['id'],
                    $e->getMessage()
                );
            }
        }

        return $results;
    }

    /**
     * Obtener productos de Shopify con cursor pagination
     */
    protected function getShopifyProducts($limit = 250)
    {
        $allProducts = [];
        $url = "https://{$this->shopifyStore}/admin/api/2024-01/products.json?limit={$limit}";

        while ($url) {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->shopifyToken,
            ])->get($url);

            if (!$response->successful()) {
                throw new \Exception("Error obteniendo productos de Shopify: " . $response->body());
            }

            $products = $response->json()['products'] ?? [];
            $allProducts = array_merge($allProducts, $products);

            // Obtener siguiente página del header Link
            $url = $this->getNextPageUrl($response->header('Link'));

            // Evitar rate limiting
            if ($url) {
                sleep(1);
            }
        }

        return $allProducts;
    }

    /**
     * Extraer URL de siguiente página del header Link
     */
    protected function getNextPageUrl($linkHeader)
    {
        if (!$linkHeader) {
            return null;
        }

        // El header Link tiene formato: <url>; rel="next"
        if (preg_match('/<([^>]+)>;\s*rel="next"/', $linkHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Obtener productos de Lioren
     */
    protected function getLiorenProducts()
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->liorenApiKey}",
            'Accept' => 'application/json',
        ])->get('https://www.lioren.cl/api/productos');

        if (!$response->successful()) {
            throw new \Exception("Error obteniendo productos de Lioren: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Buscar producto en Lioren por SKU
     */
    protected function findLiorenProductBySku($sku)
    {
        $products = $this->getLiorenProducts();

        foreach ($products as $product) {
            if ($product['codigo'] === $sku) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Crear producto en Lioren
     */
    protected function createLiorenProduct($shopifyProduct, $variant)
    {
        $sku = $variant['sku'] ?? 'SHOPIFY-' . $variant['id'];
        $price = floatval($variant['price']);
        $priceNeto = round($price / 1.19, 2);

        $data = [
            'nombre' => substr($shopifyProduct['title'], 0, 80),
            'codigo' => substr($sku, 0, 128),
            'unidad' => 'Unidad',
            'descripcion' => substr(strip_tags($shopifyProduct['body_html'] ?? ''), 0, 1000),
            'fraccionable' => 0,
            'exento' => 0,
            'preciocompraneto' => $priceNeto,
            'precioventabruto' => $price,
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->liorenApiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://www.lioren.cl/api/productos', $data);

        if (!$response->successful()) {
            throw new \Exception("Error creando producto en Lioren: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Actualizar producto en Lioren
     */
    protected function updateLiorenProduct($liorenId, $shopifyProduct, $variant)
    {
        $price = floatval($variant['price']);
        $priceNeto = round($price / 1.19, 2);

        $data = [
            'nombre' => substr($shopifyProduct['title'], 0, 80),
            'codigo' => substr($variant['sku'] ?? 'SHOPIFY-' . $variant['id'], 0, 128),
            'unidad' => 'Unidad',
            'descripcion' => substr(strip_tags($shopifyProduct['body_html'] ?? ''), 0, 1000),
            'fraccionable' => 0,
            'exento' => 0,
            'preciocompraneto' => $priceNeto,
            'precioventabruto' => $price,
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->liorenApiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->put("https://www.lioren.cl/api/productos/{$liorenId}", $data);

        if (!$response->successful()) {
            throw new \Exception("Error actualizando producto en Lioren: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Crear producto en Shopify
     */
    protected function createShopifyProduct($liorenProduct)
    {
        $data = [
            'product' => [
                'title' => $liorenProduct['nombre'],
                'body_html' => $liorenProduct['descripcion'] ?? '',
                'vendor' => 'Lioren',
                'product_type' => 'General',
                'variants' => [
                    [
                        'sku' => $liorenProduct['codigo'],
                        'price' => $liorenProduct['precioventabruto'],
                        'inventory_management' => 'shopify',
                        'inventory_policy' => 'deny',
                    ]
                ],
            ]
        ];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shopifyToken,
            'Content-Type' => 'application/json',
        ])->post("https://{$this->shopifyStore}/admin/api/2024-01/products.json", $data);

        if (!$response->successful()) {
            throw new \Exception("Error creando producto en Shopify: " . $response->body());
        }

        return $response->json()['product'];
    }

    /**
     * Guardar mapeo de producto
     */
    protected function saveProductMapping($shopifyId, $variantId, $liorenId, $sku, $title, $price, $stock)
    {
        return ProductMapping::updateOrCreate(
            [
                'user_id' => $this->userId,
                'sku' => $sku,
            ],
            [
                'shopify_product_id' => $shopifyId,
                'shopify_variant_id' => $variantId,
                'lioren_product_id' => $liorenId,
                'product_title' => $title,
                'price' => $price,
                'stock' => $stock,
                'sync_status' => 'synced',
                'last_synced_at' => now(),
            ]
        );
    }
}
