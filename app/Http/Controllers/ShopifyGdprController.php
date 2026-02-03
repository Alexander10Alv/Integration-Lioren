<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyGdprController extends Controller
{
    /**
     * Verificar firma HMAC de Shopify
     */
    private function verifyWebhook(Request $request): bool
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $secret = config('shopify.client_secret');
        
        if (!$hmac || !$secret) {
            return false;
        }
        
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        
        return hash_equals($hmac, $calculatedHmac);
    }

    /**
     * Webhook: Solicitud de datos del cliente (GDPR)
     * Shopify solicita que proporcionemos todos los datos que tenemos del cliente
     */
    public function customersDataRequest(Request $request)
    {
        Log::info('🔔 GDPR Webhook recibido: customers/data_request', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent()
        ]);

        // Verificar firma HMAC
        $isValid = $this->verifyWebhook($request);
        
        Log::info('🔐 Verificación HMAC', [
            'webhook' => 'customers/data_request',
            'hmac_valido' => $isValid ? 'SÍ' : 'NO',
            'hmac_recibido' => $request->header('X-Shopify-Hmac-Sha256'),
            'secret_configurado' => config('shopify.client_secret') ? 'SÍ' : 'NO'
        ]);

        if (!$isValid) {
            Log::warning('❌ GDPR Webhook: Firma HMAC inválida', [
                'webhook' => 'customers/data_request',
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        $data = $request->json()->all();
        
        Log::info('✅ GDPR: Solicitud de datos del cliente recibida', [
            'shop_domain' => $data['shop_domain'] ?? null,
            'customer_id' => $data['customer']['id'] ?? null,
            'customer_email' => $data['customer']['email'] ?? null,
        ]);

        // TODO: Implementar lógica para recopilar y enviar datos del cliente
        // Por ahora solo registramos la solicitud
        
        return response('', 200);
    }

    /**
     * Webhook: Solicitud de eliminación de datos del cliente (GDPR)
     * Shopify solicita que eliminemos todos los datos del cliente
     */
    public function customersRedact(Request $request)
    {
        Log::info('🔔 GDPR Webhook recibido: customers/redact', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent()
        ]);

        // Verificar firma HMAC
        $isValid = $this->verifyWebhook($request);
        
        Log::info('🔐 Verificación HMAC', [
            'webhook' => 'customers/redact',
            'hmac_valido' => $isValid ? 'SÍ' : 'NO',
            'hmac_recibido' => $request->header('X-Shopify-Hmac-Sha256'),
            'secret_configurado' => config('shopify.client_secret') ? 'SÍ' : 'NO'
        ]);

        if (!$isValid) {
            Log::warning('❌ GDPR Webhook: Firma HMAC inválida', [
                'webhook' => 'customers/redact',
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        $data = $request->json()->all();
        
        Log::info('✅ GDPR: Solicitud de eliminación de datos del cliente', [
            'shop_domain' => $data['shop_domain'] ?? null,
            'customer_id' => $data['customer']['id'] ?? null,
            'customer_email' => $data['customer']['email'] ?? null,
        ]);

        // TODO: Implementar lógica para eliminar datos del cliente
        // - Eliminar de integracion_configs
        // - Eliminar de solicitudes
        // - Eliminar de facturas/boletas relacionadas
        // - Anonimizar datos si es necesario por regulaciones
        
        return response('', 200);
    }

    /**
     * Webhook: Solicitud de eliminación de datos de la tienda (GDPR)
     * Shopify solicita que eliminemos todos los datos de la tienda
     */
    public function shopRedact(Request $request)
    {
        Log::info('🔔 GDPR Webhook recibido: shop/redact', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent()
        ]);

        // Verificar firma HMAC
        $isValid = $this->verifyWebhook($request);
        
        Log::info('🔐 Verificación HMAC', [
            'webhook' => 'shop/redact',
            'hmac_valido' => $isValid ? 'SÍ' : 'NO',
            'hmac_recibido' => $request->header('X-Shopify-Hmac-Sha256'),
            'secret_configurado' => config('shopify.client_secret') ? 'SÍ' : 'NO'
        ]);

        if (!$isValid) {
            Log::warning('❌ GDPR Webhook: Firma HMAC inválida', [
                'webhook' => 'shop/redact',
                'ip' => $request->ip()
            ]);
            return response('Unauthorized', 401);
        }

        $data = $request->json()->all();
        
        Log::info('✅ GDPR: Solicitud de eliminación de datos de la tienda', [
            'shop_domain' => $data['shop_domain'] ?? null,
            'shop_id' => $data['shop_id'] ?? null,
        ]);

        // TODO: Implementar lógica para eliminar todos los datos de la tienda
        // - Eliminar integracion_configs de esta tienda
        // - Eliminar solicitudes relacionadas
        // - Eliminar webhooks
        // - Eliminar facturas/boletas
        // - Eliminar mappings de productos
        
        return response('', 200);
    }
}
