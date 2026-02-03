# Webhooks GDPR Obligatorios de Shopify

## 📋 URLs para Configurar en Shopify Partners

Cuando configures tu App Pública en Shopify Partners, debes agregar estas 3 URLs en la sección de **"GDPR mandatory webhooks"**:

### 1. Customer Data Request
```
https://tudominio.com/webhooks/customers/data_request
```
**Propósito:** Shopify solicita que proporciones todos los datos que tienes del cliente.

---

### 2. Customer Redact
```
https://tudominio.com/webhooks/customers/redact
```
**Propósito:** Shopify solicita que elimines todos los datos del cliente.

---

### 3. Shop Redact
```
https://tudominio.com/webhooks/shop/redact
```
**Propósito:** Shopify solicita que elimines todos los datos de la tienda.

---

## 🔒 Seguridad

Todos los webhooks:
- ✅ Verifican firma HMAC SHA256 usando tu `Client Secret`
- ✅ Responden HTTP 200 inmediatamente
- ✅ Registran todas las solicitudes en logs
- ✅ Son rutas públicas (no requieren autenticación)

## 📝 Logs

Los webhooks registran en `storage/logs/laravel.log`:
- Solicitudes recibidas
- Datos del cliente/tienda
- Intentos de firma inválida

## ⚠️ Implementación Pendiente

Los webhooks actualmente:
- ✅ Reciben y validan las peticiones
- ✅ Responden HTTP 200
- ✅ Registran en logs

**TODO (para cumplimiento completo):**
- [ ] Implementar recopilación de datos del cliente
- [ ] Implementar eliminación de datos del cliente
- [ ] Implementar eliminación de datos de la tienda
- [ ] Enviar datos al email del cliente (data_request)

## 🧪 Probar Webhooks

Puedes probar los webhooks con curl:

```bash
# Test Customer Data Request
curl -X POST https://tudominio.com/webhooks/customers/data_request \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Hmac-Sha256: [HMAC_CALCULADO]" \
  -d '{"shop_domain":"test.myshopify.com","customer":{"id":123,"email":"test@example.com"}}'

# Test Customer Redact
curl -X POST https://tudominio.com/webhooks/customers/redact \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Hmac-Sha256: [HMAC_CALCULADO]" \
  -d '{"shop_domain":"test.myshopify.com","customer":{"id":123,"email":"test@example.com"}}'

# Test Shop Redact
curl -X POST https://tudominio.com/webhooks/shop/redact \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Hmac-Sha256: [HMAC_CALCULADO]" \
  -d '{"shop_domain":"test.myshopify.com","shop_id":456}'
```

## 📚 Referencias

- [Shopify GDPR Webhooks](https://shopify.dev/docs/apps/build/privacy-law-compliance)
- [Mandatory Webhooks](https://shopify.dev/docs/apps/build/privacy-law-compliance/mandatory-webhooks)
