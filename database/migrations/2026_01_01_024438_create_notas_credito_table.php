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
        Schema::create('notas_credito', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_order_id');
            $table->string('shopify_order_number');
            $table->string('tipo_documento_original'); // 33=Factura, 39=Boleta
            $table->integer('folio_original'); // Folio del documento que se anula
            $table->integer('lioren_nota_id')->nullable();
            $table->integer('folio')->nullable(); // Folio de la Nota de Crédito
            $table->string('rut_receptor')->nullable();
            $table->string('razon_social')->nullable();
            $table->decimal('monto_neto', 10, 2)->default(0);
            $table->decimal('monto_iva', 10, 2)->default(0);
            $table->decimal('monto_total', 10, 2)->default(0);
            $table->longText('pdf_base64')->nullable();
            $table->longText('xml_base64')->nullable();
            $table->string('status')->default('pending'); // pending, emitida, error
            $table->string('glosa')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('emitida_at')->nullable();
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
        Schema::dropIfExists('notas_credito');
    }
};
