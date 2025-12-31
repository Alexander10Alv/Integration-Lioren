<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lioren_id',
        'tipodoc',
        'folio',
        'fecha',
        'receptor_rut',
        'receptor_nombre',
        'receptor_email',
        'monto_neto',
        'monto_exento',
        'monto_iva',
        'monto_total',
        'pdf_base64',
        'xml_base64',
        'detalles',
        'pagos',
        'observaciones',
        'status',
        'error_message',
    ];

    protected $casts = [
        'detalles' => 'array',
        'pagos' => 'array',
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPdfUrlAttribute()
    {
        return route('boletas.pdf', $this->id);
    }
}
