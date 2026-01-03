# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Dashboard con métricas en tiempo real
- Notificaciones in-app para locations nuevas
- Sincronización nocturna de respaldo (2 AM)
- Soporte para WooCommerce y Mercado Libre
- API REST para integraciones externas
- Jobs en background con Laravel Queues
- Cache de productos para mejor performance
- Webhooks bidireccionales (Lioren → Shopify)
- Compresión de logs antiguos
- Backup automático de configuraciones

---

## [2.1.0] - 2025-12-31

### 🔄 Nueva Funcionalidad - Notas de Crédito Automáticas

#### Added
- **Sistema de Notas de Crédito Automáticas**
  - Checkbox en configuración de integración para activar/desactivar
  - Webhooks automáticos para `orders/cancelled` y `refunds/create`
  - Emisión automática de Notas de Crédito (tipodoc: 61) en Lioren
  - Búsqueda automática del documento original (boleta o factura)
  - Referencia correcta al documento original según normativa SII
  
- **Modelo y Migración**
  - Modelo `NotaCredito` con todos los campos necesarios
  - Tabla `notas_credito` en base de datos
  - Campo `notas_credito_enabled` en `integracion_configs`
  - Almacenamiento de PDF y XML en base64

- **Controlador y Rutas**
  - Métodos `procesarCancelacion()` y `procesarReembolso()`
  - Método `emitirNotaCredito()` para emisión en Lioren
  - Rutas para listar, ver PDF y descargar XML
  - Integración con webhook receiver existente

- **Vistas**
  - Vista de listado de Notas de Crédito (`/notas-credito`)
  - Tarjeta en dashboard de integración
  - Descarga de PDF y XML desde la interfaz
  - Visualización de errores si la emisión falla

- **Documentación**
  - Archivo `DOCUMENTACION_NOTAS_CREDITO.md` completo
  - Actualización de `database_facturacion.sql`
  - Ejemplos de uso y flujo completo

#### Technical Details
- Cálculo automático de monto neto (sin IVA)
- Validación de existencia de documento original
- Actualización opcional de notas en Shopify
- Manejo de errores y logging detallado
- Soporte para cancelaciones y reembolsos

---

## [2.0.0] - 2024-12-11

### 🎉 Major Release - Sistema de Roles y Gestión de Bodegas

#### Added
- **Sistema de Roles Completo**
  - Middleware `CheckRole` para protección de rutas
  - Rol `admin` con acceso completo al sistema
  - Rol `cliente` con dashboard personalizado
  - Redirección automática según rol después del login
  
- **Dashboard de Cliente**
  - Vista personalizada para clientes (`/cliente/dashboard`)
  - Navbar específica para clientes
  - Sección de pedidos (`/cliente/pedidos`)
  - Sección de facturas (`/cliente/facturas`)
  - Diseño responsive con Tailwind CSS

- **Gestión de Bodegas/Locations**
  - Controlador `WarehouseConfigController` completo
  - Modo Simple: Una bodega para todo el inventario
  - Modo Avanzado: Mapeo manual de locations → bodegas
  - Vista de configuración de bodegas (`/warehouse-config`)
  - Detección automática de nuevas locations

- **Modelos de Warehouse**
  - `WarehouseMapping` - Configuración de modo de sincronización
  - `LocationBodegaMapping` - Mapeo location → bodega
  - `PendingLocationMapping` - Locations sin mapear detectadas

- **Comandos Artisan**
  - `sync:detect-locations` - Detecta nuevas locations sin mapear
  - Programación automática cada 6 horas

- **Servicio de Inventario**
  - `InventorySyncService` completo
  - Sincronización inteligente de stock
  - Soporte para múltiples bodegas
  - Bodega fallback para locations no mapeadas

#### Changed
- **Rutas Protegidas por Rol**
  - Rutas de admin protegidas con `role:admin`
  - Rutas de cliente protegidas con `role:cliente`
  - Webhook receiver sin autenticación (público)

- **Layout Principal**
  - Navbar dinámica según rol del usuario
  - Menú de admin: Dashboard, Integración, Bodegas, Boletas
  - Menú de cliente: Dashboard, Pedidos, Facturas

- **Base de Datos**
  - Agregada columna `role` a tabla `users`
  - Nuevas tablas: `warehouse_mappings`, `location_bodega_mappings`, `pending_location_mappings`

#### Fixed
- Protección de rutas administrativas
- Separación de permisos entre admin y cliente
- Navegación según rol del usuario

---

## [1.5.0] - 2024-12-10

### 🚀 Sistema de Webhooks y Cola de Reintentos

#### Added
- **Sistema de Webhooks en Tiempo Real**
  - Servicio `WebhookSyncService` completo
  - Procesamiento de eventos de Shopify
  - Soporte para 5 tipos de webhooks:
    - `orders/create` - Emisión de facturas/boletas
    - `products/create` - Crear producto en Lioren
    - `products/update` - Actualizar producto en Lioren
    - `products/delete` - Eliminar producto en Lioren
    - `inventory_levels/update` - Sincronizar inventario

- **Cola de Reintentos**
  - Modelo `SyncQueue` para trabajos pendientes
  - Sistema de reintentos automáticos (3 intentos)
  - Delay incremental entre reintentos
  - Comando `sync:process-queue` para procesar cola
  - Programación automática cada 5 minutos

- **Logs de Sincronización**
  - Modelo `SyncLog` para auditoría completa
  - Registro de éxitos y errores
  - Información detallada de cada operación
  - Métodos helper para consultas rápidas

#### Changed
- **Endpoint de Webhooks**
  - Ruta `/integracion/webhook-receiver` mejorada
  - Validación de firma HMAC de Shopify
  - Procesamiento asíncrono con cola
  - Respuesta inmediata (200 OK)

- **Procesamiento de Eventos**
  - Ejecución inmediata + cola de respaldo
  - Tolerancia a fallos mejorada
  - Logs detallados de cada operación

#### Fixed
- Manejo de errores en webhooks
- Reintentos automáticos ante fallos
- Pérdida de eventos durante caídas del sistema

---

## [1.0.0] - 2024-12-08

### 🎊 Release Inicial - Sincronización Bidireccional

#### Added
- **Sincronización Bidireccional de Productos**
  - Servicio `ProductSyncService` completo
  - PASO 1: Shopify → Lioren (Shopify como fuente de verdad)
  - PASO 2: Lioren → Shopify (Productos nuevos de Lioren)
  - Identificación por SKU común
  - Mapeo automático de productos

- **Modelo de Mapeo**
  - `ProductMapping` para relacionar productos
  - Campos: `shopify_product_id`, `shopify_variant_id`, `lioren_product_id`, `sku`
  - Métodos helper: `findBySku()`, `findByShopifyId()`, `findByLiorenId()`
  - Estados de sincronización: `synced`, `pending`, `error`

- **Controlador de Integración**
  - `IntegracionController` completo
  - Vista de configuración inicial
  - Procesamiento de credenciales
  - Validación de APIs
  - Creación automática de webhooks

- **Configuración de Integración**
  - Modelo `IntegracionConfig`
  - Campos: `shopify_tienda`, `shopify_token`, `lioren_api_key`
  - Soporte para múltiples usuarios
  - Activación/desactivación de facturación

- **Vistas de Admin**
  - `integracion/index.blade.php` - Configuración inicial
  - `integracion/procesar.blade.php` - Resultado de configuración
  - `integracion/dashboard.blade.php` - Dashboard principal
  - `integracion/resetear.blade.php` - Resetear integración

#### Changed
- **Paginación de Shopify**
  - Migrado de paginación por página (deprecated) a cursor-based
  - Soporte para grandes catálogos de productos
  - Mejor performance en sincronizaciones

- **Límite de Ejecución**
  - Aumentado a 300 segundos (5 minutos)
  - Configurado en `.env` y `public/index.php`
  - Soporte para sincronizaciones de catálogos grandes

#### Fixed
- Timeout en sincronizaciones grandes
- Duplicación de productos
- Conflictos de SKU

---

## [0.5.0] - 2024-12-07

### 📄 Sistema de Facturación Automática

#### Added
- **Emisión Automática de Documentos**
  - Integración con API de Lioren para facturación
  - Soporte para facturas y boletas
  - Validación de RUT chileno
  - Almacenamiento de PDF y XML

- **Modelo de Facturas**
  - `FacturaEmitida` para registro de documentos
  - Campos: `shopify_order_id`, `tipo_documento`, `folio`, `rut_receptor`
  - Almacenamiento de PDF/XML en base64
  - Estados: `emitida`, `error`, `pendiente`

- **Controlador de Boletas**
  - `BoletaController` para gestión de documentos
  - Vista de listado de boletas
  - Descarga de PDF
  - Reenvío de documentos

#### Changed
- **Formato de RUT**
  - Corrección en limpieza de RUT
  - Mantener guión para dígito verificador
  - Formato requerido por Lioren: `12345678-9`

#### Fixed
- Error de validación de RUT en Lioren
- Formato incorrecto de RUT (sin guión)
- Emisión de facturas con datos incompletos

---

## [0.3.0] - 2024-12-06

### 🗄️ Estructura de Base de Datos

#### Added
- **Migración Principal**
  - Tabla `integracion_configs` para configuración
  - Tabla `product_mappings` para mapeo de productos
  - Tabla `sync_logs` para logs de sincronización
  - Tabla `sync_queue` para cola de trabajos
  - Tabla `facturas_emitidas` para documentos tributarios

- **Columna de Facturación**
  - Campo `facturacion_enabled` en `integracion_configs`
  - Activación/desactivación de emisión automática

#### Changed
- Estructura de tablas optimizada
- Índices para mejor performance
- Relaciones entre tablas definidas

#### Fixed
- Error de columna faltante `facturacion_enabled`
- Migraciones en orden correcto

---

## [0.2.0] - 2024-12-05

### 🔧 Configuración Inicial

#### Added
- **Proyecto Laravel**
  - Laravel 9.x instalado
  - Configuración de base de datos MySQL
  - Autenticación con Laravel Breeze
  - Tailwind CSS configurado

- **Modelos Base**
  - `User` con autenticación
  - `IntegracionConfig` para configuración
  - Relaciones entre modelos

- **Rutas Iniciales**
  - Rutas de autenticación
  - Rutas de integración
  - Rutas de dashboard

#### Changed
- Configuración de `.env` para desarrollo
- Configuración de base de datos

---

## [0.1.0] - 2024-12-04

### 🎬 Inicio del Proyecto

#### Added
- Repositorio inicial creado
- Estructura de carpetas Laravel
- Archivo `.gitignore` configurado
- Documentación inicial

---

## Tipos de Cambios

- `Added` - Nuevas funcionalidades
- `Changed` - Cambios en funcionalidades existentes
- `Deprecated` - Funcionalidades que serán removidas
- `Removed` - Funcionalidades removidas
- `Fixed` - Corrección de bugs
- `Security` - Correcciones de seguridad

---

## Versionado

Este proyecto usa [Semantic Versioning](https://semver.org/):

- **MAJOR** (X.0.0) - Cambios incompatibles con versiones anteriores
- **MINOR** (0.X.0) - Nuevas funcionalidades compatibles
- **PATCH** (0.0.X) - Correcciones de bugs compatibles

---

## Enlaces

- [Repositorio](https://github.com/tu-usuario/shopify-lioren-integrator)
- [Issues](https://github.com/tu-usuario/shopify-lioren-integrator/issues)
- [Pull Requests](https://github.com/tu-usuario/shopify-lioren-integrator/pulls)
- [Documentación](README.md)

---

**Última actualización:** 11 de Diciembre, 2024
