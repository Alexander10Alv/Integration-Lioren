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
        // Tabla de chats/conversaciones
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained('planes')->onDelete('set null');
            $table->string('contexto'); // Ej: "Plan: Plan Básico - $50"
            $table->enum('estado', ['activo', 'cerrado'])->default('activo');
            $table->integer('mensaje_count')->default(0);
            $table->timestamp('ultimo_mensaje_at')->nullable();
            $table->timestamp('cerrado_at')->nullable();
            $table->string('cerrado_por')->nullable(); // 'cliente', 'admin', 'sistema'
            $table->timestamps();
        });

        // Tabla de mensajes
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('mensaje');
            $table->string('archivo_path')->nullable();
            $table->string('archivo_nombre')->nullable();
            $table->boolean('leido')->default(false);
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
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chats');
    }
};
