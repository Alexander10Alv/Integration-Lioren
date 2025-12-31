<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaEmitida extends Model
{
    use HasFactory;

    protected $table = 'facturas_emitidas';

    protected $fillable = [
        'shopify_order_id',
        'shopify_order_number',
        'tipo_documento',
        'lioren_factura_id',
        'folio',
        'rut_receptor',
        'razon_social',
        'monto_neto',
        'monto_iva',
        'monto_total',
        'pdf_base64',
        'xml_base64',
        'status',
        'error_message',
        'emitida_at',
    ];

    protected $casts = [
        'emitida_at' => 'datetime',
    ];
}
