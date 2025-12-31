<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IntegracionConfig;
use App\Models\LocationBodegaMapping;
use App\Models\PendingLocationMapping;
use App\Services\InventorySyncService;
use Illuminate\Support\Facades\Log;

class DetectNewLocations extends Command
{
    protected $signature = 'sync:detect-locations';
    protected $description = 'Detectar nuevas locations de Shopify sin mapear';

    public function handle()
    {
        $this->info("🔍 Detectando nuevas locations de Shopify...");

        // Obtener todas las configuraciones activas
        $configs = IntegracionConfig::where('activo', true)->get();

        if ($configs->isEmpty()) {
            $this->info("No hay configuraciones activas");
            return 0;
        }

        $totalDetected = 0;

        foreach ($configs as $config) {
            $this->line("Procesando usuario: {$config->user_id}");

            try {
                $inventoryService = new InventorySyncService($config->user_id);

                // Obtener locations de Shopify
                $shopifyLocations = $inventoryService->getShopifyLocations();

                $this->info("  Locations encontradas en Shopify: " . count($shopifyLocations));

                // Obtener locations ya mapeadas
                $mappedLocations = LocationBodegaMapping::where('user_id', $config->user_id)
                    ->pluck('shopify_location_id')
                    ->toArray();

                // Detectar locations nuevas
                foreach ($shopifyLocations as $location) {
                    $locationId = (string)$location['id'];

                    if (!in_array($locationId, $mappedLocations)) {
                        // Location no mapeada
                        $pending = PendingLocationMapping::firstOrCreate(
                            [
                                'user_id' => $config->user_id,
                                'shopify_location_id' => $locationId,
                            ],
                            [
                                'shopify_location_name' => $location['name'],
                                'status' => 'pending',
                                'first_detected_at' => now(),
                                'affected_products_count' => 0,
                            ]
                        );

                        if ($pending->wasRecentlyCreated) {
                            $totalDetected++;
                            $this->warn("  🆕 Nueva location detectada: {$location['name']} (ID: {$locationId})");
                            
                            Log::warning("Nueva location sin mapear detectada", [
                                'user_id' => $config->user_id,
                                'location_id' => $locationId,
                                'location_name' => $location['name'],
                            ]);
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                Log::error("Error detectando locations: " . $e->getMessage());
            }
        }

        $this->newLine();
        
        if ($totalDetected > 0) {
            $this->warn("⚠️  {$totalDetected} nueva(s) location(s) detectada(s) sin mapear");
        } else {
            $this->info("✅ Todas las locations están mapeadas");
        }

        return 0;
    }
}
