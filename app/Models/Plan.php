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
        'precio',
        'activo',
    ];

    protected $casts = [
        'caracteristicas' => 'array',
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
