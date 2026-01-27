-- ============================================
-- MIGRACION: Sistema Multi-Cliente de Integración
-- Fecha: 2026-01-17
-- Descripción: Permite que múltiples clientes tengan sus propias integraciones Shopify-Lioren
-- ============================================

-- 1. Agregar campos de credenciales a la tabla solicitudes (si no existen)
-- Ya existen: tienda_shopify, access_token, api_secret, api_key

-- 2. Agregar campo para indicar si la integración fue conectada
ALTER TABLE `solicitudes` 
ADD COLUMN `integracion_conectada` TINYINT(1) NOT NULL DEFAULT 0 AFTER `estado`,
ADD COLUMN `fecha_conexion` TIMESTAMP NULL DEFAULT NULL AFTER `integracion_conectada`;

-- 3. Asegurar que integracion_configs tenga relación con solicitud
ALTER TABLE `integracion_configs`
ADD COLUMN `solicitud_id` BIGINT(20) UNSIGNED NULL AFTER `user_id`,
ADD CONSTRAINT `integracion_configs_solicitud_id_foreign` 
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE SET NULL;

-- 4. Agregar índices para mejorar performance
ALTER TABLE `integracion_configs` ADD INDEX `integracion_configs_activo_index` (`activo`);
ALTER TABLE `solicitudes` ADD INDEX `solicitudes_estado_index` (`estado`);
ALTER TABLE `solicitudes` ADD INDEX `solicitudes_integracion_conectada_index` (`integracion_conectada`);

-- 5. Asegurar que warehouse_mappings y location_bodega_mappings tengan user_id
-- Ya tienen user_id ✅

-- 6. Agregar campo para tracking de webhooks por cliente
CREATE TABLE IF NOT EXISTS `cliente_webhooks` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `solicitud_id` BIGINT(20) UNSIGNED NULL,
  `webhook_shopify_id` VARCHAR(255) NOT NULL,
  `topic` VARCHAR(255) NOT NULL,
  `address` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_webhooks_user_id_foreign` (`user_id`),
  KEY `cliente_webhooks_solicitud_id_foreign` (`solicitud_id`),
  CONSTRAINT `cliente_webhooks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cliente_webhooks_solicitud_id_foreign` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Actualizar boletas y facturas para relacionarlas con user_id si no lo tienen
-- boletas ya tiene user_id ✅
-- facturas_emitidas NO tiene user_id, agregar:
ALTER TABLE `facturas_emitidas`
ADD COLUMN `user_id` BIGINT(20) UNSIGNED NULL AFTER `id`,
ADD CONSTRAINT `facturas_emitidas_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- 8. Agregar índice para búsqueda rápida de configuración por usuario
ALTER TABLE `integracion_configs` ADD UNIQUE INDEX `integracion_configs_user_id_activo_unique` (`user_id`, `activo`);

-- ============================================
-- CONSULTAS DE VERIFICACIÓN
-- ============================================

-- Ver solicitudes pendientes de conexión (pagadas pero no conectadas)
SELECT 
    s.id,
    s.cliente_id,
    u.name as cliente_nombre,
    u.email as cliente_email,
    p.nombre as plan_nombre,
    s.tienda_shopify,
    s.estado,
    s.integracion_conectada,
    s.created_at
FROM solicitudes s
JOIN users u ON s.cliente_id = u.id
JOIN planes p ON s.plan_id = p.id
WHERE s.estado = 'en_proceso' 
  AND s.integracion_conectada = 0
  AND s.tienda_shopify IS NOT NULL
ORDER BY s.created_at DESC;

-- Ver integraciones activas por cliente
SELECT 
    ic.id,
    ic.user_id,
    u.name as cliente_nombre,
    ic.shopify_tienda,
    ic.facturacion_enabled,
    ic.shopify_visibility_enabled,
    ic.notas_credito_enabled,
    ic.order_limit_enabled,
    ic.monthly_order_limit,
    ic.activo,
    ic.ultima_sincronizacion,
    COUNT(DISTINCT pm.id) as productos_sincronizados
FROM integracion_configs ic
JOIN users u ON ic.user_id = u.id
LEFT JOIN product_mappings pm ON pm.user_id = ic.user_id AND pm.sync_status = 'synced'
WHERE ic.activo = 1
GROUP BY ic.id
ORDER BY ic.created_at DESC;

-- Ver webhooks por cliente
SELECT 
    cw.id,
    cw.user_id,
    u.name as cliente_nombre,
    cw.topic,
    cw.webhook_shopify_id,
    cw.created_at
FROM cliente_webhooks cw
JOIN users u ON cw.user_id = u.id
ORDER BY cw.user_id, cw.topic;

-- Ver estadísticas de facturación por cliente
SELECT 
    u.id as user_id,
    u.name as cliente_nombre,
    COUNT(DISTINCT b.id) as total_boletas,
    SUM(b.monto_total) as total_boletas_monto,
    COUNT(DISTINCT f.id) as total_facturas,
    SUM(f.monto_total) as total_facturas_monto
FROM users u
LEFT JOIN boletas b ON b.user_id = u.id AND b.status = 'emitida'
LEFT JOIN facturas_emitidas f ON f.user_id = u.id AND f.status = 'emitida'
WHERE u.role = 'cliente'
GROUP BY u.id
ORDER BY (COALESCE(SUM(b.monto_total), 0) + COALESCE(SUM(f.monto_total), 0)) DESC;
