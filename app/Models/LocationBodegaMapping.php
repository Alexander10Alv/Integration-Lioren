<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationBodegaMapping extends Model
{
    protected $fillable = [
        'user_id',
        'shopify_location_id',
        'shopify_location_name',
        'lioren_bodega_id',
        'lioren_bodega_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Buscar bodega para una location
    public static function getBodegaForLocation($locationId, $userId)
    {
        $mapping = static::where('shopify_location_id', $locationId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        return $mapping ? $mapping->lioren_bodega_id : null;
    }

    // Obtener todas las locations mapeadas
    public static function getMappedLocations($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
    }
}
