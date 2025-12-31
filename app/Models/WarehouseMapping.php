<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseMapping extends Model
{
    protected $fillable = [
        'user_id',
        'sync_mode',
        'default_bodega_id',
        'default_bodega_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locationMappings()
    {
        return $this->hasMany(LocationBodegaMapping::class, 'user_id', 'user_id');
    }

    // Obtener configuración activa del usuario
    public static function getConfig($userId)
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'sync_mode' => 'simple',
                'default_bodega_id' => null,
            ]
        );
    }

    // Verificar si está en modo simple
    public function isSimpleMode()
    {
        return $this->sync_mode === 'simple';
    }

    // Verificar si está en modo avanzado
    public function isAdvancedMode()
    {
        return $this->sync_mode === 'advanced';
    }
}
