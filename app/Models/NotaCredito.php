<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaCredito extends Model
{
    use HasFactory;

    protected $table = 'notas_credito';

    protected $fillable = [
        'shopify_order_id',
        'shopify_order_number',
        'tipo_documento_original',
        'folio_original',
        'lioren_nota_id',
        'folio',
        'rut_receptor',
        'razon_social',
        'monto_neto',
        'monto_iva',
        'monto_total',
        'pdf_base64',
        'xml_base64',
        'status',
        'glosa',
        'error_message',
        'emitida_at',
    ];

    protected $casts = [
        'monto_neto' => 'decimal:2',
        'monto_iva' => 'decimal:2',
        'monto_total' => 'decimal:2',
        'emitida_at' => 'datetime',
    ];
}
