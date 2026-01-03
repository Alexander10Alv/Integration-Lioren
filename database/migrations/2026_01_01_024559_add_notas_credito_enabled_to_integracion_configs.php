<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->boolean('notas_credito_enabled')->default(false)->after('shopify_visibility_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->dropColumn('notas_credito_enabled');
        });
    }
};
