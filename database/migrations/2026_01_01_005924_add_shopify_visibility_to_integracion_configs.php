<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->boolean('shopify_visibility_enabled')->default(false)->after('facturacion_enabled');
        });
    }

    public function down()
    {
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->dropColumn('shopify_visibility_enabled');
        });
    }
};
