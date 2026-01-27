-- =====================================================
-- AGREGAR CAMPOS DE CARACTERÍSTICAS DE LIOREN A PLANES
-- Ejecuta estas consultas en tu base de datos MySQL
-- =====================================================

ALTER TABLE `planes` 
ADD COLUMN `facturacion_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `caracteristicas`,
ADD COLUMN `shopify_visibility_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `facturacion_enabled`,
ADD COLUMN `notas_credito_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `shopify_visibility_enabled`,
ADD COLUMN `order_limit_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `notas_credito_enabled`,
ADD COLUMN `monthly_order_limit` int(11) NULL AFTER `order_limit_enabled`;

-- =====================================================
-- ¡LISTO! Ahora los planes pueden tener características de Lioren
-- =====================================================
