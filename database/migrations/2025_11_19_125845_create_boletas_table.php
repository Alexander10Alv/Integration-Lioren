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
        Schema::create('boletas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Datos de Lioren
            $table->bigInteger('lioren_id')->nullable();
            $table->string('tipodoc', 3)->default('39'); // 39 = Boleta Afecta
            $table->integer('folio')->nullable();
            $table->date('fecha');
            
            // Datos del receptor (opcional)
            $table->string('receptor_rut', 20)->nullable();
            $table->string('receptor_nombre', 100)->nullable();
            $table->string('receptor_email', 80)->nullable();
            
            // Montos
            $table->integer('monto_neto')->default(0);
            $table->integer('monto_exento')->default(0);
            $table->integer('monto_iva')->default(0);
            $table->integer('monto_total');
            
            // Archivos
            $table->text('pdf_base64')->nullable();
            $table->text('xml_base64')->nullable();
            
            // Detalles y metadata
            $table->json('detalles')->nullable();
            $table->json('pagos')->nullable();
            $table->text('observaciones')->nullable();
            
            // Estado
            $table->enum('status', ['emitida', 'anulada', 'error'])->default('emitida');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('folio');
            $table->index('fecha');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boletas');
    }
};
