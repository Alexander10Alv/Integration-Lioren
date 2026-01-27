<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('suscripcion_id')->nullable()->after('solicitud_id')->constrained('suscripciones')->onDelete('set null');
            $table->date('periodo_inicio')->nullable()->after('paid_at');
            $table->date('periodo_fin')->nullable()->after('periodo_inicio');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['suscripcion_id']);
            $table->dropColumn(['suscripcion_id', 'periodo_inicio', 'periodo_fin']);
        });
    }
};
