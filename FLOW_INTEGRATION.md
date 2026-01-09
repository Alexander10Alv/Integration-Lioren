# Integración Flow Chile - Laravel

## ¿Qué es Flow?

Flow es una pasarela de pagos chilena que permite procesar pagos con tarjetas de crédito/débito de forma segura. Actúa como intermediario entre tu aplicación y diferentes métodos de pago como Webpay, Servipag, Multicaja, OnePay, etc.

## Configuración

### 1. Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
# Flow Payment Gateway Configuration
FLOW_API_KEY=tu_api_key_aqui
FLOW_SECRET_KEY=tu_secret_key_aqui
FLOW_SANDBOX=true
```

### 2. Obtener Credenciales

#### Para Testing (Sandbox):
1. Ve a [Flow Developers](https://www.flow.cl/developers)
2. Regístrate como desarrollador
3. Crea una aplicación de prueba
4. Obtén tu API Key y Secret Key de sandbox

#### Para Producción:
1. Contacta a Flow para obtener credenciales de producción
2. Cambia `FLOW_SANDBOX=false` en tu `.env`

### 3. Ejecutar Migraciones

```bash
php artisan migrate
```

## Uso

### 1. Formulario de Pago

Visita `/flow/payment-form` para ver el formulario de prueba.

### 2. Crear un Pago Programáticamente

```php
use App\Services\FlowService;
use App\Models\Payment;

$flowService = new FlowService();

$paymentData = [
    'order_id' => 'ORDER_' . time(),
    'subject' => 'Pago de servicios',
    'amount' => 1000, // En pesos chilenos
    'email' => 'cliente@email.com',
    'currency' => 'CLP',
    'payment_method' => 1, // 1 = Webpay
    'url_confirmation' => config('flow.confirmation_url'),
    'url_return' => config('flow.return_url'),
];

$response = $flowService->createPayment($paymentData);

if (!isset($response['error'])) {
    // Guardar en base de datos
    $payment = Payment::create([
        'order_id' => $paymentData['order_id'],
        'flow_token' => $response['token'],
        'subject' => $paymentData['subject'],
        'amount' => $paymentData['amount'],
        'currency' => 'CLP',
        'email' => $paymentData['email'],
        'payment_method' => $paymentData['payment_method'],
        'status' => Payment::STATUS_CREATED,
        'flow_response' => $response,
        'user_id' => auth()->id(),
    ]);
    
    // Redirigir al usuario a Flow
    return redirect($response['url']);
}
```

### 3. Estados de Pago

Los pagos tienen los siguientes estados:

- `0` - **Creado**: Pago creado pero no procesado
- `1` - **Pendiente**: Pago en proceso
- `2` - **Pagado**: Pago exitoso
- `3` - **Rechazado**: Pago rechazado
- `4` - **Cancelado**: Pago cancelado

## Testing

### Tarjetas de Prueba

En el ambiente sandbox puedes usar estas tarjetas de prueba:

**Visa (Aprobada):**
- Número: `4051885600446623`
- CVV: `123`
- Fecha: Cualquier fecha futura

**Mastercard (Aprobada):**
- Número: `5186059559590568`
- CVV: `123`
- Fecha: Cualquier fecha futura

**Visa (Rechazada):**
- Número: `4051885600446615`
- CVV: `123`
- Fecha: Cualquier fecha futura

### URLs de Testing

- **Formulario de pago**: `/flow/payment-form`
- **URL de retorno**: `/flow/return`
- **Webhook de confirmación**: `/flow/confirmation`

## Webhooks

Flow enviará notificaciones a tu webhook de confirmación (`/flow/confirmation`) cuando el estado de un pago cambie. Esto es importante para actualizar el estado en tu base de datos de forma confiable.

## Métodos de Pago Disponibles

```php
'payment_methods' => [
    'webpay' => 1,      // Tarjetas de crédito/débito
    'servipag' => 2,    // Servipag
    'multicaja' => 3,   // Multicaja
    'onepay' => 4,      // OnePay
    'paypal' => 5,      // PayPal
],
```

## Seguridad

- Todas las comunicaciones con Flow usan HTTPS
- Los pagos se procesan en los servidores de Flow, no en tu aplicación
- Las firmas digitales verifican la autenticidad de los webhooks
- Los datos sensibles de tarjetas nunca pasan por tu servidor

## Logs

Los eventos importantes se registran en los logs de Laravel:

```bash
tail -f storage/logs/laravel.log | grep Flow
```

## Troubleshooting

### Error: "Token de pago no válido"
- Verifica que las URLs de retorno estén correctamente configuradas
- Asegúrate de que el token se esté pasando correctamente

### Error: "Firma de webhook inválida"
- Verifica que tu SECRET_KEY sea correcto
- Asegúrate de que Flow esté enviando el header `X-Flow-Signature`

### Error: "API Key inválida"
- Verifica que tu API_KEY sea correcto
- Asegúrate de estar usando las credenciales correctas (sandbox vs producción)

## Próximos Pasos

1. **Obtener credenciales reales de Flow**
2. **Configurar webhooks en el panel de Flow**
3. **Implementar notificaciones por email**
4. **Agregar reportes de pagos**
5. **Implementar reembolsos**

## Contacto Flow

- **Soporte técnico**: soporte@flow.cl
- **Teléfono**: +56 2 2583 0102 opción 2
- **Comercial**: comercial@flow.cl
- **Teléfono comercial**: +56 2 2583 0102 opción 3
