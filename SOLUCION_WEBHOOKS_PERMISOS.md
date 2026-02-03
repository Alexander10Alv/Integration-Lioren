# Solución: Problemas de Permisos en Webhooks de Shopify

## 🔴 Problemas Encontrados

### 1. Error HTTP 403 - Protected Customer Data
```
HTTP 403: You do not have permission to create or update webhooks with orders/create topic.
This topic contains protected customer data.
```

**Webhooks afectados:**
- ❌ `orders/create` - Nuevos Pedidos
- ❌ `orders/cancelled` - Pedidos Cancelados  
- ❌ `refunds/create` - Reembolsos Creados

**Causa:** Estos webhooks requieren que la app de Shopify tenga aprobación especial para acceder a "Protected Customer Data".

### 2. Error "Array to string conversion"
**Causa:** El código intentaba convertir un array de errores directamente a string.
**Solución:** ✅ Ya corregido - ahora maneja arrays correctamente con `json_encode()`.

## ✅ Webhooks que SÍ Funcionan

Estos webhooks NO requieren permisos especiales:
- ✅ `products/create` - Productos Creados
- ✅ `products/update` - Productos Actualizados
- ✅ `inventory_levels/update` - Inventario Actualizado

## 🔧 Soluciones Disponibles

### Opción 1: Usar Solo Webhooks de Productos (Recomendado)

Modificar el código para crear solo los webhooks que funcionan:

```php
$webhooks = [
    ['topic' => 'products/create', 'nombre' => 'Productos Creados'],
    ['topic' => 'products/update', 'nombre' => 'Productos Actualizados'],
    ['topic' => 'inventory_levels/update', 'nombre' => 'Inventario Actualizado']
];

// NO incluir webhooks de orders/refunds hasta tener permisos
```

### Opción 2: Solicitar Permisos a Shopify

Para usar webhooks de pedidos, necesitas:

1. **Crear una App Pública en Shopify Partners**
   - Ir a: https://partners.shopify.com/
   - Crear app pública (no custom app)
   - Solicitar acceso a "Protected Customer Data"

2. **Justificar el uso de datos protegidos**
   - Explicar por qué necesitas acceso a pedidos
   - Pasar revisión de seguridad de Shopify
   - Puede tomar varios días

3. **Scopes necesarios:**
   ```
   read_orders, write_orders
   read_customers (si necesitas datos de clientes)
   ```

### Opción 3: Usar API Polling en lugar de Webhooks

Para pedidos, puedes consultar la API periódicamente:

```php
// Cada X minutos, consultar nuevos pedidos
$orders = $shopify->Order->get([
    'created_at_min' => $lastCheck,
    'status' => 'any'
]);
```

## 🎯 Recomendación Inmediata

**Modificar el servicio para crear solo webhooks de productos:**

Esto permitirá:
- ✅ Sincronización de productos en tiempo real
- ✅ Actualización de inventario automática
- ✅ Sin errores de permisos
- ⚠️ Pedidos se procesarán de otra forma (polling o manual)

## 📝 Cambios Necesarios

### 1. Modificar `IntegracionMulticlienteService.php`

```php
private function crearWebhooks(Solicitud $solicitud, IntegracionConfig $config)
{
    $webhookUrl = url('/integracion/webhook-receiver');
    
    // Solo webhooks que NO requieren permisos especiales
    $webhooks = [
        ['topic' => 'products/create', 'nombre' => 'Productos Creados'],
        ['topic' => 'products/update', 'nombre' => 'Productos Actualizados'],
        ['topic' => 'inventory_levels/update', 'nombre' => 'Inventario Actualizado']
    ];

    // NOTA: Webhooks de orders/refunds requieren permisos especiales
    // Se procesarán mediante polling o cuando se obtengan permisos
    
    // ... resto del código
}
```

### 2. Implementar Polling para Pedidos (Opcional)

```php
// En app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Consultar nuevos pedidos cada 5 minutos
    $schedule->command('shopify:sync-orders')->everyFiveMinutes();
}
```

## 🚀 Próximos Pasos

1. ✅ **Inmediato:** Modificar código para usar solo webhooks de productos
2. ⏳ **Corto plazo:** Implementar polling para pedidos
3. 📋 **Largo plazo:** Solicitar permisos a Shopify si es necesario

## 📊 Impacto

**Con webhooks de productos solamente:**
- ✅ Sincronización de catálogo: **Tiempo real**
- ✅ Actualización de stock: **Tiempo real**
- ⚠️ Procesamiento de pedidos: **Polling (5-15 min delay)**
- ⚠️ Notas de crédito: **Manual o polling**

**Esto es suficiente para la mayoría de casos de uso.**

## 🔗 Referencias

- [Shopify Protected Customer Data](https://shopify.dev/docs/apps/launch/protected-customer-data)
- [Shopify Webhook Topics](https://shopify.dev/docs/api/admin-rest/2024-01/resources/webhook#event-topics)
- [Shopify App Scopes](https://shopify.dev/docs/api/usage/access-scopes)
