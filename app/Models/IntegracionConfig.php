<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegracionConfig extends Model
{
    use HasFactory;

    protected $table = 'integracion_configs';

    protected $fillable = [
        'user_id',
        'shopify_tienda',
        'shopify_token',
        'shopify_secret',
        'lioren_api_key',
        'facturacion_enabled',
        'activo',
        'ultima_sincronizacion',
    ];

    protected $casts = [
        'facturacion_enabled' => 'boolean',
        'activo' => 'boolean',
        'ultima_sincronizacion' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la configuración activa
     */
    public static function getActiva()
    {
        return self::where('activo', true)->first();
    }
}
