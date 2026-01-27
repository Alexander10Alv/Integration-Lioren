-- =====================================================
-- CONSULTAS SQL PARA MÓDULO DE SUSCRIPCIONES
-- Ejecuta estas consultas en tu base de datos MySQL
-- =====================================================

-- 1. Crear tabla de suscripciones
CREATE TABLE IF NOT EXISTS `suscripciones` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `estado` enum('activa','vencida','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `proximo_pago` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suscripciones_user_id_estado_index` (`user_id`, `estado`),
  KEY `suscripciones_proximo_pago_index` (`proximo_pago`),
  KEY `suscripciones_user_id_foreign` (`user_id`),
  KEY `suscripciones_plan_id_foreign` (`plan_id`),
  CONSTRAINT `suscripciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `suscripciones_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agregar campos de suscripción a la tabla payments
ALTER TABLE `payments` 
ADD COLUMN `suscripcion_id` bigint(20) UNSIGNED NULL AFTER `solicitud_id`,
ADD COLUMN `periodo_inicio` date NULL AFTER `paid_at`,
ADD COLUMN `periodo_fin` date NULL AFTER `periodo_inicio`;

-- 3. Agregar foreign key para suscripcion_id
ALTER TABLE `payments` 
ADD CONSTRAINT `payments_suscripcion_id_foreign` 
FOREIGN KEY (`suscripcion_id`) REFERENCES `suscripciones` (`id`) ON DELETE SET NULL;

-- =====================================================
-- ¡LISTO! Ahora tu sistema tiene el módulo de suscripciones
-- =====================================================

-- VERIFICACIÓN: Consultas para verificar que todo se creó correctamente
-- Ejecuta estas después de aplicar los cambios:

-- Ver estructura de suscripciones
DESCRIBE suscripciones;

-- Ver estructura actualizada de payments
DESCRIBE payments;

-- Ver foreign keys de suscripciones
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    TABLE_NAME = 'suscripciones'
    AND TABLE_SCHEMA = DATABASE();

-- Ver foreign keys de payments
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    TABLE_NAME = 'payments'
    AND TABLE_SCHEMA = DATABASE();
