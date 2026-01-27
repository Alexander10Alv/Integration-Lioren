<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar campos a solicitudes para tracking de conexión
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->boolean('integracion_conectada')->default(false)->after('estado');
            $table->timestamp('fecha_conexion')->nullable()->after('integracion_conectada');
        });

        // 2. Relacionar integracion_configs con solicitudes
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('solicitud_id')->nullable()->after('user_id');
            $table->foreign('solicitud_id')->references('id')->on('solicitudes')->onDelete('set null');
        });

        // 3. Agregar user_id a facturas_emitidas
        Schema::table('facturas_emitidas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // 4. Crear tabla para tracking de webhooks por cliente
        Schema::create('cliente_webhooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('solicitud_id')->nullable();
            $table->string('webhook_shopify_id');
            $table->string('topic');
            $table->text('address');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('solicitud_id')->references('id')->on('solicitudes')->onDelete('set null');
        });

        // 5. Agregar índices para performance
        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->index('activo');
        });

        Schema::table('solicitudes', function (Blueprint $table) {
            $table->index('estado');
            $table->index('integracion_conectada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['integracion_conectada']);
            $table->dropColumn(['integracion_conectada', 'fecha_conexion']);
        });

        Schema::table('integracion_configs', function (Blueprint $table) {
            $table->dropIndex(['activo']);
            $table->dropForeign(['solicitud_id']);
            $table->dropColumn('solicitud_id');
        });

        Schema::table('facturas_emitidas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::dropIfExists('cliente_webhooks');
    }
};
