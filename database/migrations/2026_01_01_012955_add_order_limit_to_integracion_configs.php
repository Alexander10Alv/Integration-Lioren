<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->boolean('order_limit_enabled')->default(false)->after('shopify_visibility_enabled');
            $table->integer('monthly_order_limit')->nullable()->after('order_limit_enabled');
        });
    }

    public function down()
    {
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->dropColumn(['order_limit_enabled', 'monthly_order_limit']);
        });
    }
};
