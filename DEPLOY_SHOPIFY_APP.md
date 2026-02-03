# Desplegar Configuración a Shopify Partners

## 📋 Prerequisitos

1. **Instalar Shopify CLI:**
   ```bash
   npm install -g @shopify/cli @shopify/app
   ```

2. **Editar `shopify.app.toml`:**
   - Reemplaza `TU_CLIENT_ID_AQUI` con tu Client ID real de Shopify Partners

## 🚀 Comandos para Desplegar

### 1. Autenticarse con Shopify Partners
```bash
shopify auth login
```
Esto abrirá tu navegador para autenticarte.

### 2. Vincular el proyecto con tu App en Partners
```bash
shopify app config link
```
Selecciona tu app existente de la lista.

### 3. Desplegar la configuración
```bash
shopify app deploy
```

Este comando:
- ✅ Lee el `shopify.app.toml`
- ✅ Sube la configuración de webhooks a Partners
- ✅ **NO modifica tu código Laravel**
- ✅ **NO sube archivos** (solo configuración)

### 4. Verificar en Partners
Ve a: https://partners.shopify.com/ → Tu App → Versions

Deberías ver una nueva versión con los webhooks GDPR configurados.

## ⚠️ Importante

- El archivo `shopify.app.toml` es **solo para configuración**
- **NO afecta** tu lógica de Laravel
- **NO convierte** tu app en una app embebida
- Solo registra las URLs de webhooks en Shopify

## 🔧 Alternativa sin CLI

Si no quieres instalar Shopify CLI, puedes:

1. Crear una versión nueva en Partners manualmente
2. En la sección "Webhooks", agregar las URLs:
   - Customer data request: `https://shopify-integrator.lioren.cl/webhooks/customers/data_request`
   - Customer redact: `https://shopify-integrator.lioren.cl/webhooks/customers/redact`
   - Shop redact: `https://shopify-integrator.lioren.cl/webhooks/shop/redact`

## 📝 Notas

- El `shopify.app.toml` puede estar en `.gitignore` si quieres
- Solo necesitas desplegarlo una vez
- Después puedes eliminarlo de tu proyecto si quieres
