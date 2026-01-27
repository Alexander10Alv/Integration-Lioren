-- ============================================
-- SOLICITUD DE PRUEBA - Plan de Prueba Gratuito
-- Cliente: Cliente Demo (ID: 2)
-- Plan: Plan de Prueba Gratuito (ID: 1)
-- ============================================

USE al_shopify_integrator;

-- Crear solicitud (sin credenciales aún, las ingresarás desde la interfaz)
INSERT INTO `solicitudes` (
    `cliente_id`,
    `plan_id`,
    `tienda_shopify`,
    `descripcion`,
    `telefono`,
    `email`,
    `access_token`,
    `api_secret`,
    `api_key`,
    `estado`,
    `notas_admin`,
    `integracion_conectada`,
    `fecha_conexion`,
    `created_at`,
    `updated_at`
) VALUES (
    2, -- Cliente Demo
    1, -- Plan de Prueba Gratuito
    NULL, -- Lo ingresarás desde la interfaz
    'Solicitud de prueba para testing del sistema multi-cliente',
    NULL,
    'cliente@demo.com',
    NULL, -- Lo ingresarás desde la interfaz
    NULL, -- Lo ingresarás desde la interfaz
    NULL, -- Lo ingresarás desde la interfaz
    'en_proceso', -- Estado: como si ya hubiera pagado
    NULL,
    0, -- No conectada aún
    NULL,
    NOW(),
    NOW()
);

-- Obtener el ID de la solicitud
SET @solicitud_id = LAST_INSERT_ID();

-- Crear pago simulado
INSERT INTO `payments` (
    `order_id`,
    `flow_token`,
    `subject`,
    `amount`,
    `currency`,
    `email`,
    `payment_method`,
    `status`,
    `flow_response`,
    `paid_at`,
    `periodo_inicio`,
    `periodo_fin`,
    `user_id`,
    `solicitud_id`,
    `suscripcion_id`,
    `created_at`,
    `updated_at`
) VALUES (
    CONCAT('TEST_', UNIX_TIMESTAMP()),
    CONCAT('TEST_TOKEN_', UNIX_TIMESTAMP()),
    'Pago de prueba - Plan de Prueba Gratuito',
    360.00,
    'CLP',
    'cliente@demo.com',
    9,
    2, -- Pagado
    '{"status":"success","test":true}',
    NOW(),
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
    2, -- Cliente Demo
    @solicitud_id,
    NULL,
    NOW(),
    NOW()
);

SET @payment_id = LAST_INSERT_ID();

-- Crear suscripción activa
INSERT INTO `suscripciones` (
    `user_id`,
    `plan_id`,
    `estado`,
    `fecha_inicio`,
    `fecha_fin`,
    `proximo_pago`,
    `created_at`,
    `updated_at`
) VALUES (
    2, -- Cliente Demo
    1, -- Plan de Prueba Gratuito
    'activa',
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
    NOW(),
    NOW()
);

SET @suscripcion_id = LAST_INSERT_ID();

-- Actualizar payment con suscripcion_id
UPDATE `payments` 
SET `suscripcion_id` = @suscripcion_id 
WHERE `id` = @payment_id;

-- ============================================
-- VERIFICACIÓN
-- ============================================
SELECT 
    '✅ SOLICITUD CREADA' as resultado,
    @solicitud_id as solicitud_id,
    @payment_id as payment_id,
    @suscripcion_id as suscripcion_id;

SELECT 
    s.id,
    u.name as cliente,
    p.nombre as plan,
    s.estado,
    s.integracion_conectada,
    'Ahora ve a /cliente/solicitudes/credenciales para ingresar tus credenciales' as siguiente_paso
FROM solicitudes s
JOIN users u ON s.cliente_id = u.id
JOIN planes p ON s.plan_id = p.id
WHERE s.id = @solicitud_id;

-- ============================================
-- SIGUIENTE PASO:
-- ============================================
-- 1. Inicia sesión como: cliente@demo.com
-- 2. Ve a: http://127.0.0.1:8000/cliente/solicitudes/credenciales
-- 3. Ingresa tus credenciales reales de Shopify y Lioren
-- 4. Luego como admin ve a: http://127.0.0.1:8000/admin/solicitudes-pendientes-conexion
-- 5. Haz clic en "Conectar" para activar la integración
-- ============================================
