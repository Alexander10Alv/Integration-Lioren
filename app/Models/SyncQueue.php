<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncQueue extends Model
{
    protected $table = 'sync_queue';

    protected $fillable = [
        'user_id',
        'operation',
        'platform',
        'payload',
        'sku',
        'status',
        'attempts',
        'max_attempts',
        'last_error',
        'scheduled_at',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Agregar trabajo a la cola
    public static function enqueue($userId, $operation, $platform, $payload, $sku = null, $scheduledAt = null)
    {
        return static::create([
            'user_id' => $userId,
            'operation' => $operation,
            'platform' => $platform,
            'payload' => $payload,
            'sku' => $sku,
            'status' => 'pending',
            'scheduled_at' => $scheduledAt ?? now(),
        ]);
    }

    // Marcar como procesando
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'attempts' => $this->attempts + 1,
        ]);
    }

    // Marcar como completado
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    // Marcar como fallido
    public function markAsFailed($error)
    {
        $shouldRetry = $this->attempts < $this->max_attempts;

        $this->update([
            'status' => $shouldRetry ? 'pending' : 'failed',
            'last_error' => $error,
            'scheduled_at' => $shouldRetry ? now()->addMinutes(5 * $this->attempts) : null,
        ]);

        return $shouldRetry;
    }

    // Obtener trabajos pendientes
    public static function getPending($limit = 10)
    {
        return static::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    // Verificar si puede reintentar
    public function canRetry()
    {
        return $this->attempts < $this->max_attempts;
    }
}
