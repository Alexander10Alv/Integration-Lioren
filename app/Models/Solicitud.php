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
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
