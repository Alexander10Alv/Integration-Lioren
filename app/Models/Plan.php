<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'caracteristicas',
        'facturacion_enabled',
        'shopify_visibility_enabled',
        'notas_credito_enabled',
        'order_limit_enabled',
        'monthly_order_limit',
        'precio',
        'moneda',
        'activo',
    ];

    protected $casts = [
        'caracteristicas' => 'array',
        'facturacion_enabled' => 'boolean',
        'shopify_visibility_enabled' => 'boolean',
        'notas_credito_enabled' => 'boolean',
        'order_limit_enabled' => 'boolean',
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
