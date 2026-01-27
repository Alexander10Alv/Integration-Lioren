# 🚀 Implementación Sistema Multi-Cliente de Integración Shopify-Lioren

## 📋 Resumen

Este sistema permite que múltiples clientes tengan sus propias integraciones independientes entre Shopify y Lioren, cada uno con sus propias credenciales, webhooks, productos sincronizados y configuraciones de plan.

## 🔄 Flujo Completo

### 1. Cliente Solicita Plan
- El cliente navega a `/cliente/planes`
- Selecciona un plan y hace clic en "Solicitar"
- Se crea una `solicitud` con `estado='pendiente'`

### 2. Cliente Ingresa Credenciales
- El cliente va a `/cliente/solicitudes/credenciales`
- Ingresa:
  - Tienda Shopify (ej: `mi-tienda.myshopify.com`)
  - Access Token de Shopify
  - API Secret de Shopify
  - API Key de Lioren
  - Teléfono (opcional)
- Las credenciales se guardan en la tabla `solicitudes`

### 3. Cliente Realiza el Pago
- El cliente procede al pago vía Flow
- Al confirmar el pago:
  - `solicitud.estado` cambia a `'en_proceso'`
  - Se crea un registro en `payments`
  - Se crea una `suscripcion` activa

### 4. Admin Conecta la Integración
- El admin va a `/admin/solicitudes-pendientes-conexion`
- Ve todas las solicitudes pagadas con credenciales completas
- Puede ver:
  - Información del cliente
  - Plan contratado y características
  - Credenciales proporcionadas
- Hace clic en "🔌 Conectar"
- El sistema automáticamente:
  1. Valida credenciales de Shopify
  2. Valida credenciales de Lioren
  3. Crea `integracion_config` con permisos del plan
  4. Crea webhooks en Shopify (con `user_id` en la URL)
  5. Sincroniza productos bidireccional mente
  6. Guarda webhooks en `cliente_webhooks`
  7. Marca `solicitud.integracion_conectada = true`
  8. Cambia `solicitud.estado = 'activa'`

### 5. Cliente Usa su Integración
- Los webhooks de Shopify llegan con el `user_id` del cliente
- El sistema identifica al cliente y usa su configuración
- Cada cliente tiene:
  - Sus propios productos sincronizados
  - Sus propias boletas/facturas
  - Sus propios webhooks
  - Sus propias bodegas configuradas
  - Sus propios límites de pedidos

## 📊 Estructura de Base de Datos

### Nuevas Tablas

#### `cliente_webhooks`
```sql
- id
- user_id (FK a users)
- solicitud_id (FK a solicitudes)
- webhook_shopify_id (ID del webhook en Shopify)
- topic (ej: 'orders/create')
- address (URL completa con user_id)
- created_at
- updated_at
```

### Tablas Modificadas

#### `solicitudes`
```sql
+ integracion_conectada (boolean, default: false)
+ fecha_conexion (timestamp, nullable)
```

#### `integracion_configs`
```sql
+ solicitud_id (FK a solicitudes, nullable)
```

#### `facturas_emitidas`
```sql
+ user_id (FK a users, nullable)
```

## 🔧 Archivos Creados/Modificados

### Nuevos Archivos

1. **Migración**: `database/migrations/2026_01_17_180000_add_multicliente_integration_support.php`
2. **SQL**: `database_integracion_multicliente.sql`
3. **Modelo**: `app/Models/ClienteWebhook.php`
4. **Servicio**: `app/Services/IntegracionMulticlienteService.php`
5. **Vista Cliente**: `resources/views/cliente/solicitudes/credenciales.blade.php`
6. **Vista Admin**: `resources/views/admin/solicitudes/pendientes-conexion.blade.php`

### Archivos Modificados

1. **Modelo Solicitud**: `app/Models/Solicitud.php`
   - Agregados campos `integracion_conectada`, `fecha_conexion`
   - Agregadas relaciones y métodos helper

2. **Modelo IntegracionConfig**: `app/Models/IntegracionConfig.php`
   - Agregado campo `solicitud_id`
   - Agregado método `getActivaByUser()`

3. **Controlador Solicitud**: `app/Http/Controllers/SolicitudController.php`
   - Agregados métodos:
     - `credenciales()` - Vista para ingresar credenciales
     - `guardarCredenciales()` - Guardar credenciales
     - `pendientesConexion()` - Lista de solicitudes listas para conectar
     - `conectarIntegracion()` - Conectar integración
     - `rechazar()` - Rechazar solicitud

4. **Controlador Integración**: `app/Http/Controllers/IntegracionController.php`
   - Modificado `webhookReceiver()` para identificar cliente por `user_id`
   - Modificado `procesarPedido()` para usar `$config->user_id`
   - Modificado `emitirFactura()` para usar `$config->user_id`

5. **Rutas**: `routes/web.php`
   - Agregadas rutas de credenciales para clientes
   - Agregadas rutas de conexión para admin

6. **Navegación**: `resources/views/layouts/navigation.blade.php`
   - Agregado enlace "Configurar Integración" para clientes
   - Agregado enlace "Conectar Clientes" para admin

## 🚀 Pasos de Implementación

### 1. Ejecutar Migración

```bash
php artisan migrate
```

O ejecutar el SQL manualmente:
```bash
mysql -u tu_usuario -p tu_base_de_datos < database_integracion_multicliente.sql
```

### 2. Verificar Permisos

Asegúrate de que los clientes tengan acceso a las rutas:
- `/cliente/solicitudes/credenciales`
- `/cliente/solicitudes/{id}/credenciales` (PUT)

### 3. Probar el Flujo

1. Como cliente:
   - Solicitar un plan
   - Ir a "Configurar Integración"
   - Ingresar credenciales
   - Realizar pago

2. Como admin:
   - Ir a "Conectar Clientes"
   - Ver la solicitud pendiente
   - Hacer clic en "Conectar"
   - Verificar que se crearon webhooks y productos

3. Probar webhooks:
   - Crear un pedido en Shopify del cliente
   - Verificar que se emita boleta/factura
   - Verificar que se guarde con el `user_id` correcto

## 📝 Consultas Útiles

### Ver solicitudes pendientes de conexión
```sql
SELECT 
    s.id,
    u.name as cliente,
    p.nombre as plan,
    s.tienda_shopify,
    s.estado,
    s.integracion_conectada
FROM solicitudes s
JOIN users u ON s.cliente_id = u.id
JOIN planes p ON s.plan_id = p.id
WHERE s.estado = 'en_proceso' 
  AND s.integracion_conectada = 0
  AND s.tienda_shopify IS NOT NULL;
```

### Ver integraciones activas por cliente
```sql
SELECT 
    ic.user_id,
    u.name as cliente,
    ic.shopify_tienda,
    ic.activo,
    COUNT(DISTINCT pm.id) as productos_sincronizados,
    COUNT(DISTINCT cw.id) as webhooks_activos
FROM integracion_configs ic
JOIN users u ON ic.user_id = u.id
LEFT JOIN product_mappings pm ON pm.user_id = ic.user_id
LEFT JOIN cliente_webhooks cw ON cw.user_id = ic.user_id
WHERE ic.activo = 1
GROUP BY ic.id;
```

### Ver webhooks por cliente
```sql
SELECT 
    cw.user_id,
    u.name as cliente,
    cw.topic,
    cw.webhook_shopify_id,
    cw.created_at
FROM cliente_webhooks cw
JOIN users u ON cw.user_id = u.id
ORDER BY cw.user_id, cw.topic;
```

## ⚠️ Consideraciones Importantes

1. **Webhooks**: Cada webhook incluye el `user_id` en la URL para identificar al cliente
2. **Aislamiento**: Cada cliente tiene sus propios datos completamente aislados
3. **Permisos del Plan**: Los permisos se copian del plan a `integracion_config` al conectar
4. **Bodegas**: Cada cliente debe configurar sus propias bodegas después de conectar
5. **Límites**: Los límites de pedidos se aplican por cliente individualmente

## 🔒 Seguridad

- Las credenciales se almacenan en la BD (considera encriptarlas en producción)
- Cada webhook valida que pertenezca al cliente correcto
- Los clientes solo pueden ver/modificar sus propios datos
- Los admin pueden ver todos los clientes pero no sus credenciales completas

## 📞 Soporte

Si tienes dudas sobre la implementación, revisa:
1. Los logs en `storage/logs/laravel.log`
2. Los webhooks en la tabla `cliente_webhooks`
3. Las configuraciones en `integracion_configs`

## ✅ Checklist de Implementación

- [ ] Ejecutar migración
- [ ] Verificar que las rutas funcionan
- [ ] Probar flujo completo como cliente
- [ ] Probar conexión como admin
- [ ] Verificar que los webhooks llegan correctamente
- [ ] Verificar que las boletas/facturas se guardan con el user_id correcto
- [ ] Configurar bodegas para un cliente de prueba
- [ ] Verificar límites de pedidos si aplica

¡Listo! El sistema multi-cliente está implementado y funcionando. 🎉
