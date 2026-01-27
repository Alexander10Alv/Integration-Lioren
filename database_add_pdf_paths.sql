-- =====================================================
-- AGREGAR CAMPOS pdf_path Y xml_path A LAS TABLAS
-- Alternativa a ejecutar php artisan migrate
-- =====================================================

-- 1. Agregar campos a tabla boletas
ALTER TABLE `boletas` 
ADD COLUMN `pdf_path` VARCHAR(255) NULL AFTER `monto_total`,
ADD COLUMN `xml_path` VARCHAR(255) NULL AFTER `pdf_path`;

-- 2. Agregar campos a tabla notas_credito
ALTER TABLE `notas_credito` 
ADD COLUMN `pdf_path` VARCHAR(255) NULL AFTER `monto_total`,
ADD COLUMN `xml_path` VARCHAR(255) NULL AFTER `pdf_path`;

-- 3. Agregar campos a tabla facturas_emitidas (si existe)
ALTER TABLE `facturas_emitidas` 
ADD COLUMN `pdf_path` VARCHAR(255) NULL AFTER `monto_total`,
ADD COLUMN `xml_path` VARCHAR(255) NULL AFTER `pdf_path`;

-- =====================================================
-- VERIFICAR LOS CAMBIOS
-- =====================================================
DESCRIBE boletas;
DESCRIBE notas_credito;
DESCRIBE facturas_emitidas;

-- =====================================================
-- NOTA: Después de ejecutar esto, ejecuta:
-- php artisan pdfs:migrate-to-files
-- para migrar los PDFs existentes a archivos
-- =====================================================
