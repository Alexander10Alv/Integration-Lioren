<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncQueue;
use App\Services\WebhookSyncService;
use Illuminate\Support\Facades\Log;

class ProcessSyncQueue extends Command
{
    protected $signature = 'sync:process-queue {--limit=10 : Number of jobs to process}';
    protected $description = 'Procesar cola de sincronización con reintentos automáticos';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("🔄 Procesando cola de sincronización (límite: {$limit})...");

        $jobs = SyncQueue::getPending($limit);

        if ($jobs->isEmpty()) {
            $this->info("✅ No hay trabajos pendientes");
            return 0;
        }

        $this->info("📋 Encontrados {$jobs->count()} trabajos pendientes");

        $processed = 0;
        $succeeded = 0;
        $failed = 0;
        $retried = 0;

        foreach ($jobs as $job) {
            $processed++;
            
            $this->line("Procesando trabajo #{$job->id} - {$job->operation} en {$job->platform} (intento {$job->attempts}/{$job->max_attempts})");

            try {
                $job->markAsProcessing();

                // Ejecutar según el tipo de operación
                $this->executeJob($job);

                $job->markAsCompleted();
                $succeeded++;
                
                $this->info("  ✅ Completado");

            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                
                $willRetry = $job->markAsFailed($e->getMessage());
                
                if ($willRetry) {
                    $retried++;
                    $this->warn("  🔄 Se reintentará en " . $job->scheduled_at->diffForHumans());
                } else {
                    $failed++;
                    $this->error("  ⛔ Máximo de reintentos alcanzado");
                }
            }
        }

        $this->newLine();
        $this->info("📊 Resumen:");
        $this->table(
            ['Estado', 'Cantidad'],
            [
                ['Procesados', $processed],
                ['Exitosos', $succeeded],
                ['Reintentarán', $retried],
                ['Fallidos', $failed],
            ]
        );

        return 0;
    }

    /**
     * Ejecutar trabajo según tipo
     */
    protected function executeJob($job)
    {
        $payload = $job->payload;
        $webhookSync = new WebhookSyncService($job->user_id);

        switch ($job->operation) {
            case 'create':
                if ($job->platform === 'lioren') {
                    $webhookSync->handleProductCreate($payload['product']);
                }
                break;

            case 'update':
                if ($job->platform === 'lioren') {
                    $webhookSync->handleProductUpdate($payload['product']);
                }
                break;

            case 'delete':
                if ($job->platform === 'lioren') {
                    $liorenId = $payload['lioren_id'];
                    $this->executeDelete($job->user_id, $liorenId);
                }
                break;

            case 'sync_inventory':
                if ($job->platform === 'lioren') {
                    $this->executeSyncInventory($job->user_id, $payload);
                }
                break;

            default:
                throw new \Exception("Operación desconocida: {$job->operation}");
        }
    }

    /**
     * Ejecutar eliminación
     */
    protected function executeDelete($userId, $liorenId)
    {
        $config = \App\Models\IntegracionConfig::where('user_id', $userId)->first();

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "Bearer {$config->lioren_api_key}",
            'Accept' => 'application/json',
        ])->delete("https://www.lioren.cl/api/productos/{$liorenId}");

        if (!$response->successful()) {
            throw new \Exception("Error eliminando producto: " . $response->body());
        }
    }

    /**
     * Ejecutar sincronización de inventario
     */
    protected function executeSyncInventory($userId, $payload)
    {
        $webhookSync = new WebhookSyncService($userId);
        
        $inventoryData = [
            'inventory_item_id' => $payload['lioren_id'],
            'available' => $payload['quantity'],
            'location_id' => $payload['location_id'] ?? null,
        ];

        $webhookSync->handleInventoryUpdate($inventoryData);
    }
}
