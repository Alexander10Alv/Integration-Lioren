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
        'solicitud_id',
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
        'auth_method',        // NUEVO - OAuth 2.0
        'oauth_installed_at', // NUEVO - OAuth 2.0
        'shop_domain',        // NUEVO - OAuth 2.0
    ];

    protected $casts = [
        'shopify_token' => 'encrypted',    // NUEVO - Encriptar credenciales
        'shopify_secret' => 'encrypted',   // NUEVO - Encriptar credenciales
        'lioren_api_key' => 'encrypted',   // NUEVO - Encriptar credenciales
        'facturacion_enabled' => 'boolean',
        'shopify_visibility_enabled' => 'boolean',
        'notas_credito_enabled' => 'boolean',
        'order_limit_enabled' => 'boolean',
        'activo' => 'boolean',
        'ultima_sincronizacion' => 'datetime',
        'oauth_installed_at' => 'datetime', // NUEVO - OAuth 2.0
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function webhooks()
    {
        return $this->hasMany(ClienteWebhook::class, 'user_id', 'user_id');
    }

    /**
     * Obtener la configuración activa (método legacy para compatibilidad)
     */
    public static function getActiva()
    {
        return self::where('activo', true)->first();
    }

    /**
     * Obtener la configuración activa de un usuario específico
     */
    public static function getActivaByUser($userId)
    {
        return self::where('user_id', $userId)
            ->where('activo', true)
            ->first();
    }
}
