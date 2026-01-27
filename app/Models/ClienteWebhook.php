<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'solicitud_id',
        'webhook_shopify_id',
        'topic',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }
}
