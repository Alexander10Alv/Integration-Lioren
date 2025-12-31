<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'empresa',
        'rut',
        'telefono',
        'telefono_secundario',
        'direccion',
        'ciudad',
        'region',
        'codigo_postal',
        'giro',
        'notas',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
