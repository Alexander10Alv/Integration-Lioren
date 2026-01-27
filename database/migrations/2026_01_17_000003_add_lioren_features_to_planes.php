<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('planes', function (Blueprint $table) {
            // Características específicas de Lioren
            $table->boolean('facturacion_enabled')->default(false)->after('caracteristicas');
            $table->boolean('shopify_visibility_enabled')->default(false)->after('facturacion_enabled');
            $table->boolean('notas_credito_enabled')->default(false)->after('shopify_visibility_enabled');
            $table->boolean('order_limit_enabled')->default(false)->after('notas_credito_enabled');
            $table->integer('monthly_order_limit')->nullable()->after('order_limit_enabled');
        });
    }

    public function down()
    {
        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn([
                'facturacion_enabled',
                'shopify_visibility_enabled',
                'notas_credito_enabled',
                'order_limit_enabled',
                'monthly_order_limit'
            ]);
        });
    }
};
