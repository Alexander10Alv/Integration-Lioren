<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('integracion_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('shopify_tienda');
            $table->text('shopify_token');
            $table->text('shopify_secret');
            $table->text('lioren_api_key');
            $table->boolean('facturacion_enabled')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamp('ultima_sincronizacion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('integracion_configs');
    }
};
