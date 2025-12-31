<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingLocationMapping extends Model
{
    protected $fillable = [
        'user_id',
        'shopify_location_id',
        'shopify_location_name',
        'affected_products_count',
        'status',
        'first_detected_at',
        'last_notified_at',
        'resolved_at',
    ];

    protected $casts = [
        'first_detected_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Incrementar contador de productos afectados
    public function incrementAffectedCount()
    {
        $this->increment('affected_products_count');
    }

    // Marcar como notificado
    public function markAsNotified()
    {
        $this->update([
            'status' => 'notified',
            'last_notified_at' => now(),
        ]);
    }

    // Marcar como resuelto
    public function markAsResolved()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    // Obtener locations pendientes
    public static function getPending($userId)
    {
        return static::where('user_id', $userId)
            ->whereIn('status', ['pending', 'notified'])
            ->orderBy('first_detected_at', 'desc')
            ->get();
    }
}
