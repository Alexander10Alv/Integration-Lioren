<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Suscripcion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificarVencimientosSuscripciones extends Command
{
    protected $signature = 'suscripciones:verificar-vencimientos';
    protected $description = 'Verifica y marca como vencidas las suscripciones que no han sido renovadas';

    public function handle()
    {
        $hoy = now()->format('Y-m-d');
        
        // Buscar suscripciones activas cuyo próximo pago es hoy o anterior
        $suscripcionesVencidas = Suscripcion::where('estado', 'activa')
            ->where('proximo_pago', '<=', $hoy)
            ->get();

        if ($suscripcionesVencidas->isEmpty()) {
            $this->info('No hay suscripciones vencidas hoy.');
            Log::info('Verificación de vencimientos: No hay suscripciones vencidas.');
            return 0;
        }

        foreach ($suscripcionesVencidas as $suscripcion) {
            try {
                \DB::beginTransaction();

                // Marcar suscripción como vencida
                $suscripcion->update(['estado' => 'vencida']);
                
                Log::warning("Suscripción #{$suscripcion->id} marcada como vencida", [
                    'user_id' => $suscripcion->user_id,
                    'plan_id' => $suscripcion->plan_id,
                    'proximo_pago' => $suscripcion->proximo_pago,
                ]);

                // Desactivar la integración del usuario
                $integracionConfig = \App\Models\IntegracionConfig::where('user_id', $suscripcion->user_id)
                    ->where('activo', true)
                    ->first();

                if ($integracionConfig) {
                    $integracionConfig->update(['activo' => false]);
                    
                    Log::warning("Integración desactivada por vencimiento de suscripción", [
                        'user_id' => $suscripcion->user_id,
                        'suscripcion_id' => $suscripcion->id,
                        'integracion_config_id' => $integracionConfig->id
                    ]);
                }

                \DB::commit();

                // Notificar al usuario (opcional)
                $this->info("Suscripción #{$suscripcion->id} del usuario {$suscripcion->user->name} marcada como vencida e integración desactivada.");
            } catch (\Exception $e) {
                \DB::rollBack();
                Log::error("Error al procesar vencimiento de suscripción #{$suscripcion->id}: " . $e->getMessage());
                $this->error("Error al procesar suscripción #{$suscripcion->id}");
            }
        }

        $this->info("Se procesaron {$suscripcionesVencidas->count()} suscripciones vencidas.");
        return 0;
    }
}
