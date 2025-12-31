<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Agregar campo para habilitar facturación en la configuración
        Schema::table('product_mappings', function (Blueprint $table) {
            $table->boolean('facturacion_enabled')->default(false)->after('last_synced_at');
        });

        // Crear tabla para almacenar facturas emitidas
        Schema::create('facturas_emitidas', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_order_id')->unique();
            $table->string('shopify_order_number');
            $table->string('tipo_documento')->default('33'); // 33=Factura, 39=Boleta
            $table->integer('lioren_factura_id')->nullable();
            $table->integer('folio')->nullable();
            $table->string('rut_receptor')->nullable();
            $table->string('razon_social')->nullable();
            $table->decimal('monto_neto', 10, 2)->default(0);
            $table->decimal('monto_iva', 10, 2)->default(0);
            $table->decimal('monto_total', 10, 2)->default(0);
            $table->text('pdf_base64')->nullable();
            $table->text('xml_base64')->nullable();
            $table->string('status')->default('pending'); // pending, emitida, error
            $table->text('error_message')->nullable();
            $table->timestamp('emitida_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('product_mappings', function (Blueprint $table) {
            $table->dropColumn('facturacion_enabled');
        });
        
        Schema::dropIfExists('facturas_emitidas');
    }
};
