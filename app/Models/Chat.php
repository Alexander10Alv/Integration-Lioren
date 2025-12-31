<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'plan_id',
        'contexto',
        'estado',
        'mensaje_count',
        'ultimo_mensaje_at',
        'cerrado_at',
        'cerrado_por',
    ];

    protected $casts = [
        'ultimo_mensaje_at' => 'datetime',
        'cerrado_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function mensajes()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    public function ultimoMensaje()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function mensajesNoLeidos()
    {
        return $this->mensajes()->where('leido', false);
    }
}
