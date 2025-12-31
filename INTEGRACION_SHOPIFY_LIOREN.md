# 🔗 Sistema de Integración Shopify - Lioren

## 📍 Ubicación

El módulo de integración se encuentra en: `/prueba_integracion/`

## 🚀 Acceso Rápido

**Dashboard Principal:** `http://localhost/prueba_integracion/dashboard.php`

## 📋 Archivos Creados

### Archivos Principales
- ✅ `dashboard.php` - Dashboard principal con navegación
- ✅ `index.php` - Formulario de configuración de integración
- ✅ `procesar_integracion.php` - Procesador de integración automática
- ✅ `webhook_receiver.php` - Receptor de eventos de Shopify
- ✅ `funciones.php` - Biblioteca de funciones auxiliares

### Herramientas de Utilidad
- ✅ `test_conexion.php` - Pruebas de conexión con APIs
- ✅ `ver_logs.php` - Visor de logs en tiempo real
- ✅ `install.php` - Verificador de requisitos del sistema
- ✅ `ejemplo_uso.php` - Ejemplos de uso de las funciones

### Documentación
- ✅ `README.md` - Documentación completa del sistema
- ✅ `INSTALACION.txt` - Guía rápida de instalación
- ✅ `config_ejemplo.php` - Plantilla de configuración

### Configuración
- ✅ `.gitignore` - Protección de archivos sensibles
- ✅ `logs/.htaccess` - Protección de logs
- ✅ `logs/index.php` - Prevención de acceso directo

## 🎯 Características Implementadas

### ✅ Validación Automática
- Validación de credenciales de Shopify
- Validación de credenciales de Lioren
- Verificación de permisos y requisitos

### ✅ Webhooks Automáticos
El sistema crea automáticamente 4 webhooks en Shopify:
1. **orders/create** - Sincroniza nuevos pedidos
2. **products/create** - Sincroniza productos nuevos
3. **products/update** - Sincroniza actualizaciones de productos
4. **inventory_levels/update** - Sincroniza cambios de inventario

### ✅ Sincronización Inicial
- Obtiene productos de Shopify (hasta 10 en prueba)
- Mapea datos al formato de Lioren
- Crea productos en Lioren automáticamente

### ✅ Sincronización en Tiempo Real
- Recibe eventos de Shopify vía webhooks
- Valida autenticidad con HMAC
- Procesa y envía a Lioren automáticamente
- Registra todo en logs detallados

### ✅ Sistema de Logs
- Logs de integración inicial
- Logs de webhooks por día
- Datos completos en JSON para debugging
- Visor web de logs en tiempo real

## 🔧 Instalación

### Requisitos
- PHP 7.4+
- Extensión cURL
- Extensión JSON
- Servidor web (Apache/Nginx)

### Pasos

1. **Los archivos ya están creados** en `/prueba_integracion/`

2. **Verificar el sistema:**
   ```
   http://localhost/prueba_integracion/install.php
   ```

3. **Acceder al dashboard:**
   ```
   http://localhost/prueba_integracion/dashboard.php
   ```

## 🔑 Credenciales Necesarias

### Shopify
- **Nombre de tienda:** `tu-tienda.myshopify.com`
- **Access Token:** Obtener desde Settings > Apps > Develop apps
- **API Secret:** Para validación de webhooks

### Lioren
- **API Key:** Obtener desde tu panel de Lioren

## 🌐 Webhooks en Desarrollo Local

Para recibir webhooks en localhost, usa **ngrok**:

```bash
ngrok http 80
```

Luego usa la URL proporcionada:
```
https://abc123.ngrok.io/prueba_integracion/webhook_receiver.php
```

## 📊 Mapeo de Datos

### Productos (Shopify → Lioren)
```
product.title → name
product.variants[0].price → price
product.variants[0].sku → sku
product.variants[0].inventory_quantity → stock
product.body_html → description
product.id → external_id
```

### Pedidos (Shopify → Lioren)
```
order.id → external_id
order.line_items → items
order.total_price → total
order.customer → customer_data
```

## 🔄 Flujo de Trabajo

1. **Configuración Inicial**
   - Acceder a `dashboard.php`
   - Ir a "Configurar Integración"
   - Ingresar credenciales
   - Sistema valida y configura todo automáticamente

2. **Sincronización Automática**
   - Shopify envía eventos a webhook_receiver.php
   - Sistema valida y procesa eventos
   - Datos se sincronizan a Lioren
   - Todo se registra en logs

3. **Monitoreo**
   - Ver logs en tiempo real desde `ver_logs.php`
   - Revisar archivos en `/logs/`

## 📁 Estructura de Logs

```
logs/
├── integracion.log              # Log de configuración inicial
├── webhook_2024-11-17.log       # Logs de webhooks por día
├── webhook_data_*.json          # Datos completos de webhooks
└── ejemplo.log                  # Logs de pruebas
```

## 🧪 Testing

### Probar Conexiones
```
http://localhost/prueba_integracion/test_conexion.php
```

### Ver Ejemplos de Código
```
http://localhost/prueba_integracion/ejemplo_uso.php
```

### Simular Webhook (cURL)
```bash
curl -X POST http://localhost/prueba_integracion/webhook_receiver.php?evento=order_create \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Topic: orders/create" \
  -d '{"id": 123, "order_number": 1001, "total_price": "99.99"}'
```

## 🔒 Seguridad

- ✅ Validación HMAC de webhooks
- ✅ Tokens en sesión (no en archivos)
- ✅ Logs protegidos con .htaccess
- ✅ Validación de entrada en formularios
- ✅ .gitignore para archivos sensibles

## ⚠️ Importante

Este es un **módulo de PRUEBA**. Para producción considera:
- Sistema de autenticación robusto
- Base de datos para mapeo de IDs
- Cola de trabajos para sincronización
- Manejo de errores más completo
- Reintentos automáticos
- Monitoreo y alertas
- Tests automatizados

## 📚 Documentación Completa

Ver: `/prueba_integracion/README.md`

## 🎉 ¡Listo para Usar!

El sistema está completamente implementado y listo para pruebas. Solo necesitas:
1. Obtener tus credenciales
2. Acceder al dashboard
3. Configurar la integración

---

**Sistema creado:** 17 de Noviembre, 2024  
**Versión:** 1.0  
**Tipo:** Módulo de Prueba
