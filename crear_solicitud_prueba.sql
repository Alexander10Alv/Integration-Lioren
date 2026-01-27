-- ============================================
-- CREAR SOLICITUD DE PRUEBA (SIN PAGAR)
-- Simula que un cliente solicitó un plan, pagó y está listo para conectar
-- ============================================

-- PASO 1: Verificar IDs disponibles
-- Ejecuta estas consultas primero para obtener los IDs correctos:

-- Ver planes disponibles
SELECT id, nombre, empresa_id, precio, moneda FROM planes;

-- Ver usuarios cliente disponibles
SELECT id, name, email, role FROM users WHERE role = 'cliente';

-- ============================================
-- PASO 2: Crear la solicitud con credenciales
-- ============================================

-- AJUSTA ESTOS VALORES:
-- @cliente_id = ID del usuario cliente (ej: 2)
-- @plan_id = ID del plan que quieres probar (ej: 1)
-- @email = Email del cliente

SET @cliente_id = 2; -- CAMBIA ESTO por el ID de tu usuario cliente
SET @plan_id = 1;    -- CAMBIA ESTO por el ID del plan que quieres probar
SET @email = 'cliente@test.com'; -- CAMBIA ESTO por el email del cliente

-- Insertar solicitud con credenciales de prueba
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
    @cliente_id,
    @plan_id,
    'mi-tienda-prueba.myshopify.com', -- Tienda de prueba
    'Solicitud de prueba para testing',
    '+56912345678',
    @email,
    'shpat_test_access_token_1234567890abcdef', -- Token de prueba (20+ caracteres)
    'shpss_test_api_secret_1234567890abcdef',   -- Secret de prueba (20+ caracteres)
    'test_lioren_api_key_1234567890',           -- API Key de prueba (10+ caracteres)
    'en_proceso', -- Estado: en_proceso (como si ya hubiera pagado)
    NULL,
    0, -- No conectada aún
    NULL,
    NOW(),
    NOW()
);

-- Obtener el ID de la solicitud recién creada
SET @solicitud_id = LAST_INSERT_ID();

-- ============================================
-- PASO 3: Crear el pago simulado
-- ============================================

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
    CONCAT('TEST_', UNIX_TIMESTAMP()), -- Order ID único
    CONCAT('TEST_TOKEN_', UNIX_TIMESTAMP()), -- Token de prueba
    'Pago de prueba - Plan Test',
    50000, -- Monto de prueba
    'CLP',
    @email,
    9, -- Método de pago (9 = Todos los medios)
    2, -- Status: 2 = Pagado
    '{"status":"success","test":true}', -- Respuesta simulada
    NOW(), -- Pagado ahora
    CURDATE(), -- Periodo inicio hoy
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH), -- Periodo fin en 1 mes
    @cliente_id,
    @solicitud_id,
    NULL, -- Suscripción se crea después
    NOW(),
    NOW()
);

-- Obtener el ID del pago
SET @payment_id = LAST_INSERT_ID();

-- ============================================
-- PASO 4: Crear la suscripción activa
-- ============================================

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
    @cliente_id,
    @plan_id,
    'activa', -- Estado activa
    CURDATE(), -- Inicio hoy
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH), -- Fin en 1 mes
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH), -- Próximo pago en 1 mes
    NOW(),
    NOW()
);

-- Obtener el ID de la suscripción
SET @suscripcion_id = LAST_INSERT_ID();

-- Actualizar el payment con el suscripcion_id
UPDATE `payments` 
SET `suscripcion_id` = @suscripcion_id 
WHERE `id` = @payment_id;

-- ============================================
-- PASO 5: Verificar que todo se creó correctamente
-- ============================================

-- Ver la solicitud creada
SELECT 
    s.id,
    s.cliente_id,
    u.name as cliente_nombre,
    s.plan_id,
    p.nombre as plan_nombre,
    s.tienda_shopify,
    s.estado,
    s.integracion_conectada,
    s.created_at
FROM solicitudes s
JOIN users u ON s.cliente_id = u.id
JOIN planes p ON s.plan_id = p.id
WHERE s.id = @solicitud_id;

-- Ver el pago creado
SELECT 
    id,
    order_id,
    subject,
    amount,
    currency,
    status,
    paid_at,
    solicitud_id,
    suscripcion_id
FROM payments
WHERE id = @payment_id;

-- Ver la suscripción creada
SELECT 
    id,
    user_id,
    plan_id,
    estado,
    fecha_inicio,
    fecha_fin,
    proximo_pago
FROM suscripciones
WHERE id = @suscripcion_id;

-- ============================================
-- RESULTADO ESPERADO:
-- ============================================
-- Ahora deberías poder:
-- 1. Como CLIENTE: Ir a /cliente/solicitudes/credenciales y ver tu solicitud
-- 2. Como ADMIN: Ir a /admin/solicitudes-pendientes-conexion y ver la solicitud lista para conectar
-- 3. Hacer clic en "Conectar" y el sistema validará las credenciales (fallarán porque son de prueba)
--
-- NOTA: Las credenciales son de prueba, así que la validación fallará.
-- Si quieres probar la conexión real, reemplaza:
-- - access_token con tu token real de Shopify
-- - api_secret con tu secret real de Shopify  
-- - api_key con tu API key real de Lioren
-- - tienda_shopify con tu tienda real
-- ============================================

-- ============================================
-- BONUS: Consulta para ver solicitudes pendientes de conexión
-- ============================================
SELECT 
    s.id,
    u.name as cliente,
    u.email,
    p.nombre as plan,
    s.tienda_shopify,
    s.estado,
    s.integracion_conectada,
    pay.paid_at as fecha_pago,
    s.created_at
FROM solicitudes s
JOIN users u ON s.cliente_id = u.id
JOIN planes p ON s.plan_id = p.id
LEFT JOIN payments pay ON pay.solicitud_id = s.id
WHERE s.estado = 'en_proceso' 
  AND s.integracion_conectada = 0
  AND s.tienda_shopify IS NOT NULL
ORDER BY s.created_at DESC;
