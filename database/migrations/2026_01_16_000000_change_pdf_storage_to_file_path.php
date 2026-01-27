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
        // Cambiar pdf_base64 y xml_base64 a pdf_path y xml_path en boletas
        Schema::table('boletas', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('monto_total');
            $table->string('xml_path')->nullable()->after('pdf_path');
        });

        // Cambiar en notas_credito
        Schema::table('notas_credito', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('monto_total');
            $table->string('xml_path')->nullable()->after('pdf_path');
        });

        // Cambiar en facturas_emitidas si existe
        if (Schema::hasTable('facturas_emitidas')) {
            Schema::table('facturas_emitidas', function (Blueprint $table) {
                $table->string('pdf_path')->nullable()->after('monto_total');
                $table->string('xml_path')->nullable()->after('pdf_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boletas', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'xml_path']);
        });

        Schema::table('notas_credito', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'xml_path']);
        });

        if (Schema::hasTable('facturas_emitidas')) {
            Schema::table('facturas_emitidas', function (Blueprint $table) {
                $table->dropColumn(['pdf_path', 'xml_path']);
            });
        }
    }
};
