<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMapping extends Model
{
    protected $fillable = [
        'user_id',
        'shopify_product_id',
        'shopify_variant_id',
        'lioren_product_id',
        'sku',
        'product_title',
        'price',
        'stock',
        'sync_status',
        'sync_error',
        'last_synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(SyncLog::class, 'entity_id', 'id')
            ->where('entity_type', 'product');
    }

    // Buscar por SKU
    public static function findBySku($sku, $userId)
    {
        return static::where('sku', $sku)
            ->where('user_id', $userId)
            ->first();
    }

    // Buscar por ID de Shopify
    public static function findByShopifyId($shopifyId, $userId)
    {
        return static::where('shopify_product_id', $shopifyId)
            ->where('user_id', $userId)
            ->first();
    }

    // Buscar por ID de Lioren
    public static function findByLiorenId($liorenId, $userId)
    {
        return static::where('lioren_product_id', $liorenId)
            ->where('user_id', $userId)
            ->first();
    }

    // Marcar como sincronizado
    public function markAsSynced()
    {
        $this->update([
            'sync_status' => 'synced',
            'sync_error' => null,
            'last_synced_at' => now(),
        ]);
    }

    // Marcar como error
    public function markAsError($errorMessage)
    {
        $this->update([
            'sync_status' => 'error',
            'sync_error' => $errorMessage,
        ]);
    }
}
