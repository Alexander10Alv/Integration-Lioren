-- =====================================================
-- CONSULTAS SQL PARA MÓDULO DE FACTURACIÓN
-- Ejecuta estas consultas en tu base de datos MySQL
-- =====================================================

-- 1. Crear tabla de configuración de integración
CREATE TABLE IF NOT EXISTS `integracion_configs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `shopify_tienda` varchar(255) NOT NULL,
  `shopify_token` text NOT NULL,
  `shopify_secret` text NOT NULL,
  `lioren_api_key` text NOT NULL,
  `facturacion_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultima_sincronizacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `integracion_configs_user_id_foreign` (`user_id`),
  CONSTRAINT `integracion_configs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Crear tabla de facturas emitidas
CREATE TABLE IF NOT EXISTS `facturas_emitidas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shopify_order_id` varchar(255) NOT NULL,
  `shopify_order_number` varchar(255) NOT NULL,
  `tipo_documento` varchar(255) NOT NULL DEFAULT '33',
  `lioren_factura_id` int(11) DEFAULT NULL,
  `folio` int(11) DEFAULT NULL,
  `rut_receptor` varchar(255) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `monto_neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pdf_base64` text DEFAULT NULL,
  `xml_base64` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `emitida_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facturas_emitidas_shopify_order_id_unique` (`shopify_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Crear tabla de notas de crédito
CREATE TABLE IF NOT EXISTS `notas_credito` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shopify_order_id` varchar(255) NOT NULL,
  `shopify_order_number` varchar(255) NOT NULL,
  `tipo_documento_original` varchar(255) NOT NULL,
  `folio_original` int(11) NOT NULL,
  `lioren_nota_id` int(11) DEFAULT NULL,
  `folio` int(11) DEFAULT NULL,
  `rut_receptor` varchar(255) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `monto_neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pdf_base64` longtext DEFAULT NULL,
  `xml_base64` longtext DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `glosa` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `emitida_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Agregar campo notas_credito_enabled a integracion_configs
ALTER TABLE `integracion_configs` 
ADD COLUMN `notas_credito_enabled` tinyint(1) NOT NULL DEFAULT 0 
AFTER `shopify_visibility_enabled`;

-- 5. Agregar campo facturacion_enabled a product_mappings (si existe)
-- Si la tabla product_mappings no existe, ignora este paso
ALTER TABLE `product_mappings` 
ADD COLUMN `facturacion_enabled` tinyint(1) NOT NULL DEFAULT 0 
AFTER `last_synced_at`;

-- =====================================================
-- ¡LISTO! Ahora tu sistema está preparado para facturación y notas de crédito
-- =====================================================
