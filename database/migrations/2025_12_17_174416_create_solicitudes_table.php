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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('planes')->onDelete('cascade');
            $table->string('tienda_shopify')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email');
            $table->string('access_token')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('api_key')->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'en_proceso', 'activa'])->default('pendiente');
            $table->text('notas_admin')->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->string('flow_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes');
    }
};
