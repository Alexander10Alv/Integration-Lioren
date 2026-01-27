<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'cliente_id',
        'plan_id',
        'tienda_shopify',
        'descripcion',
        'telefono',
        'email',
        'access_token',
        'api_secret',
        'api_key',
        'estado',
        'notas_admin',
        'integracion_conectada',
        'fecha_conexion',
    ];

    protected $casts = [
        'integracion_conectada' => 'boolean',
        'fecha_conexion' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function integracionConfig()
    {
        return $this->hasOne(IntegracionConfig::class);
    }

    public function webhooks()
    {
        return $this->hasMany(ClienteWebhook::class);
    }

    public function suscripcion()
    {
        return $this->hasOne(Suscripcion::class, 'user_id', 'cliente_id')
            ->where('plan_id', $this->plan_id)
            ->where('estado', 'activa');
    }

    /**
     * Verificar si tiene credenciales completas
     */
    public function tieneCredencialesCompletas()
    {
        return !empty($this->tienda_shopify) && 
               !empty($this->access_token) && 
               !empty($this->api_secret) && 
               !empty($this->api_key);
    }

    /**
     * Verificar si está lista para conectar
     */
    public function puedeConectar()
    {
        return $this->estado === 'en_proceso' && 
               !$this->integracion_conectada && 
               $this->tieneCredencialesCompletas();
    }
}
