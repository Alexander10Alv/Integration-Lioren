<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('planes')->onDelete('cascade');
            $table->enum('estado', ['activa', 'vencida', 'cancelada'])->default('activa');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('proximo_pago');
            $table->timestamps();
            
            $table->index(['user_id', 'estado']);
            $table->index('proximo_pago');
        });
    }

    public function down()
    {
        Schema::dropIfExists('suscripciones');
    }
};
