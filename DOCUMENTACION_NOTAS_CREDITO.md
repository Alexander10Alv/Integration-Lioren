# 🔄 Documentación: Notas de Crédito Automáticas

## 📋 Descripción General

Esta funcionalidad permite emitir **Notas de Crédito automáticas** en Lioren cuando un pedido es cancelado o reembolsado en Shopify. El sistema detecta estos eventos vía webhooks y emite el documento tributario correspondiente que anula la boleta o factura original.

---

## 🎯 ¿Cómo Funciona?

### Flujo Completo:

```
1. Cliente cancela pedido o solicita reembolso en Shopify
   ↓
2. Shopify dispara webhook (orders/cancelled o refunds/create)
   ↓
3. Sistema recibe el webhook en tu servidor
   ↓
4. Sistema busca la boleta/factura original emitida
   ↓
5. Sistema emite Nota de Crédito en Lioren (tipodoc: 61)
   ↓
6. Nota de Crédito referencia el documento original
   ↓
7. Sistema guarda el PDF y XML en la base de datos
   ↓
8. (Opcional) Actualiza nota en Shopify con el folio
```

---

## ⚙️ Configuración

### 1. Activar la Funcionalidad

En la página de integración (`/integracion`), marca el checkbox:

✅ **Notas de Crédito Automáticas**

Esto hará que el sistema:
- Cree webhooks para `orders/cancelled` y `refunds/create`
- Procese automáticamente cancelaciones y reembolsos
- Emita Notas de Crédito en Lioren

### 2. Webhooks Creados

Cuando activas esta opción, se crean 2 webhooks adicionales en Shopify:

| Webhook | Descripción |
|---------|-------------|
| `orders/cancelled` | Se dispara cuando un pedido es cancelado |
| `refunds/create` | Se dispara cuando se crea un reembolso |

---

## 📊 Estructura de la Nota de Crédito

### Datos Enviados a Lioren:

```json
{
  "emisor": {
    "tipodoc": "61",  // Nota de Crédito
    "fecha": "2025-12-31"
  },
  "receptor": {
    "rut": "12345678-9",
    "rs": "Cliente Name",
    "giro": "Comercio",
    "comuna": 13101,
    "ciudad": 131,
    "direccion": "Sin dirección"
  },
  "detalles": [
    {
      "nombre": "Devolución por cancelación/reembolso",
      "cantidad": 1,
      "precio": 10000,  // Precio NETO (sin IVA)
      "exento": false
    }
  ],
  "referencias": [
    {
      "fecha": "2025-12-30",
      "tipodoc": "39",  // 39=Boleta, 33=Factura
      "folio": "987654",  // Folio del documento original
      "razon": 1,  // Anula documento de referencia
      "glosa": "Anula documento por cancelación de pedido"
    }
  ],
  "expects": "all"
}
```

### Campos Importantes:

- **tipodoc: "61"** → Identifica que es una Nota de Crédito
- **referencias** → Array que vincula con el documento original
- **razon: 1** → Código SII que indica "Anula documento de referencia"
- **folio** → Número del documento original (boleta o factura)

---

## 🗄️ Base de Datos

### Tabla: `notas_credito`

```sql
CREATE TABLE `notas_credito` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shopify_order_id` varchar(255) NOT NULL,
  `shopify_order_number` varchar(255) NOT NULL,
  `tipo_documento_original` varchar(255) NOT NULL,  -- 33=Factura, 39=Boleta
  `folio_original` int(11) NOT NULL,  -- Folio del doc original
  `lioren_nota_id` int(11) DEFAULT NULL,
  `folio` int(11) DEFAULT NULL,  -- Folio de la NC
  `rut_receptor` varchar(255) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `monto_neto` decimal(10,2) DEFAULT 0.00,
  `monto_iva` decimal(10,2) DEFAULT 0.00,
  `monto_total` decimal(10,2) DEFAULT 0.00,
  `pdf_base64` longtext DEFAULT NULL,
  `xml_base64` longtext DEFAULT NULL,
  `status` varchar(255) DEFAULT 'pending',  -- pending, emitida, error
  `glosa` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `emitida_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

---

## 🔍 Visualización

### Ver Notas de Crédito Emitidas

Accede a: `/notas-credito`

Desde ahí puedes:
- Ver todas las notas de crédito emitidas
- Descargar el PDF de cada nota
- Descargar el XML de cada nota
- Ver errores si hubo problemas

---

## 📝 Ejemplo de Uso

### Escenario 1: Cliente Cancela Pedido

1. Cliente realiza pedido #1001 en Shopify
2. Sistema emite Boleta #987654 en Lioren
3. Cliente cancela el pedido
4. Shopify dispara webhook `orders/cancelled`
5. Sistema busca Boleta #987654
6. Sistema emite Nota de Crédito #123456 que anula Boleta #987654
7. Nota de Crédito queda guardada en BD con PDF y XML

### Escenario 2: Cliente Solicita Reembolso

1. Cliente realiza pedido #1002 en Shopify
2. Sistema emite Factura #555555 en Lioren
3. Cliente solicita reembolso
4. Shopify dispara webhook `refunds/create`
5. Sistema busca Factura #555555
6. Sistema emite Nota de Crédito #123457 que anula Factura #555555
7. Nota de Crédito queda guardada en BD con PDF y XML

---

## ⚠️ Consideraciones Importantes

### 1. Solo se Emiten NC para Documentos Existentes

El sistema **solo emitirá Notas de Crédito** si encuentra una boleta o factura previamente emitida para ese pedido. Si no existe documento original, no se emite NC.

### 2. Búsqueda de Documento Original

El sistema busca el documento original de dos formas:

**Para Facturas:**
```php
$factura = FacturaEmitida::where('shopify_order_id', $orderId)
    ->where('status', 'emitida')
    ->first();
```

**Para Boletas:**
```php
$boleta = Boleta::where('observaciones', 'LIKE', "%Shopify #{$orderNumber}%")
    ->where('status', 'emitida')
    ->first();
```

### 3. Cálculo de Montos

El sistema calcula automáticamente el monto neto (sin IVA):

```php
$montoNeto = round($montoTotal / 1.19, 2);
```

### 4. Visibilidad en Shopify

Si tienes activada la opción "Visibilidad desde Shopify", el sistema actualizará las notas del pedido con:

```
"Nota de Crédito Lioren #123456"
```

---

## 🔧 Endpoints de la API

### Listar Notas de Crédito
```
GET /notas-credito
```

### Descargar PDF
```
GET /notas-credito/{id}/pdf
```

### Descargar XML
```
GET /notas-credito/{id}/xml
```

---

## 📊 Códigos de Tipo de Documento (SII)

| Código | Descripción |
|--------|-------------|
| 33 | Factura Electrónica |
| 39 | Boleta Electrónica |
| 61 | Nota de Crédito Electrónica |

---

## 🚀 Ventajas

✅ **Automatización Total** - No necesitas emitir NC manualmente  
✅ **Cumplimiento SII** - Documentos tributarios válidos  
✅ **Trazabilidad** - Cada NC referencia el documento original  
✅ **Almacenamiento** - PDF y XML guardados en BD  
✅ **Visibilidad** - Integración con Shopify  

---

## 📞 Soporte

Si tienes dudas sobre esta funcionalidad, revisa:
- Logs del sistema en `storage/logs/laravel.log`
- Tabla `notas_credito` en la base de datos
- Webhooks en Shopify Admin

---

## 🔗 Referencias

- [Documentación API Lioren](https://www.lioren.cl/docs)
- [Webhooks de Shopify](https://shopify.dev/docs/api/admin-rest/2025-10/resources/webhook)
- [Normativa SII Chile](https://www.sii.cl)

---

**Última actualización:** 31 de Diciembre, 2025
