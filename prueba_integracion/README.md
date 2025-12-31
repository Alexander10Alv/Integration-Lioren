# 🔗 Sistema de Integración Shopify - Lioren

Módulo de prueba para integración automática entre Shopify y Lioren.

## 📋 Características

- ✅ Validación automática de credenciales
- ✅ Creación automática de webhooks en Shopify
- ✅ Sincronización inicial de productos
- ✅ Sincronización en tiempo real de:
  - Nuevos pedidos
  - Productos creados/actualizados
  - Cambios de inventario
- ✅ Sistema de logs detallado
- ✅ Interfaz web simple y clara

## 🚀 Instalación

### Requisitos

- PHP 7.4 o superior
- Extensión cURL habilitada
- Servidor web (Apache/Nginx)
- Acceso a internet

### Pasos

1. **Clonar o descargar** este directorio en tu servidor web

2. **Crear carpeta de logs**
   ```bash
   mkdir logs
   chmod 755 logs
   ```

3. **Configurar credenciales** (opcional)
   ```bash
   cp config_ejemplo.php config.php
   # Editar config.php con tus credenciales
   ```

4. **Acceder al formulario**
   ```
   http://tudominio.com/prueba_integracion/
   ```

## 🔑 Obtener Credenciales

### Shopify

1. Inicia sesión en tu tienda Shopify
2. Ve a: **Settings > Apps and sales channels > Develop apps**
3. Crea una nueva app o selecciona una existente
4. Configura los permisos necesarios:
   - `read_products`, `write_products`
   - `read_orders`, `write_orders`
   - `read_inventory`, `write_inventory`
5. En **API credentials**, copia:
   - Admin API access token
   - API secret key

### Lioren

1. Inicia sesión en tu cuenta de Lioren
2. Ve a la sección de **API** o **Configuración**
3. Genera o copia tu API Key
4. Guárdala de forma segura

## 🌐 Configurar Webhooks (Desarrollo Local)

Si estás probando en localhost, necesitas exponer tu servidor:

### Opción 1: ngrok (Recomendado)

```bash
# Instalar ngrok desde https://ngrok.com/
ngrok http 80

# Usar la URL proporcionada:
# https://abc123.ngrok.io/prueba_integracion/webhook_receiver.php
```

### Opción 2: localtunnel

```bash
npm install -g localtunnel
lt --port 80

# Usar la URL proporcionada
```

## 📁 Estructura de Archivos

```
prueba_integracion/
├── index.php                    # Formulario de configuración
├── procesar_integracion.php     # Lógica de integración
├── webhook_receiver.php         # Receptor de webhooks
├── funciones.php                # Funciones auxiliares
├── config_ejemplo.php           # Ejemplo de configuración
├── README.md                    # Este archivo
└── logs/                        # Logs del sistema
    ├── integracion.log
    ├── webhook_YYYY-MM-DD.log
    └── webhook_data_*.json
```

## 🔄 Flujo de Integración

1. **Usuario completa formulario** con credenciales
2. **Sistema valida** conexión con Shopify y Lioren
3. **Sistema crea** 4 webhooks automáticamente:
   - `orders/create` - Nuevos pedidos
   - `products/create` - Productos creados
   - `products/update` - Productos actualizados
   - `inventory_levels/update` - Inventario actualizado
4. **Sistema sincroniza** productos iniciales (hasta 10)
5. **Webhooks activos** - Sincronización automática en tiempo real

## 📊 Mapeo de Datos

### Productos (Shopify → Lioren)

| Shopify | Lioren |
|---------|--------|
| `product.title` | `name` |
| `product.variants[0].price` | `price` |
| `product.variants[0].sku` | `sku` |
| `product.variants[0].inventory_quantity` | `stock` |
| `product.body_html` | `description` |
| `product.id` | `external_id` |

### Pedidos (Shopify → Lioren)

| Shopify | Lioren |
|---------|--------|
| `order.id` | `external_id` |
| `order.line_items` | `items` |
| `order.total_price` | `total` |
| `order.customer` | `customer_data` |

## 🐛 Debugging

### Ver logs

```bash
# Log de integración
tail -f logs/integracion.log

# Logs de webhooks
tail -f logs/webhook_$(date +%Y-%m-%d).log

# Ver datos completos de webhooks
cat logs/webhook_data_*.json | jq
```

### Problemas comunes

**Error: "Credenciales de Shopify inválidas"**
- Verifica que el Access Token sea correcto
- Asegúrate de que la app tenga los permisos necesarios
- Confirma que el nombre de tienda incluya `.myshopify.com`

**Error: "API Key de Lioren inválida"**
- Verifica que el API Key sea correcto
- Confirma que tu cuenta de Lioren tenga acceso a la API
- Revisa que no haya espacios al inicio o final del token

**Webhooks no se reciben**
- Verifica que la URL sea accesible públicamente
- Confirma que el servidor esté corriendo
- Revisa los logs de Shopify en: Settings > Notifications > Webhooks

## 🔒 Seguridad

- ✅ Validación HMAC de webhooks
- ✅ Tokens almacenados en sesión (no en archivos)
- ✅ Logs no exponen información sensible
- ✅ Validación de entrada en formularios

### Recomendaciones

- No uses este módulo en producción sin revisión de seguridad
- Implementa autenticación adicional si es necesario
- Mantén PHP y dependencias actualizadas
- Usa HTTPS en producción

## 📝 Logs

El sistema genera logs detallados:

- `integracion.log` - Proceso de configuración inicial
- `webhook_YYYY-MM-DD.log` - Eventos de webhooks por día
- `webhook_data_*.json` - Datos completos de cada webhook (debugging)

## 🧪 Pruebas

### Probar webhooks manualmente

```bash
# Simular webhook de nuevo pedido
curl -X POST http://tudominio.com/prueba_integracion/webhook_receiver.php?evento=order_create \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Topic: orders/create" \
  -H "X-Shopify-Shop-Domain: tu-tienda.myshopify.com" \
  -d '{"id": 123, "order_number": 1001, "total_price": "99.99"}'
```

## 📚 Documentación de APIs

- [Shopify Admin REST API](https://shopify.dev/docs/api/admin-rest)
- [Shopify Webhooks](https://shopify.dev/docs/api/admin-rest/2024-01/resources/webhook)
- [Lioren API](https://www.lioren.cl/docs)

## ⚠️ Limitaciones

- Este es un módulo de **PRUEBA**, no está optimizado para producción
- No incluye sistema de usuarios ni autenticación avanzada
- Sincronización inicial limitada a 10 productos
- No maneja paginación de productos
- No incluye reintentos automáticos en caso de error

## 🤝 Soporte

Para problemas o preguntas:
1. Revisa los logs en `/logs/`
2. Verifica la documentación de las APIs
3. Confirma que las credenciales sean correctas

## 📄 Licencia

Este es un módulo de prueba para fines educativos y de testing.

---

**Nota:** Este módulo está diseñado para pruebas y desarrollo. Para uso en producción, considera implementar:
- Sistema de autenticación robusto
- Manejo de errores más completo
- Cola de trabajos para sincronización
- Base de datos para mapeo de IDs
- Monitoreo y alertas
- Tests automatizados
