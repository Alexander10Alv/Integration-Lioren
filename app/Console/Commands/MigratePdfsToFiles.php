<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Boleta;
use App\Models\NotaCredito;
use App\Models\FacturaEmitida;

class MigratePdfsToFiles extends Command
{
    protected $signature = 'pdfs:migrate-to-files';
    protected $description = 'Migrar PDFs y XMLs de base64 en BD a archivos en storage';

    public function handle()
    {
        $this->info('🚀 Iniciando migración de PDFs y XMLs a archivos...');

        // Migrar Boletas
        $this->info("\n📄 Migrando Boletas...");
        $boletas = Boleta::whereNotNull('pdf_base64')
            ->whereNull('pdf_path')
            ->get();
        
        $boletasCount = 0;
        foreach ($boletas as $boleta) {
            if ($boleta->pdf_base64) {
                $boleta->pdf_path = $boleta->savePdfFromBase64($boleta->pdf_base64);
            }
            if ($boleta->xml_base64) {
                $boleta->xml_path = $boleta->saveXmlFromBase64($boleta->xml_base64);
            }
            $boleta->save();
            $boletasCount++;
            $this->info("  ✓ Boleta #{$boleta->folio} migrada");
        }
        $this->info("✅ {$boletasCount} boletas migradas");

        // Migrar Notas de Crédito
        $this->info("\n📄 Migrando Notas de Crédito...");
        $notasCredito = NotaCredito::whereNotNull('pdf_base64')
            ->whereNull('pdf_path')
            ->get();
        
        $ncCount = 0;
        foreach ($notasCredito as $nc) {
            if ($nc->pdf_base64) {
                $nc->pdf_path = $nc->savePdfFromBase64($nc->pdf_base64);
            }
            if ($nc->xml_base64) {
                $nc->xml_path = $nc->saveXmlFromBase64($nc->xml_base64);
            }
            $nc->save();
            $ncCount++;
            $this->info("  ✓ Nota de Crédito #{$nc->folio} migrada");
        }
        $this->info("✅ {$ncCount} notas de crédito migradas");

        // Migrar Facturas Emitidas
        $this->info("\n📄 Migrando Facturas...");
        $facturas = FacturaEmitida::whereNotNull('pdf_base64')
            ->whereNull('pdf_path')
            ->get();
        
        $facturasCount = 0;
        foreach ($facturas as $factura) {
            if ($factura->pdf_base64) {
                $factura->pdf_path = $factura->savePdfFromBase64($factura->pdf_base64);
            }
            if ($factura->xml_base64) {
                $factura->xml_path = $factura->saveXmlFromBase64($factura->xml_base64);
            }
            $factura->save();
            $facturasCount++;
            $this->info("  ✓ Factura #{$factura->folio} migrada");
        }
        $this->info("✅ {$facturasCount} facturas migradas");

        $this->info("\n🎉 Migración completada!");
        $this->info("Total: {$boletasCount} boletas, {$ncCount} notas de crédito, {$facturasCount} facturas");
        $this->warn("\n⚠️  Los campos pdf_base64 y xml_base64 aún contienen los datos.");
        $this->warn("   Puedes eliminarlos manualmente después de verificar que todo funciona correctamente.");
        
        return 0;
    }
}
