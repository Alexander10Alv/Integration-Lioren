# Documentación del Sistema de Webhooks - Shopify + Lioren

## Resumen de tu Implementación Actual

Tu proyecto es una aplicación **Laravel** (no Express/Node.js) que integra Shopify con Lioren para facturación electrónica chilena.

---

## 1. Validación HMAC de Webhooks de Shopify

### ¿Cómo validas la firma HMAC?

**Ubicación:** `app/Http/Controllers/IntegracionController.php` - Método `webhookReceiver()`

```php
public function webhookReceiver(Request $request)
{
    $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
    $shop_domain = $request->header('X-Shopify-Shop-Domain');
    $topic = $request->header('X-Shopify-Topic');
    
    $data = $request->getContent(); // Raw body
    
    // Obtener configuración activa de la BD
    $config = \App\Models\IntegracionConfig::getActiva();
    
    // Validar HMAC (ACTUALMENTE DESACTIVADO)
    if ($hmac_header && false) { // ⚠️ Desactivado temporalmente
        $calculated_hmac = base64_encode(
            hash_hmac('sha256', $data, $config->shopify_secret, true)
        );
        
        if (!hash_equals($calculated_hmac, $hmac_header)) {
            Log::error('HMAC inválido - Webhook rechazado');
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        Log::info('✅ HMAC válido');
    }
    
    // Procesar webhook...
}
```

### ¿Qué valor usas como WEBHOOK_SECRET?

**Respuesta:** Usas `$config->shopify_secret` que se obtiene de la tabla `integracion_configs` en la base de datos.

- **Campo en BD:** `shopify_secret`
- **Origen:** Se guarda cuando el usuario configura la integración en el formulario
- **Modelo:** `App\Models\IntegracionConfig`

**⚠️ IMPORTANTE:** Actualmente la validación HMAC está **DESACTIVADA** (`if ($hmac_header && false)`). Esto es inseguro en producción.

---

## 2. Configuración de Middleware

### Framework: Laravel (no Express)

Laravel usa middleware diferente a Express. Aquí está tu configuración:

#### Exclusión de CSRF para Webhooks

**Archivo:** `app/Http/Middleware/VerifyCsrfToken.php`

```php
protected $except = [
    'integracion/webhook-receiver', // ✅ Webhook excluido de CSRF
];
```

Esto permite que Shopify envíe POST requests sin token CSRF.

#### Ruta del Webhook

**Archivo:** `routes/web.php`

```php
// Webhook receiver - Public route (no auth required)
Route::post('/integracion/webhook-receiver', 
    [App\Http\Controllers\IntegracionController::class, 'webhookReceiver']
)->name('integracion.webhook');
```

**URL completa:** `https://tu-dominio.com/integracion/webhook-receiver`

---

## 3. Captura del Raw Body para Verificación HMAC

### ¿Cómo capturas el raw body?

Laravel maneja esto automáticamente:

```php
// Obtener raw body (para HMAC)
$data = $request->getContent();

// Obtener datos parseados (para procesar)
$webhook_data = json_decode($data, true);
```

**Diferencia con Express:**
- En Express necesitas `bodyParser.raw()` + middleware custom
- En Laravel, `$request->getContent()` siempre devuelve el raw body
- Laravel parsea automáticamente el JSON en `$request->all()` o `json_decode()`

---

## 4. Procesamiento de Webhooks de Órdenes

### Flujo Completo

```
Shopify Webhook → Laravel Route → webhookReceiver() → Procesar según evento
                                                      ↓
                                    ┌─────────────────┴─────────────────┐
                                    │                                   │
                            facturacion_enabled?                        │
                                    │                                   │
                        ┌───────────┴───────────┐                      │
                        │                       │                      │
                       SÍ                      NO                      │
                        │                       │                      │
        procesarPedidoConFacturacion()  procesarPedido()              │
                        │                       │                      │
                        ├───────────────────────┤                      │
                        │                                              │
                Emitir Factura/Boleta en Lioren                       │
                        │                                              │
                Guardar en BD (facturas_emitidas / boletas)           │
                        │                                              │
                    Actualizar Stock (TODO)                            │
```

### Eventos Soportados

Tu sistema escucha estos webhooks:

1. **orders/create** → `procesarPedido()` o `procesarPedidoConFacturacion()`
2. **products/create** → `procesarProductoCreado()`
3. **products/update** → `procesarProductoActualizado()`
4. **inventory_levels/update** → `procesarInventario()`

---

## 5. Procesamiento de Órdenes - Código Detallado

### A. Validación de la Orden

```php
public function webhookReceiver(Request $request)
{
    // 1. Extraer headers
    $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
    $shop_domain = $request->header('X-Shopify-Shop-Domain');
    $topic = $request->header('X-Shopify-Topic');
    $evento = $request->query('evento'); // orders_create, products_update, etc.
    
    // 2. Obtener raw body
    $data = $request->getContent();
    
    // 3. Log del evento
    Log::info('=== WEBHOOK RECIBIDO ===', [
        'evento' => $evento,
        'topic' => $topic,
        'shop' => $shop_domain,
    ]);
    
    // 4. Obtener configuración activa
    $config = \App\Models\IntegracionConfig::getActiva();
    
    if (!$config) {
        Log::error('No hay configuración activa');
        return response()->json(['error' => 'No configuration found'], 500);
    }
    
    // 5. Validar HMAC (actualmente desactivado)
    // ... código de validación ...
    
    // 6. Parsear JSON
    $webhook_data = json_decode($data, true);
    
    if (!$webhook_data) {
        Log::error('Error al decodificar JSON');
        return response()->json(['error' => 'Bad Request'], 400);
    }
    
    // 7. Procesar según evento
    switch ($evento) {
        case 'orders_create':
        case 'order_create':
            if ($config->facturacion_enabled) {
                $this->procesarPedidoConFacturacion($webhook_data, $config->lioren_api_key);
            } else {
                $this->procesarPedido($webhook_data, $config->lioren_api_key);
            }
            break;
        // ... otros casos ...
    }
    
    return response()->json(['status' => 'success'], 200);
}
```

### B. Guardar en Base de Datos

**Modelo:** `App\Models\Boleta` o `App\Models\FacturaEmitida`

```php
// Ejemplo de guardado de boleta
$boleta = \App\Models\Boleta::create([
    'user_id' => 1,
    'lioren_id' => $result['id'] ?? null,
    'tipodoc' => '39', // Boleta
    'folio' => $result['folio'] ?? null,
    'fecha' => $result['fecha'] ?? now()->format('Y-m-d'),
    'receptor_rut' => $rut,
    'receptor_nombre' => $customerName,
    'receptor_email' => $customerEmail,
    'monto_neto' => $result['montoneto'] ?? 0,
    'monto_exento' => $result['montoexento'] ?? 0,
    'monto_iva' => $result['montoiva'] ?? 0,
    'monto_total' => $result['montototal'] ?? 0,
    'pdf_base64' => $result['pdf'] ?? null,
    'xml_base64' => $result['xml'] ?? null,
    'detalles' => $result['detalles'] ?? $detalles,
    'observaciones' => 'Pedido Shopify #' . $order['order_number'],
    'status' => 'emitida',
]);
```

### C. Generar Boleta/Factura en Lioren

#### Boleta Simple (sin facturación habilitada)

```php
private function procesarPedido($order, $api_key)
{
    // 1. Extraer datos del cliente
    $customer = $order['customer'] ?? [];
    $customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
    $customerEmail = $customer['email'] ?? $order['email'] ?? null;
    
    // 2. Extraer RUT de note_attributes
    $rut = null;
    if (isset($order['note_attributes']) && is_array($order['note_attributes'])) {
        foreach ($order['note_attributes'] as $attr) {
            if (strtoupper($attr['name'] ?? '') === 'RUT') {
                $rut = $attr['value'];
                break;
            }
        }
    }
    
    // 3. Preparar detalles de productos
    $detalles = [];
    foreach ($order['line_items'] ?? [] as $item) {
        $detalles[] = [
            'codigo' => $item['sku'] ?? 'PROD-' . $item['product_id'],
            'nombre' => $item['title'] ?? 'Producto',
            'cantidad' => floatval($item['quantity'] ?? 1),
            'precio' => floatval($item['price'] ?? 0), // Precio BRUTO (con IVA)
            'unidad' => 'UN',
            'exento' => false, // Afecto a IVA
        ];
    }
    
    // 4. Preparar payload para Lioren
    $boletaData = [
        'emisor' => [
            'tipodoc' => '39', // Boleta Afecta
            'servicio' => 3,   // Ventas y Servicios
            'observaciones' => 'Pedido Shopify #' . $order['order_number'],
        ],
        'detalles' => $detalles,
        'expects' => 'all', // Recibir PDF y XML
    ];
    
    // 5. Agregar receptor si hay datos
    if ($rut || $customerName) {
        $boletaData['receptor'] = array_filter([
            'rut' => $rut,
            'rs' => $customerName ?: 'Cliente',
            'email' => $customerEmail,
        ]);
    }
    
    // 6. Enviar a Lioren
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$api_key}",
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->timeout(30)->post('https://www.lioren.cl/api/boletas', $boletaData);
    
    // 7. Guardar resultado en BD
    if ($response->successful()) {
        $result = $response->json();
        
        \App\Models\Boleta::create([
            'user_id' => 1,
            'lioren_id' => $result['id'] ?? null,
            'tipodoc' => '39',
            'folio' => $result['folio'] ?? null,
            // ... más campos ...
            'status' => 'emitida',
        ]);
        
        Log::info("✅ Boleta #{$result['folio']} emitida exitosamente");
    } else {
        // Guardar error
        \App\Models\Boleta::create([
            'status' => 'error',
            'error_message' => $response->body(),
        ]);
    }
}
```

#### Factura Completa (con facturación habilitada)

```php
private function procesarPedidoConFacturacion($order, $api_key)
{
    // 1. Extraer note_attributes
    $noteAttributes = $order['note_attributes'] ?? [];
    $customData = [];
    foreach ($noteAttributes as $attribute) {
        $customData[$attribute['name']] = $attribute['value'];
    }
    
    // 2. Detectar tipo de documento
    $tipoDocumento = $customData['tipo_documento'] ?? 'boleta';
    $esFactura = strtolower($tipoDocumento) === 'factura';
    
    if ($esFactura) {
        // 3. Extraer datos de factura
        $rut = $customData['rut'] ?? null;
        $razonSocial = $customData['razon_social'] ?? null;
        $giro = $customData['giro'] ?? null;
        $direccion = $customData['direccion'] ?? null;
        $ciudad = $customData['ciudad'] ?? null;
        
        // 4. Validar campos obligatorios
        if (!$rut || !$razonSocial || !$giro || !$direccion || !$ciudad) {
            throw new \Exception('Faltan datos obligatorios para factura');
        }
        
        // 5. Obtener IDs de localización (comuna/ciudad)
        $localizacion = $this->obtenerIdsLocalizacion($ciudad, $api_key);
        
        // 6. Preparar productos
        $detalles = [];
        foreach ($order['line_items'] ?? [] as $item) {
            $detalles[] = [
                'nombre' => $item['name'],
                'cantidad' => $item['quantity'],
                'precio' => round(floatval($item['price']) / 1.19, 2), // Neto sin IVA
                'codigo' => $item['sku'] ?? '',
                'exento' => false,
            ];
        }
        
        // 7. Construir payload para factura
        $datosFactura = [
            'emisor' => [
                'tipodoc' => '33', // Factura afecta
                'fecha' => date('Y-m-d'),
                'observaciones' => "Orden Shopify: {$order['order_number']}",
            ],
            'receptor' => [
                'rut' => preg_replace('/[^0-9kK]/', '', $rut),
                'rs' => $razonSocial,
                'giro' => $giro,
                'comuna' => $localizacion['comunaId'],
                'ciudad' => $localizacion['ciudadId'],
                'direccion' => $direccion,
                'email' => $order['customer']['email'] ?? null,
            ],
            'detalles' => $detalles,
            'expects' => 'all',
        ];
        
        // 8. Emitir factura
        $resultado = $this->emitirFacturaLioren($datosFactura, $api_key);
        
        // 9. Guardar en BD
        \App\Models\FacturaEmitida::create([
            'shopify_order_id' => $order['id'],
            'shopify_order_number' => $order['order_number'],
            'tipo_documento' => '33',
            'lioren_factura_id' => $resultado['id'] ?? null,
            'folio' => $resultado['folio'] ?? null,
            'rut_receptor' => $rut,
            'razon_social' => $razonSocial,
            'monto_neto' => $resultado['montoneto'] ?? 0,
            'monto_iva' => $resultado['montoiva'] ?? 0,
            'monto_total' => $resultado['montototal'] ?? 0,
            'pdf_base64' => $resultado['pdf'] ?? null,
            'xml_base64' => $resultado['xml'] ?? null,
            'status' => 'emitida',
            'emitida_at' => now(),
        ]);
        
        Log::info("✅ Factura emitida: Folio {$resultado['folio']}");
    } else {
        // Emitir boleta simple
        $this->emitirBoletaLioren($order, $api_key);
    }
}
```

### D. Actualizar Stock

**Estado:** ⚠️ **NO IMPLEMENTADO** (marcado como TODO en el código)

Actualmente el sistema:
- ✅ Sincroniza productos de Shopify → Lioren al configurar
- ✅ Escucha webhooks de `inventory_levels/update`
- ❌ NO actualiza stock automáticamente

Para implementarlo, necesitarías:

```php
private function procesarInventario($inventory, $api_key)
{
    $inventoryItemId = $inventory['inventory_item_id'];
    $available = $inventory['available'];
    
    // 1. Buscar producto mapeado
    $mapping = \App\Models\ProductMapping::where('shopify_variant_id', $inventoryItemId)->first();
    
    if (!$mapping) {
        Log::warning("Producto no encontrado para inventory_item_id: {$inventoryItemId}");
        return;
    }
    
    // 2. Actualizar stock en Lioren
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$api_key}",
        'Content-Type' => 'application/json',
    ])->put("https://www.lioren.cl/api/productos/{$mapping->lioren_product_id}", [
        'stock' => $available,
    ]);
    
    if ($response->successful()) {
        // 3. Actualizar en BD local
        $mapping->update([
            'stock' => $available,
            'last_synced_at' => now(),
        ]);
        
        Log::info("✅ Stock actualizado: {$mapping->product_title} → {$available}");
    }
}
```

---

## 6. Configuración de Webhooks en Shopify

### Creación Automática

Cuando configuras la integración, el sistema crea automáticamente los webhooks:

```php
private function crearWebhooks($tienda, $token, $webhook_url)
{
    $webhooks = [
        ['topic' => 'orders/create', 'nombre' => 'Nuevos Pedidos'],
        ['topic' => 'products/create', 'nombre' => 'Productos Creados'],
        ['topic' => 'products/update', 'nombre' => 'Productos Actualizados'],
        ['topic' => 'inventory_levels/update', 'nombre' => 'Inventario Actualizado']
    ];
    
    foreach ($webhooks as $webhook) {
        $url_completa = $webhook_url . '?evento=' . str_replace('/', '_', $webhook['topic']);
        
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
        ])->post("https://{$tienda}/admin/api/2024-01/webhooks.json", [
            'webhook' => [
                'topic' => $webhook['topic'],
                'address' => $url_completa,
                'format' => 'json'
            ]
        ]);
        
        if ($response->successful()) {
            Log::info("Webhook creado: {$webhook['topic']}");
        }
    }
}
```

**URLs generadas:**
- `https://tu-dominio.com/integracion/webhook-receiver?evento=orders_create`
- `https://tu-dominio.com/integracion/webhook-receiver?evento=products_create`
- `https://tu-dominio.com/integracion/webhook-receiver?evento=products_update`
- `https://tu-dominio.com/integracion/webhook-receiver?evento=inventory_levels_update`

---

## 7. Problemas y Mejoras Recomendadas

### ⚠️ Problemas Actuales

1. **HMAC Desactivado:** La validación HMAC está comentada (`if ($hmac_header && false)`). Esto es inseguro.

2. **No hay variable de entorno WEBHOOK_SECRET:** El secret se obtiene de la BD, no del `.env`.

3. **Stock no se actualiza:** El método `procesarInventario()` está vacío.

4. **Sin reintentos:** Si Lioren falla, no hay sistema de reintentos.

5. **Sin rate limiting:** Los webhooks no tienen protección contra spam.

### ✅ Mejoras Recomendadas

#### 1. Activar validación HMAC

```php
// Cambiar esto:
if ($hmac_header && false) {

// Por esto:
if ($hmac_header) {
    $calculated_hmac = base64_encode(
        hash_hmac('sha256', $data, $config->shopify_secret, true)
    );
    
    if (!hash_equals($calculated_hmac, $hmac_header)) {
        Log::error('HMAC inválido');
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
```

#### 2. Implementar cola de trabajos (Queue)

```php
// En webhookReceiver()
switch ($evento) {
    case 'orders_create':
        // En lugar de procesar directamente:
        dispatch(new ProcessOrderJob($webhook_data, $config->lioren_api_key));
        break;
}
```

#### 3. Agregar reintentos

```php
// En el Job
public $tries = 3;
public $backoff = [60, 300, 900]; // 1min, 5min, 15min
```

#### 4. Implementar actualización de stock

Ver código de ejemplo en sección D anterior.

---

## 8. Comparación con Express/Node.js

| Aspecto | Tu Implementación (Laravel) | Express/Node.js |
|---------|----------------------------|-----------------|
| **Framework** | Laravel (PHP) | Express (JavaScript) |
| **Raw Body** | `$request->getContent()` | `bodyParser.raw()` |
| **Parsed Body** | `json_decode($data, true)` | `bodyParser.json()` |
| **CSRF** | Middleware `VerifyCsrfToken` | Custom middleware |
| **Routing** | `routes/web.php` | `app.post('/webhook')` |
| **HMAC** | `hash_hmac()` + `base64_encode()` | `crypto.createHmac()` |
| **Secret Storage** | Base de datos | `.env` (típicamente) |

---

## 9. Configuración Actual en `.env`

**Nota:** Tu `.env` NO tiene variables de Shopify/Lioren. Todo se guarda en la BD.

```env
# No hay estas variables:
# SHOPIFY_SHOP=
# SHOPIFY_ACCESS_TOKEN=
# SHOPIFY_WEBHOOK_SECRET=
# LIOREN_API_KEY=
```

**Razón:** Tu sistema permite múltiples usuarios, cada uno con su propia configuración en la tabla `integracion_configs`.

---

## 10. Testing de Webhooks

### Usando ngrok (ya lo tienes)

```bash
# Iniciar ngrok
ngrok.exe http 8000

# URL generada: https://9f007b16845b.ngrok-free.app
```

### Configurar en Shopify

1. Ir a Settings → Notifications → Webhooks
2. Crear webhook:
   - Event: `Order creation`
   - Format: `JSON`
   - URL: `https://9f007b16845b.ngrok-free.app/integracion/webhook-receiver?evento=orders_create`

### Ver logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# O usar Log Viewer en el navegador
php artisan log-viewer:publish
```

---

## Resumen Final

Tu sistema:
- ✅ Usa Laravel (no Express)
- ✅ Recibe webhooks en `/integracion/webhook-receiver`
- ✅ Excluye CSRF para webhooks
- ✅ Captura raw body con `$request->getContent()`
- ✅ Valida HMAC (pero está desactivado)
- ✅ Procesa órdenes y emite boletas/facturas
- ✅ Guarda en BD (`boletas` y `facturas_emitidas`)
- ⚠️ NO actualiza stock automáticamente
- ⚠️ NO usa colas (procesa síncronamente)
- ⚠️ HMAC desactivado (inseguro)

**Próximos pasos recomendados:**
1. Activar validación HMAC
2. Implementar actualización de stock
3. Usar colas de Laravel para procesar webhooks
4. Agregar reintentos automáticos
