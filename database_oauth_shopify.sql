-- ============================================
-- MIGRACIÓN OAUTH 2.0 SHOPIFY
-- Fecha: 2026-01-26
-- ============================================

-- Agregar campos OAuth a integracion_configs
ALTER TABLE `integracion_configs` 
ADD COLUMN `auth_method` ENUM('manual', 'oauth') NOT NULL DEFAULT 'oauth' AFTER `shopify_secret`,
ADD COLUMN `oauth_installed_at` TIMESTAMP NULL DEFAULT NULL AFTER `auth_method`,
ADD COLUMN `shop_domain` VARCHAR(255) NULL DEFAULT NULL AFTER `oauth_installed_at`;

-- Opcional: Actualizar registros existentes a método 'manual'
UPDATE `integracion_configs` SET `auth_method` = 'manual' WHERE `created_at` < '2026-01-26';

-- ============================================
-- ROLLBACK (si necesitas revertir)
-- ============================================
-- ALTER TABLE `integracion_configs` 
-- DROP COLUMN `auth_method`,
-- DROP COLUMN `oauth_installed_at`,
-- DROP COLUMN `shop_domain`;
