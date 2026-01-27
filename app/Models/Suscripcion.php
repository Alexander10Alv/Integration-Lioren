<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'user_id',
        'plan_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'proximo_pago',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'proximo_pago' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function pagos()
    {
        return $this->hasMany(Payment::class, 'suscripcion_id');
    }

    public function estaActiva()
    {
        return $this->estado === 'activa';
    }

    public function estaVencida()
    {
        return $this->estado === 'vencida';
    }

    public function diasRestantes()
    {
        return now()->diffInDays($this->proximo_pago, false);
    }
}
