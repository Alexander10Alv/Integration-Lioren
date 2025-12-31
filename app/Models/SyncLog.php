<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = [
        'user_id',
        'sync_type',
        'direction',
        'entity_type',
        'entity_id',
        'sku',
        'status',
        'message',
        'data',
        'retry_count',
        'next_retry_at',
    ];

    protected $casts = [
        'data' => 'array',
        'next_retry_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Crear log de éxito
    public static function logSuccess($userId, $type, $direction, $entityType, $entityId, $message = null, $data = null)
    {
        return static::create([
            'user_id' => $userId,
            'sync_type' => $type,
            'direction' => $direction,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ]);
    }

    // Crear log de error
    public static function logError($userId, $type, $direction, $entityType, $entityId, $message, $data = null)
    {
        return static::create([
            'user_id' => $userId,
            'sync_type' => $type,
            'direction' => $direction,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ]);
    }

    // Obtener logs recientes
    public static function getRecent($userId, $limit = 50)
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
