<?php

namespace App\Jobs;

use App\Models\Solicitud;
use App\Models\IntegracionConfig;
use App\Services\ProductSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SincronizarIntegracionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 1;

    protected $solicitudId;

    public function __construct($solicitudId)
    {
        $this->solicitudId = $solicitudId;
    }

    public function handle()
    {
        $solicitud = Solicitud::find($this->solicitudId);
        
        if (!$solicitud) {
            Log::error("Solicitud no encontrada: {$this->solicitudId}");
            return;
        }

        try {
            Log::info("🔄 Iniciando sincronización en segundo plano", [
                'solicitud_id' => $solicitud->id
            ]);

            // Sincronizar productos
            $syncService = new ProductSyncService(
                $solicitud->cliente_id,
                $solicitud->tienda_shopify,
                $solicitud->access_token,
                $solicitud->api_key
            );

            $syncResults = $syncService->initialBidirectionalSync();
            $productosSincronizados = $syncResults['results']['total_synced'] ?? 0;

            // Actualizar última sincronización
            $config = IntegracionConfig::where('solicitud_id', $solicitud->id)->first();
            if ($config) {
                $config->update(['ultima_sincronizacion' => now()]);
            }

            Log::info("✅ Sincronización completada", [
                'solicitud_id' => $solicitud->id,
                'productos' => $productosSincronizados
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error en sincronización: " . $e->getMessage(), [
                'solicitud_id' => $solicitud->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
