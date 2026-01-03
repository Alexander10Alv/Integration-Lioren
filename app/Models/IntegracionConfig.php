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
        'shopify_visibility_enabled',
        'notas_credito_enabled',
        'order_limit_enabled',
        'monthly_order_limit',
        'activo',
        'ultima_sincronizacion',
    ];

    protected $casts = [
        'facturacion_enabled' => 'boolean',
        'shopify_visibility_enabled' => 'boolean',
        'notas_credito_enabled' => 'boolean',
        'order_limit_enabled' => 'boolean',
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
