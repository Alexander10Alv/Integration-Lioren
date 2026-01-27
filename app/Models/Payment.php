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
        'solicitud_id',
        'suscripcion_id',
        'periodo_inicio',
        'periodo_fin',
    ];

    protected $casts = [
        'flow_response' => 'array',
        'paid_at' => 'datetime',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class);
    }

    public function isPaid()
    {
        return $this->status == 2;
    }

    public function isPending()
    {
        return $this->status == 0 || $this->status == 1;
    }

    public function isFailed()
    {
        return $this->status == 3 || $this->status == 4;
    }
}
