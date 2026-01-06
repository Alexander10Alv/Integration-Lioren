<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'flow_token',
        'subject',
        'amount',
        'currency',
        'email',
        'payment_method',
        'status',
        'flow_response',
        'paid_at',
        'user_id',
        'solicitud_id', // Nueva relación
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'flow_response' => 'array',
        'paid_at' => 'datetime',
    ];

    // Estados de pago
    const STATUS_CREATED = 0;
    const STATUS_PENDING = 1;
    const STATUS_PAID = 2;
    const STATUS_REJECTED = 3;
    const STATUS_CANCELLED = 4;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            self::STATUS_CREATED => 'Creado',
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_PAID => 'Pagado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconocido',
        };
    }

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }
}
